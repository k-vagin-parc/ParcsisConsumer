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
		/** @var \Parcsis\ConsumersMQ\Connection $connectObject */
		$connectObject = \App::make('ConnectMQ');
		$connect = $connectObject->getConnect();
		$channel = $connectObject->getChannel();

		$this->assertInstanceOf('\AMQPConnection', $connect);
		$this->assertInstanceOf('\AMQPChannel', $channel);
	}

	/**
	 * @expectedException \AMQPConnectionException
	 */
	public function testServiceFail()
	{
		\App::bind('ConnectMQ', function() {
			$configuration = \Config::get('consumers-mq.connection');
			$configuration['port'] = -1;
			return new Connection($configuration);
		});

		/** @var \Parcsis\ConsumersMQ\Connection $connectObject */
		$connectObject = \App::make('ConnectMQ');
		$connectObject->getConnect();
	}

	/**
	 * @expectedException \Parcsis\ConsumersMQ\Exceptions\ConnectionsParams
	 */
	public function testServiceWrongConfig()
	{
		\App::bind('ConnectMQ', function() {
			$configuration = \Config::get('consumers-mq.connection');
			$configuration['port'] = '';
			return new Connection($configuration);
		});

		/** @var \Parcsis\ConsumersMQ\Connection $connectObject */
		$connectObject = \App::make('ConnectMQ');
		$connectObject->getConnect();
	}
}