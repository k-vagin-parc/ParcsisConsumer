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

	public function _callback($msg)
	{
		$r = $msg;
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

		// создаем очередь

		/** @var \PhpAmqpLib\Connection\AMQPConnection $connect */
		$connect = \ConnectMQ::getConnect();

		$channel = $connect->channel();
		$channel->exchange_declare(self::EXCHANGE, 'topic', false, true, false);
		$channel->queue_declare('consumers.mq.test');
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