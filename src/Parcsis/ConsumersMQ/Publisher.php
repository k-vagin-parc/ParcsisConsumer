<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


class Publisher
{
	/**
	 * Допустимые свойства сообщений и их типы
	 * @var array
	 */
	private static $messageProperties = array(
		"content_type"        => "shortstr",
		"content_encoding"    => "shortstr",
		"application_headers" => "table",
		"delivery_mode"       => "octet",
		"priority"            => "octet",
		"correlation_id"      => "shortstr",
		"reply_to"            => "shortstr",
		"expiration"          => "shortstr",
		"message_id"          => "shortstr",
		"timestamp"           => "timestamp",
		"type"                => "shortstr",
		"user_id"             => "shortstr",
		"app_id"              => "shortstr",
		"cluster_id"          => "shortst"
	);

	/**
	 * Максимально совместимо с \AMQPPhpExtension::_publish()
	 * @param \MessageBaseUntyped $msgBody
	 * @param $exchangeName
	 * @param array $parameters
	 * @param bool $mandatory
	 * @param bool $immediate
	 * @return bool
	 * @throws \Exception
	 */
	public static function publish(\MessageBaseUntyped $msgBody, $exchangeName, array $parameters = [], $mandatory = false, $immediate = false)
	{
		$routeKey = $msgBody->getKey();

		if (empty($exchangeName)) {
			throw new \Exception('Exchange name is null. Error publish message #' . $routeKey . ' (' . get_class($msgBody) . ')');
		}

		$parameters = (!is_array($parameters) || empty($parameters)) ? [] : $parameters;
		$parameters['content_type'] = isset($parameters['content_type']) ? $parameters['content_type'] : 'text/plain';
		$parameters = array_intersect_key($parameters, self::$messageProperties);

		/** @var \Parcsis\ConsumersMQ\Connection $connectObject */
		$connectObject = \App::make('ConnectMQ');
		$exchange = $connectObject->getExchange($exchangeName);

		// Отправка сообщения
		$result = false;
		try {
			$bitflags = AMQP_NOPARAM;
			if ($mandatory) {
				$bitflags |= AMQP_MANDATORY;
			}
			if ($immediate) {
				$bitflags |= AMQP_IMMEDIATE;
			}

			$result = $exchange->publish(serialize($msgBody), $routeKey, $bitflags, $parameters);
		}
		catch (\Exception $e) {
			\Log::notice('AMQP Publish error', ['exception' => var_export($e, true)]);
		}

		return $result;
	}
}