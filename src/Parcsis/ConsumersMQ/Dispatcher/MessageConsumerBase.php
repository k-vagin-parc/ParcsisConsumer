<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Dispatcher;


abstract class MessageConsumerBase extends MessageDispatcherBase implements IConsumer
{
	/**
	 * @var \Parcsis\ConsumersMQ\Queue
	 */
	protected $queue;

	/**
	 * Время ожидания до следующей попытки скушать сообщение
	 */
	protected $wait_timeout = null;

	/**
	 * Количество уже обработанных запросов процессом
	 */
	protected $requests = 0;

	/**
	 * Максимальное количество запросов обрабатываемых процессом
	 *
	 * NOTE: при использовании конфигов данная переменная имеет более низкий приоритет использования
	 */
	protected $max_child_requests = 1000;

	const WAIT_TIMEOUT = 5;
	const DEFAULT_RETRY_COUNT = 5;

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

}