<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;

use \Parcsis\ConsumersMQ\Connection;

class ConnectionTest extends \TestCase
{
	public function testService()
	{
		$connect = \ConnectMQ::getConnect();
		$this->assertInstanceOf('\PhpAmqpLib\Connection\AMQPConnection', $connect);
	}

	/**
	 * @expectedException \PhpAmqpLib\Exception\AMQPRuntimeException
	 */
	public function testServiceFail()
	{
		\App::bind('Connect', function() {
			$configuration = \Config::get('consumers-mq::connection');
			$configuration['port'] = -1;
			return new Connection($configuration);
		});

		\ConnectMQ::getConnect();
	}

	/**
	 * @expectedException \Parcsis\ConsumersMQ\Exceptions\ConnectionsParams
	 */
	public function testServiceWrongConfig()
	{
		\App::bind('Connect', function() {
			$configuration = \Config::get('consumers-mq::connection');
			$configuration['port'] = '';
			return new Connection($configuration);
		});

		\ConnectMQ::getConnect();
	}
}