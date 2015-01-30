<?php

/**
 * Базовый класс сообщений
 *
 * @link http://php.net/arrayaccess
 *
 * @package Message
 * @property-read $message_type
 */
abstract class MessageBaseUntyped implements ArrayAccess
{
	/**
	 * Тип текущего сообщения
	 *
	 * @var string
	 * @access protected
	 */
	protected $_message_prefix = false;

	/**
	 * Время генерации сообщения.
	 * @var int
	 */
	protected $time;

	/**
	 * Returns unix time of the message
	 *
	 * @return int
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * Конструктор сообщений по умолчанию
	 *
	 * @param array $values значения сообщения
	 * @param bool|string $prefix префикс очереди
	 */
	public function __construct($values = null, $prefix = false)
	{
		if (is_array($values)) {
			foreach ($values as $key => &$val) {
				$this->$key = $val;
			}
		}

		if ($prefix !== false) {
			$this->_message_prefix = strval($prefix);
		}

		$this->time = time();

		$this->_setDefaults();
	}

	/**
	 * Установка аттрибутов по умолчанию.
	 */
	public function _setDefaults() {}

	/**
	 * Установка значения свойства сообщения
	 *
	 * @param string $key
	 * @param mixed $val
	 * @throws UndefinedPropertyMessageException
	 */
	public function __set($key, $val)
	{
		if (property_exists($this, $key)) {
			$this->$key = $val;
		} else {
			throw new UndefinedPropertyMessageException('Can\t set property: undefined key name ' . $key . ' in ' . get_class($this) . ' message');
		}
	}

	/**
	 * Получение значения свойства сообщения
	 * нужно для arrayaccess
	 *
	 * @param string $key
	 * @throws UndefinedPropertyMessageException
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($key == 'message_prefix') {
			return $this->_message_prefix;
		} elseif (isset($this->$key)) {
			return $this->$key;
		} else {
			throw new UndefinedPropertyMessageException(sprintf(
				'Can not get property: undefined key name "%s" in message "%s"',
				$key, get_class($this)
			));
		}
	}

	/**
	 * Установка значения свойства сообщения
	 * @param string $key
	 * @param mixed $val
	 * @throws UndefinedPropertyMessageException
	 */
	public function offsetSet($key, $val) {
		if (is_null($key)) {
			throw new UndefinedPropertyMessageException('Can\t set property: undefined key name ' . $key . ' in ' . get_class($this) . ' message');
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
		return isset($this->$key);
	}

	/**
	 * Сброс значения свойства сообщения
	 *
	 * @param string $key
	 */
	public function offsetUnset($key) {
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

	/**
	 * Валидация сообщения перед отправкой
	 *
	 * @abstract
	 */
	abstract protected function validate();

	/**
	 * Строка ключа в раббите.
	 * @abstract
	 * @return void
	 */
	abstract public function getKey();
}
