<?php

/**
 * Базовый класс сообщений
 *
 * @link http://php.net/arrayaccess
 * @package Message
 * @property-read $message_type
 */
abstract class MessageBase extends MessageBaseUntyped
{
	/**
	 * Тип текущего сообщения
	 *
	 * @var string
	 * @access protected
	 */
	protected $_message_type = '';
	protected $_message_prefix = false;

	/**
	 * Возвращаем тип сообщения
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->_message_type;
	}

	/**
	 * Ключ сообщения
	 *
	 * @return string
	 */
	public function getKey()
	{
		return ($this->_message_prefix !== false ? $this->_message_prefix . '.' : '') . $this->_message_type;
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
		if ($key == 'message_type') {
			return $this->_message_type;
		} else {
			return parent::__get($key);
		}
	}
}
