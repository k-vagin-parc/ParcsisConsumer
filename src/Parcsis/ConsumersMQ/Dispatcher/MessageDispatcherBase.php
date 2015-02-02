<?php

namespace Parcsis\ConsumersMQ\Dispatcher;

/**
 * @package Message
 * @subpackage MessageDispatcher
 */
abstract class MessageDispatcherBase
{
	protected $return_reason;
	protected $is_ok = true;
	protected $auto_ack = true;
	protected $queue_name = null;
	protected $debug_mode = false;

	public function setDebugMode($debug_mode)
	{
		$this->debug_mode = $debug_mode;
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
		try {


			if ($msg->getRoutingKey() == \Config::get('consumers-mq::constants.control.restart')) {

				$this->is_ok = false;

				if (!$this->auto_ack) {
					$this->ack($msg);
				}

				$this->return_reason = \Config::get('consumers-mq::constants.control.restart');
			} else {
				$this->callback($msg);
			}

		} finally {
			$this->finalize();
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
		return $this->queue_name;
	}

	public function getReturnReason()
	{
		return $this->return_reason;
	}

	protected function ack(\AMQPBrokerMessage $msg)
	{
		AMQP::getInstance()->ack($msg->getDeliveryTag());
	}

	/**
	 * Установка названия очереди
	 *
	 * @param string $queue_name
	 */
	protected function setQueueName($queue_name)
	{
		$this->queue_name = $queue_name;
	}

	protected function setAutoAck($value)
	{
		$this->auto_ack = $value;
	}

	abstract protected function callback(\AMQPBrokerMessage $msg);

	protected function cancel(\AMQPBrokerMessage $msg)
	{
		AMQP::getInstance()->cancel($msg->getConsumerTag());
	}
	protected function nack(\AMQPBrokerMessage $msg, $requeue = AMQP_NOPARAM)
	{
		AMQP::getInstance()->nack($msg->getDeliveryTag(), $requeue);
	}

	protected function reject(\AMQPBrokerMessage $msg, $requeue)
	{
		AMQP::getInstance()->reject($msg->getDeliveryTag(), $requeue);
	}

	protected function bindControl()
	{
		AMQP::getInstance()->queueBind($this->queue_name, AMQP::getInstance()->getExchangeName(), \Config::get('consumers-mq::constants.control.pattern'));
	}

	protected function setQueue($queue_name, array $classes_or_types, array $options = array('durable' => true, 'auto_delete' => false))
	{
		$this->setQueueName($queue_name);
		$amqp = AMQP::getInstance();
		$exchange_name = $amqp->getExchangeName();
		$amqp->queueDeclare($queue_name, $options);

		foreach ($classes_or_types as $class_name_or_type)
		{
			if (class_exists($class_name_or_type, true)) {
				$amqp->queueBind($queue_name, $exchange_name, $class_name_or_type::getType());
			} else {
				$amqp->queueBind($queue_name, $exchange_name, $class_name_or_type);
			}
		}
	}
}
