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
	private $connect = null;
	private $channel = null;

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
	 * @return \AMQPChannel
	 */
	public function getChannel()
	{
		if (empty($this->channel)) {
			$this->channel = new \AMQPChannel($this->getConnect());
		}

		return $this->channel;
	}
}