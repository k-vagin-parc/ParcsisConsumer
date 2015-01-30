<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


interface IConsumer
{
	/**
	 * @param int|null $timeout
	 * @return string
	 */
	public function consume($timeout = null);

	public function callback(\AMQPBrokerMessage $msg);

	public function queueDeclare($queueName, $parametersQueue);

	public function queueBind($queueName, $exchangePoint, $routingKey);
} 