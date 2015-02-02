<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests\BaseConsumeTest;

use Parcsis\ConsumersMQ\Dispatcher\MessageDispatcherBase;

class TestConsumer extends \Parcsis\ConsumersMQ\Dispatcher\MessageConsumerBase
{
	const
		CHILD_MAX_REQUEST 		= 1;

	protected $queueName = 'consumers.mq.test';

	/**
	 * @param int|null $timeout
	 * @return string
	 */
	public function consume($timeout = null)
	{
		// TODO: Implement consume() method.
	}

	public function queueDeclare($queueName, $parametersQueue)
	{
		// TODO: Implement queueDeclare() method.
	}

	public function queueBind($queueName, $exchangePoint, $routingKey)
	{
		// TODO: Implement queueBind() method.
	}

	protected function callback(\AMQPBrokerMessage $msg)
	{
		// TODO: Implement callback() method.
	}
}

class ConsumerMessage extends \MessageBase
{
	/**
	 * Тип сообщения
	 *
	 * @var string
	 * @access protected
	 */
	protected $_message_type = 'consumers.mq.test';

	protected $message = '';

	/**
	 * Валидация сообщения перед отправкой
	 *
	 */
	protected function validate()
	{

	}
}

/**
 * declare queue, send message, receive message
 * Class TestBaseConsume
 * @package Parcsis\ConsumersMQ\Tests
 */
class BaseConsumeTest extends \TestCase
{
	const
		EXCHANGE = 'consumers-mq-test-exchange';

	public function setUp()
	{
		parent::setUp();
	}

	public function testRun()
	{
		$messageData = [
			'message' => ['test', 'value'],
		];

		$message = new ConsumerMessage($messageData);

		\Parcsis\ConsumersMQ\Publisher::publish($message, self::EXCHANGE);

		$consumer = new TestConsumer;
		$consumer->consume(self::EXCHANGE, 5);

		$this->assertEquals($messageData, $consumer->getResult());
	}

	public function testRunTwiceMessage()
	{
		$messageData = [
			'message' => ['test', 'value'],
		];

		$message = new ConsumerMessage($messageData);

		\Parcsis\ConsumersMQ\Publisher::publish($message, self::EXCHANGE);
		\Parcsis\ConsumersMQ\Publisher::publish($message, self::EXCHANGE);

		$consumer = new TestConsumer;
		$consumer->consume(self::EXCHANGE, 5);

		$this->assertEquals($messageData, $consumer->getResult());

		$consumer = new TestConsumer;
		$consumer->consume(self::EXCHANGE, 5);

		$this->assertEquals($messageData, $consumer->getResult());
	}
}