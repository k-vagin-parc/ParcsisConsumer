<?php

namespace Parcsis\ConsumersMQ;

/**
 * @package Message
 * @subpackage MessageDispatcher
 */
abstract class MessageDispatcherBase
{
	protected $returnReason;
	protected $isOk = true;
	protected $autoAck = true;
	protected $queueName = null;
	protected $debugMode = false;

	public function setDebugMode($debugMode)
	{
		$this->debugMode = $debugMode;
	}

	const REASON_RESTART = 'restart';

	public function __construct() {}

	/**
	 * Callback функция, если надо продолжать обработку возвращает
	 * тру, если надо прекратить возвращает false.
	 *
	 * @param \AMQPBrokerMessage|array $msg
	 * @return boolean
	 */
	public function _callback(\AMQPBrokerMessage $msg)
	{
		try {
			if ($msg->getRoutingKey() == \Config::get('consumers-mq::constants.control.restart')) {

				$this->isOk = false;

				if (!$this->autoAck) {
					$this->ack($msg);
				}

				$this->returnReason = self::REASON_RESTART;
			}
			else {
				$this->callback($msg);
			}
		}
		finally {
			$this->finalize();
		}
		return $this->isOk;
	}

	public function finalize()
	{
		// Close all database connections
		//DataBase::cleanupAll();
	}

	public function getQueueName()
	{
		return $this->queueName;
	}

	public function getReturnReason()
	{
		return $this->returnReason;
	}

	protected function ack(AMQPBrokerMessage $msg)
	{
		//AMQP::getInstance()->ack($msg->getDeliveryTag());
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
		$this->autoAck = $value;
	}

	abstract protected function callback(\AMQPBrokerMessage $msg);

	protected function cancel(AMQPBrokerMessage $msg)
	{
		//AMQP::getInstance()->cancel($msg->getConsumerTag());
	}
	protected function nack(AMQPBrokerMessage $msg, $requeue)
	{
		//AMQP::getInstance()->nack($msg->getDeliveryTag(), $requeue);
	}

	protected function reject(AMQPBrokerMessage $msg, $requeue)
	{
		//AMQP::getInstance()->reject($msg->getDeliveryTag(), $requeue);
	}

	protected function bindControl()
	{
		//AMQP::getInstance()->queueBind($this->queue_name, AMQP::getInstance()->getExchangeName(), MessageConstant::CONTROL_PATTERN);
	}

	protected function setQueue($queue_name, array $classes_or_types, array $options = ['durable' => true, 'auto_delete' => false])
	{
		/*$this->setQueueName($queue_name);
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
		}*/
	}
}
