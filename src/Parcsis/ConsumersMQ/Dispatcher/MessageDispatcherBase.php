<?php

namespace Parcsis\ConsumersMQ\Dispatcher;

/**
 * @package Message
 * @subpackage MessageDispatcher
 */
abstract class MessageDispatcherBase
{
	/**
	 * @var \Parcsis\ConsumersMQ\Queue
	 */
	protected $queue;

	/**
	 * @var \Parcsis\ConsumersMQ\Consumer
	 */
	protected $consumer;

	protected $return_reason;
	protected $is_ok = true;
	protected $auto_ack = true;
	protected $queueName = null;
	protected $debug_mode = false;

	/**
	 * признак - печатать ли отладочный вывод в процессе обработки сообщения
	 * @var bool
	 */
	protected $is_verbosity = false;


	/**
	 * Количество уже обработанных запросов процессом
	 */
	protected $requests = 0;

	/**
	 * Максимальное количество запросов обрабатываемых процессом
	 * 0 - не ограничено
	 */
	protected $maxChildRequests = 0;


	public function setDebugMode($debug_mode)
	{
		$this->debug_mode = $debug_mode;
	}

	public function setIsVerbosity($verbosity)
	{
		$this->is_verbosity = $verbosity;
	}

	public function __construct() {}

	/**
	 * Callback функция, если надо продолжать обработку возвращает
	 * тру, если надо прекратить возвращает false.
	 *
	 * @param \AMQPBrokerMessage $msg
	 * @return boolean
	 */
	public function _callback(\AMQPBrokerMessage $msg)
	{
		$this->is_ok = true;

		try {
			$restartRoute = \Config::get('consumers-mq.constants.control.restart');
			if ($msg->getRoutingKey() == $restartRoute) {

				$this->is_ok = false;

				if (!$this->auto_ack) {
					$this->ack($msg);
				}

				$this->return_reason = $restartRoute;
			}
			else {
				$this->callback($msg);
			}
		}
		finally {
			$this->finalize();
		}

		$this->requests++;
		if ($this->maxChildRequests > 0 && ($this->requests >= $this->maxChildRequests)) {
			exit(0);
		}

		return $this->is_ok;
	}

	public function finalize()
	{
		// Close all database connections
		\DB::disconnect();
	}

	public function getQueueName()
	{
		return $this->queueName;
	}

	public function getReturnReason()
	{
		return $this->return_reason;
	}

	protected function ack(\AMQPBrokerMessage $msg)
	{
		$this->queue->getQueue()->ack($msg->getDeliveryTag());
	}

	/**
	 * Установка названия очереди
	 *
	 * @param string $queue_name
	 */
	protected function setQueueName($queue_name)
	{
		$this->queueName = $queue_name;
	}

	protected function setAutoAck($value)
	{
		$this->auto_ack = $value;
	}

	abstract protected function callback(\AMQPBrokerMessage $msg);

	/**
	 * @param \AMQPBrokerMessage $msg
	 */
	protected function cancel(\AMQPBrokerMessage $msg)
	{
		$this->queue->getQueue()->cancel($msg->getConsumerTag());
	}

	/**
	 * @param \AMQPBrokerMessage $msg
	 * @param int $requeue
	 */
	protected function nack(\AMQPBrokerMessage $msg, $requeue = AMQP_NOPARAM)
	{
		$this->queue->getQueue()->nack($msg->getDeliveryTag(), $requeue);
	}

	/**
	 * @param \AMQPBrokerMessage $msg
	 * @param $requeue
	 */
	protected function reject(\AMQPBrokerMessage $msg, $requeue = AMQP_NOPARAM)
	{
		$this->queue->getQueue()->reject($msg->getDeliveryTag(), $requeue);
	}

	protected function bindControl()
	{
		$this->queue->queueBind($this->queue->getExchangeName(), \Config::get('consumers-mq.constants.control.pattern'));
	}
}
