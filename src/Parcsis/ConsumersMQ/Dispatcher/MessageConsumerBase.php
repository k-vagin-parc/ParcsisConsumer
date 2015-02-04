<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Dispatcher;


use Parcsis\ConsumersMQ\Consumer;
use Parcsis\ConsumersMQ\Queue;

abstract class MessageConsumerBase extends MessageDispatcherBase implements IConsumer
{
	const DEFAULT_RETRY_COUNT = 5;

	protected $queueParams = [];
	protected $exchangeName = '';

	/**
	 * один консьюмер может забирать несколько типов сообщений
	 * по умолчанию ключ = #
	 * @var
	 */
	protected $routingKeys = [];

	public function __construct()
	{
		$this->init();
		$this->connectToRabbit();
	}

	/**
	 * инициализируем очередь
	 * привязывает ее к точке обмена
	 */
	protected function connectToRabbit()
	{
		if (empty($this->queueName)) {
			throw new Exceptions\ConsumerParams("Empty queue name");
		}

		if (empty($this->exchangeName)) {
			throw new Exceptions\ConsumerParams("Empty exchange name");
		}

		/** @var \Parcsis\ConsumersMQ\Connection $connectObject */
		$connectObject = \App::make('ConnectMQ');
		$this->queue = new Queue($this->queueName, $connectObject->getChannel(), $this->exchangeName, $this->queueParams);

		foreach ($this->routingKeys as $key) {
			$this->queue->queueBind($this->exchangeName, $key);
		}
	}

	protected function init() {}

	private function reconnect(\Parcsis\ConsumersMQ\Connection $connectObject)
	{
		$connectObject->disconnect();
		$this->connectToRabbit();
	}

	/**
	 * @param int|null $timeout
	 * @return string
	 */
	public function consume($timeout = null)
	{
		$try = 5;

		/** @var \Parcsis\ConsumersMQ\Connection $connectObject */
		$connectObject = \App::make('ConnectMQ');
		$connection = $connectObject->getConnect();
		$consumer = new Consumer();

		$oldReadTimeout = $connection->getReadTimeout();

		while ($try > 0) {

			try {
				if ($timeout > 0) {
					$connection->setReadTimeout($timeout);
				}
				else {
					$connection->setReadTimeout(0);
				}

				$consumer->consume($this->queue, [$this, '_callback'], $this->auto_ack);
				break;
			}
			catch (Exceptions\AMQPParentException $e) {
				$try--;
				$this->reconnect($connectObject);
			}
			catch (Exceptions\AMQPChannelException $e) {
				// если ошибки уровня протокола - значит уже ничего не можем сделать
				\Log::warning('AMQPChannelException while consuming', ['queue' => $this->queueName, 'auto_ack' => $this->auto_ack, 'exception' => var_export($e, true)]);
				$connectObject->disconnect();
			}
			catch (Exceptions\AMQPConnectionException $e) {
				if (!$timeout) {
					// NOTE: in fact in case of timeout other than timeout exception may occurs here
					\Log::warning('AMQPConnectionException while consuming', ['queue' => $this->queueName, 'auto_ack' => $this->auto_ack, 'exception' => var_export($e, true)]);
				}
				$connection->disconnect();
				if ($timeout > 0) {
					break;
				}
			}
			finally {
				if ($connectObject->hasConnection()) {
					$connection->setReadTimeout($oldReadTimeout);
				}
			}
		}

		return $this->getReturnReason();
	}

	public function queueDeclare($queueName, $parametersQueue = [])
	{
		$this->queueName = $queueName;
		$this->queueParams = $parametersQueue;
	}

	public function queueBind($exchangeName, $routingKey = '#')
	{
		$this->routingKeys[] = $routingKey;
		$this->exchangeName = $exchangeName;
	}
}