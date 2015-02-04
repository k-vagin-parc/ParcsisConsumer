<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


class Consumer
{
	private $_external_callback;
	private $_auto_ack = false;
	private $exchangeName = '';

	/**
	 * @var \AMQPQueue
	 */
	private $queue = null;

	/**
	 * Получение сообщения из очереди, если очередь пуста, ждать
	 * пока не появится сообщение
	 *
	 * @param Queue $queue
	 * @param string|array $callback
	 * @param bool $auto_ack
	 * @throws Exceptions\AMQPParentException
	 */
	public function consume(Queue $queue, $callback, $auto_ack = true)
	{
		if (empty($callback)) {
			throw new Exceptions\AMQPParentException('Callback function is null');
		}

		$this->_external_callback = $callback;
		$this->_auto_ack = $auto_ack;

		$this->queue = $queue->getQueue();
		$this->exchangeName = $queue->getExchangeName();

		$firstCallback = [
			$this,
			'consumeCallback',
		];

		// Слушаем очередь, пока callback не вернёт false
		$this->queue->consume($firstCallback);

		// Почистим
		$this->_external_callback = null;
		$this->_auto_ack = false;
	}

	public function consumeCallback(\AMQPEnvelope $msg)
	{
		$return = true;
		if ($msg !== null) {
			$msg = $this->messageConstructor($msg);

			if ($this->_auto_ack) {
				// Отвечаем, что сообщение получено
				$this->queue->ack($msg->getDeliveryTag());
			}
		}

		// Вызываем callback, если это нужно
		if ($this->_external_callback !== null) {
			$return = call_user_func($this->_external_callback, $msg);
		}

		return $return;
	}

	protected function messageConstructor(\AMQPEnvelope $msg)
	{
		// $consumer_tag — Имя очереди для отмены,
		// если объект очереди уже не является представителем очереди.
		// см. http://ru.php.net/manual/ru/amqpqueue.cancel.php
		// Вообще-то, этот тег должен присваиваться при вызове
		// AMQPQueue::consume() или приходить вместе с сообщением
		// но в мануалах пока что об этом ни слова.
		// @TODO: Отдебажить сообщение, может можно его оттуда выдернуть
		// через $this->_msg->getHeader('consumer_tag')
		$consumer_tag = $this->queue->getName();

		// Соберём собственное сообщение избавившись от лишнего
		try {
			$msg = new \AMQPBrokerMessage(
				$consumer_tag,
				$msg->getDeliveryTag(),
				$this->exchangeName,
				$msg->getRoutingKey(),
				unserialize($msg->getBody())
			);
		}
		catch (\MessageException $e) {
			\Log::notice('AMQP Message Exception', ['exception' => var_export($e->getMessage(), true)]);
		}

		return $msg;
	}
}