<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


class TestConsumer extends \Parcsis\ConsumersMQ\Dispatcher\MessageConsumerBase
{
	protected $maxChildRequests = 1;

	public function init()
	{
		parent::init();

		$this->queueBind(TestMessage::EXCHANGE);
		$this->queueDeclare(TestMessage::QUEUE);
	}

	protected function callback(\AMQPBrokerMessage $msg)
	{
		print_r($msg);
	}
}