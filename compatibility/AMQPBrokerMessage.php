<?php

/**
 * Сообщение, приходящее из брокера AMQP.
 */
class AMQPBrokerMessage implements ArrayAccess
{
	protected $consumer_tag;
	protected $delivery_tag;
	protected $exchange;
	protected $routing_key;
	/**
	 * @var $msg MessageBaseUntyped
	 */
	protected $msg;

	public function __construct($consumer_tag, $delivery_tag, $exchange, $routing_key, $msg)
	{
		if ($msg instanceof __PHP_Incomplete_Class) {
			throw new MessageException('Instance of __PHP_Incomplete_Class: ' . var_export($msg, true) . 'Routing_key = ' . $routing_key);
		}

		if ($msg instanceof MessageBaseUntyped) {
			$this->consumer_tag = $consumer_tag;
			$this->delivery_tag = $delivery_tag;
			$this->exchange = $exchange;
			$this->routing_key = $routing_key;
			$this->msg = $msg;
		} else {
			throw new MessageException('$msg must be an instance of MessageBaseUntyped. $msg = ' . var_export($msg, true) . 'Routing_key = ' . $routing_key);
		}
	}

	public function getConsumerTag()
	{
		return $this->consumer_tag;
	}

	public function getDeliveryTag()
	{
		return $this->delivery_tag;
	}

	public function getRoutingKey()
	{
		return $this->routing_key;
	}

	public function getExchange()
	{
		return $this->exchange;
	}

	/**
	 * @return MessageBase
	 */
	public function getMessage()
	{
		return $this->msg;
	}


	// IMPLEMENTS ArrayAccess only for reverse compatibility
	/**
	 * Установка значения свойства сообщения
	 *
	 * @param string $key
	 * @param mixed  $val
	 */
	public function __set($key, $val)
	{
		if (isset($this->$key)) {
			$this->$key = $val;
		} else {
			throw new UndefinedPropertyMessageException('Can\t set property: undefined key name ' . $key);
		}
	}

	/**
	 * Получение значения свойства сообщения
	 * нужно для arrayaccess
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($key == 'body') {
			return $this->msg;
		} elseif (isset($this->$key)) {
			return $this->$key;
		} else {
			throw new UndefinedPropertyMessageException('Can\t get property: undefined key name ' . $key);
		}
	}

	/**
	 * Установка значения свойства сообщения
	 * @param string $key
	 * @param mixed  $val
	 */
	public function offsetSet($key, $val) {
		if (is_null($key)) {
			throw new UndefinedPropertyMessageException('Can\t set property: undefined key name ' . $key);
		} else {
			$this->__set($key, $val);
		}
	}

	/**
	 * Проверка существования свойства сообщения
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function offsetExists($key) {
		if ($key == 'body') {
			$key = 'msg';
		}
		return isset($this->$key);
	}

	/**
	 * Сброс значения свойства сообщения
	 *
	 * @param string $key
	 */
	public function offsetUnset($key) {
		if ($key == 'body') {
			$key = 'msg';
		}
		$this->__set($key, null);
	}

	/**
	 * Получение значения свойства сообщения
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function offsetGet($key) {
		return $this->__get($key);
	}
}