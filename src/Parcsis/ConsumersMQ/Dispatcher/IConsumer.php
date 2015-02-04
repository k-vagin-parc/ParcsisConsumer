<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Dispatcher;


interface IConsumer
{
	/**
	 * @param int|null $timeout
	 * @return string
	 */
	public function consume($timeout = null);

	public function queueDeclare($queueName, $parametersQueue = []);

	public function queueBind($exchangeName, $routingKey = '#');
} 