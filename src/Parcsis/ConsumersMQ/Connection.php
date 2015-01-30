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
	 * @throws Exceptions\ConnectionsParams
	 * @return null
	 */
	public function getConnect()
	{
		if (empty($this->connect)) {

			if (empty($this->host) || empty($this->port)) {
				throw new Exceptions\ConnectionsParams("empty host or port for connection");
			}

			$this->connect = new \PhpAmqpLib\Connection\AMQPConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
		}

		return $this->connect;
	}
}