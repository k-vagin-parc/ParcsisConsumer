<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


class Connection
{
	private $host = '';
	private $port = '';
	private $user = '';
	private $password = '';
	private $vhost = '/';

	/**
	 * @var \AMQPConnection
	 */
	private $connect = null;
	private $channel = null;
	private $exchange = null;

	/**
	 * Параметры точки обмена по умолчанию
	 *
	 * @static
	 * @var array
	 */
	private static $exchangeParams = array(
		'passive'     => false,
		'durable'     => true, // Восстанавливать точку после перезагрузки. К такой точке можно привязать только очередь с durable = true
		'auto_delete' => false, // Удалять ли точку, после того как ни одной очереди не будет связанно с ней
		'internal'    => false,
		'nowait'      => false,
	);


	public function __construct(array $parameters)
	{
		foreach ($parameters as $name => $value) {
			$this->{$name} = $value;
		}
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return string
	 */
	public function getVhost()
	{
		return $this->vhost;
	}

	/**
	 * @throws Exceptions\AMQPParentException
	 * @throws Exceptions\ConnectionsParams
	 * @return \AMQPConnection
	 */
	public function getConnect()
	{
		if (empty($this->connect)) {

			if (empty($this->host) || empty($this->port)) {
				throw new Exceptions\ConnectionsParams("Empty host or port for connection");
			}

			$this->connect = new \AMQPConnection();
			if (!empty($this->user)) {
				$this->connect->setLogin($this->user);
			}

			if (!empty($this->password)) {
				$this->connect->setPassword($this->password);
			}

			if (!empty($this->vhost)) {
				$this->connect->setVhost($this->vhost);
			}

			$this->connect->setPort($this->port);
			$this->connect->setHost($this->host);
		}

		$this->connect->connect();
		if(!$this->connect->isConnected()) {
			throw new Exceptions\AMQPParentException("Cannot connect to the amqp-broker ({$this->host})!");
		}

		return $this->connect;
	}

	/**
	 * Оборвать соединение и соединиться заново
	 */
	public function reconnect()
	{
		$this->disconnect();
		$this->getConnect();
	}

	public function disconnect()
	{
		try {
			if (is_object($this->connect) && ($this->connect instanceof \AMQPConnection) && $this->connect->isConnected()) {
				$this->connect->disconnect();
			}
		}
		finally {
			// если случилась критическая ошибка и надо уничтожить инстанс коннекта - очищаем пропертя в любом случае
			$this->connect = null;
			$this->channel = null;
			$this->exchange = null;
		}
	}

	public function hasConnection()
	{
		return $this->connect !== null;
	}

	/**
	 * @return \AMQPChannel
	 */
	public function getChannel()
	{
		if (empty($this->channel)) {
			$this->channel = new \AMQPChannel($this->getConnect());
		}

		return $this->channel;
	}

	/**
	 * @param $exchangeName
	 * @param string $exchangeType
	 * @param bool|array $exchangeParams
	 * @return \AMQPExchange
	 */
	public function getExchange($exchangeName, $exchangeType = 'topic', $exchangeParams = false)
	{
		if (empty($this->exchange)) {
			$this->exchange = new \AMQPExchange($this->getChannel());
			$this->exchange->setName($exchangeName);
			$this->exchange->setType($exchangeType);
			$this->exchange->setFlags($this->constructExchangeFlags((array)$exchangeParams));
		}

		return $this->exchange;
	}

	/**
	 * Создание бит-маски параметров очереди
	 * @param array $exchangeParams
	 * @return int
	 */
	private function constructExchangeFlags(array $exchangeParams = [])
	{
		return Utils::buildParamsBitMask($exchangeParams + self::$exchangeParams);
	}
}