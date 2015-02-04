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

	const
		QUEUE		= 'consumers.mq.test',
		EXCHANGE 	= 'consumers-mq-test-exchange';

	protected $maxChildRequests = 1;

	public function init()
	{
		parent::init();

		$this->queueBind(self::EXCHANGE);
		$this->queueDeclare(self::QUEUE);
	}

	protected function callback(\AMQPBrokerMessage $msg)
	{

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

		// pre fetch
		\Parcsis\ConsumersMQ\Publisher::publish($message, TestConsumer::EXCHANGE);
		\Parcsis\ConsumersMQ\Publisher::publish($message, TestConsumer::EXCHANGE);
		\Parcsis\ConsumersMQ\Publisher::publish($message, TestConsumer::EXCHANGE);
		\Parcsis\ConsumersMQ\Publisher::publish($message, TestConsumer::EXCHANGE);


		$consumer = new TestConsumer;
		$consumer->consume();

		//$this->assertEquals($messageData, $consumer->getResult());
	}

	public function RunTwiceMessage()
	{
		$messageData = [
			'message' => ['test', 'value'],
		];

		$message = new ConsumerMessage($messageData);

		\Parcsis\ConsumersMQ\Publisher::publish($message, TestConsumer::EXCHANGE);
		\Parcsis\ConsumersMQ\Publisher::publish($message, TestConsumer::EXCHANGE);

		$consumer = new TestConsumer;
		$consumer->consume();

		//$this->assertEquals($messageData, $consumer->getResult());

		$consumer = new TestConsumer;
		$consumer->consume();

		//$this->assertEquals($messageData, $consumer->getResult());
	}
}