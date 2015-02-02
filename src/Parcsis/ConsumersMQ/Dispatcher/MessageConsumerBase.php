<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Dispatcher;


abstract class MessageConsumerBase extends MessageDispatcherBase
{
	/**
	 * Время ожидания до следующей попытки скушать сообщение
	 */
	protected $waitTimeout = null;

	/**
	 * Время ожидания получения хотя бы одного сообщения (не NULL только при тестировании)
	 * @var null
	 */
	protected $getTimeout = null;

	protected $requests = 0;

	const
		DEFAULT_RETRY_COUNT 	= 5,
		CHILD_MAX_REQUEST 		= 1000,
		WAIT_TIMEOUT 			= 5;

	/**
	 * если интервал между сообщениями достиг дня,
	 * то значит что-то не так.
	 */
	const WAIT_THRESHOLD = 86400;

	public function __construct()
	{
		$this->init();
	}

	protected function init() {}

	/**
	 * Инициализация консьюмера
	 *
	 * @param string $exchangeName	имя точки обмена
	 * @param int|null $timeout		время, в течении которого сообщение должно поступить в консьюмер (необходимо для отладки, если null - неограничено)
	 * @return mixed
	 */
	public function consume($exchangeName, $timeout = null)
	{
		$this->getTimeout = $timeout;

		$try = self::DEFAULT_RETRY_COUNT;

		while ($try > 0) {

			try {
				/** @var \PhpAmqpLib\Connection\AMQPConnection $connect */
				$connect = \ConnectMQ::getConnect();
				$channel = $connect->channel();

				$channel->basic_consume($this->queueName, '', false, false, false, false, [$this, 'callback']);
				$this->channelListener($channel, $connect);
			}
			finally {

			}

			/*try {
				AMQP::getInstance()->consume($this->queue_name, array($this, '_callback'), $this->auto_ack );
				break;
			} catch (AMQPParentException $e) {
				$try--;
				AMQP::getInstance()->reconnect();
				// если ошибки уровня протокола - значит уже ничего не можем сделать
			} catch (AMQPChannelException $e) {
				sleep(MINUTE);
				Logger::log('AMQPChannelException while consuming ' . $e->getMessage());
				die('amqp connection broken, terminating');
			}*/
		}

		return $this->getReturnReason();
	}

	protected function channelListener(\PhpAmqpLib\Channel\AMQPChannel $channel, \PhpAmqpLib\Connection\AMQPConnection $connection)
	{
		$startTime = microtime(true);
		while (count($channel->callbacks)) {
			$read = array($connection->getSocket()); // add here other sockets that you need to attend
			$write = null;
			$except = null;
			if (false === ($num_changed_streams = stream_select($read, $write, $except, 60))) {
				/* Error handling */
			} elseif ($num_changed_streams > 0) {
				$channel->wait();
			}

			// если передано время ожидания сообщения и оно превышено - кидаем исключение
			if (!empty($this->getTimeout) && (microtime(true) - $startTime > $this->getTimeout)) {
				throw new Exceptions\Timeout;
			}
		}
	}

	protected function callback(\PhpAmqpLib\Message\AMQPMessage $msg)
	{}

	/**
	 * Выполняем коммит и если он успешен, помечает сообщение как
	 * обработанное, иначе - откатывает транзакцию.
	 *
	 * @param bool $updated
	 * @param \AMQPBrokerMessage $msg
	 * @return bool можно ли продолжать работу
	 */
	protected function _ackOnSuccessAndCommit($updated, \AMQPBrokerMessage $msg)
	{
		if (($updated === false) || !self::$db->commit()) {
			self::$db->rollback();

			sleep($this->waitTimeout);

			$this->waitTimeout *= 2;

			// если превысили предел обработки сообщения - отвечаем что обработали
			if ($this->waitTimeout > self::WAIT_THRESHOLD) {
				AMQP::getInstance()->reject($msg->getDeliveryTag(), true);

				return true;
			}

			return false;
		} else {
			$this->ack($msg);

			$this->waitTimeout = self::WAIT_TIMEOUT;

			return true;
		}
	}

	/**
	 * Откатывает сообщение при ошибке, чтобы попробывать обработать позже.
	 *
	 * @param \AMQPBrokerMessage|array $msg
	 * @return bool
	 */
	protected function _rejectOnFailureAndRollback(\AMQPBrokerMessage $msg)
	{
		self::$db->rollback();

		sleep($this->waitTimeout);

		$this->reject($msg, true);

		return true;
	}

	/**
	 * Выкидывает сообщение из очереди в случае необрабатываемой ошибки.
	 * @param \AMQPBrokerMessage $msg
	 * @return bool
	 */
	protected function _ackOnFailureAndRollback(\AMQPBrokerMessage $msg)
	{
		self::$db->rollback();

		sleep($this->waitTimeout);

		$this->ack($msg);

		return true;
	}

	protected function getRetryCount()
	{
		return static::DEFAULT_RETRY_COUNT;
	}

	protected function _processInTransaction(\MessageBaseUntyped $msg)
	{
		throw new \Exception('Not implemented');

		return array(true, true);
	}
}