<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;

class BaseConsumer extends \Parcsis\ConsumersMQ\MessageDispatcherBase implements \Parcsis\ConsumersMQ\IConsumer {

	/**
	 * @param int|null $timeout
	 * @return string
	 */
	public function consume($timeout = null)
	{

	}

	public function callback(\AMQPBrokerMessage $msg)
	{
		// TODO: Implement callback() method.
	}

	public function queueDeclare($queueName, $parametersQueue)
	{
		// TODO: Implement queueDeclare() method.
	}

	public function queueBind($queueName, $exchangePoint, $routingKey)
	{
		// TODO: Implement queueBind() method.
	}
}

/**
 * declare queue, send message, receive message
 * Class TestBaseConsume
 * @package Parcsis\ConsumersMQ\Tests
 */
class BaseConsumeTest extends \TestCase
{
	public function setUp()
	{
		parent::setUp();

		// создаем очередь

		/** @var \PhpAmqpLib\Connection\AMQPConnection $connect */
		$connect = \ConnectMQ::getConnect();

		$exchangeName = \Config::get('consumers-mq::constants.exchange');
		if (empty($exchangeName)) {
			throw new \Exception("Exchange name undefined"); // маловероятное событие, поэтому кидаем стандартное исключение
		}

		$channel = $connect->channel();
		$channel->exchange_declare($exchangeName, 'topic', false, true, false);
		$channel->queue_declare('consumers-mq-test');
	}

	public function testRun()
	{

	}
}