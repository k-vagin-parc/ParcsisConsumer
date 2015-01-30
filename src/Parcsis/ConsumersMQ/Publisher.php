<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


class Publisher
{
	public static function publish(\MessageBaseUntyped $msgBody)
	{
		/** @var \PhpAmqpLib\Connection\AMQPConnection $connect */
		$connect = \ConnectMQ::getConnect();

		$exchangeName = \Config::get('consumers-mq::constants.exchange');
		if (empty($exchangeName)) {
			throw new \Exception("Exchange name undefined"); // маловероятное событие, поэтому кидаем стандартное исключение
		}

		$message = new \PhpAmqpLib\Message\AMQPMessage(serialize($msgBody), ['content_type' => 'text/plain', 'delivery_mode' => 2]);

		$channel = $connect->channel();
		$channel->exchange_declare($exchangeName, 'topic', false, true, false);
		$channel->queue_declare($msgBody->message_type);
		$channel->queue_bind($msgBody->message_type, $exchangeName);

		$channel->basic_publish($message, $exchangeName, $msgBody->getKey());
		$channel->close();
	}
}