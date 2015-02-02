<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


class Publisher
{
	public static function publish(\MessageBaseUntyped $msgBody, $exchangeName)
	{
		/** @var \PhpAmqpLib\Connection\AMQPConnection $connect */
		$connect = \ConnectMQ::getConnect();

		$message = new \PhpAmqpLib\Message\AMQPMessage(serialize($msgBody), ['content_type' => 'text/plain', 'delivery_mode' => 2]);

		$channel = $connect->channel();
		$channel->queue_bind($msgBody->message_type, $exchangeName, $msgBody->getKey());
		$channel->basic_publish($message, $exchangeName, $msgBody->getKey());
		$channel->close();
	}
}