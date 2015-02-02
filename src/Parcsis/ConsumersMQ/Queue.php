<?php
/**
 * абстракт над конкретной очередь конкретного консьюмера
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


class Queue
{
	/**
	 * Параметры объявления очереди по умолчанию
	 *
	 * @static
	 * @var array
	 */
	private static $queueParams = array(
		'passive'     => false,
		'durable'     => true, // Восстанавливать очередь после перезагрузки. такую очередь можно привязать только к точке с durable = true
		'exclusive'   => false,
		'auto_delete' => true, // Удаление очереди, после дисконекта клиента
		'nowait'      => false,
	);

	/**
	 * @return \AMQPQueue
	 */
	public function getQueue()
	{
		return $this->queue;
	}

	/**
	 * Маппинг параметров очереди на флаги AMQP-extension
	 * @var array
	 */
	private static $queueParamsToFlagsMap = array(
		'passive'     => AMQP_PASSIVE,
		'durable'     => AMQP_DURABLE, // Восстанавливать очередь после перезагрузки. такую очередь можно привязать только к точке с durable = true
		'exclusive'   => AMQP_EXCLUSIVE,
		'auto_delete' => AMQP_AUTODELETE, // Удаление очереди, после дисконекта клиента
		'nowait'      => AMQP_NOWAIT,
	);

	/**
	 * @var \AMQPQueue
	 */
	private $queue = null;

	/**
	 * @param string $queueName
	 * @param \AMQPChannel $channel
	 * @param string $exchangeName
	 * @param bool $queueParams
	 */
	public function __construct($queueName, \AMQPChannel $channel, $exchangeName, $queueParams = false)
	{
		$this->queue = new \AMQPQueue($channel);

		$this->queue->setName($queueName);
		$this->queue->setFlags($this->constructQueueFlags($queueParams));

		$this->queue->declareQueue();
	}

	/**
	 * Создание бит-маски параметров очереди
	 * @param array $queueParams
	 * @return int
	 */
	private function constructQueueFlags(array $queueParams = [])
	{
		// Загрузка и изменение параметров по умолчанию
		$defaultParams = self::$queueParams + $queueParams;

		$flags = 0;
		foreach ($defaultParams as $key => $value) {
			if ($value) {
				$flags |= self::$queueParamsToFlagsMap[$key];
			}
		}

		return $flags;
	}
}