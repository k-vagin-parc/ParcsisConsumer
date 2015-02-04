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
		'auto_delete' => false, // true - Удаление очереди, после дисконекта клиента
		'nowait'      => false,
	);

	private $exchangeName = '';

	/**
	 * @return string
	 */
	public function getExchangeName()
	{
		return $this->exchangeName;
	}

	/**
	 * @return \AMQPQueue
	 */
	public function getQueue()
	{
		return $this->queue;
	}

	/**
	 * @var \AMQPQueue
	 */
	private $queue = null;

	/**
	 * @param string $queueName
	 * @param \AMQPChannel $channel
	 * @param string $exchangeName
	 * @param array $queueParams
	 * @throws \Exception
	 */
	public function __construct($queueName, \AMQPChannel $channel, $exchangeName, $queueParams = [])
	{
		if (empty($exchangeName)) {
			throw new \Exception("Exchange can't empty!");
		}

		$this->exchangeName = $exchangeName;

		$this->queue = new \AMQPQueue($channel);
		$this->queue->setName($queueName);
		$this->queue->setFlags($this->constructQueueFlags((array)$queueParams));
		$this->queue->declareQueue(); // возвращает количество сообщений в очереди. это может быть полезно
	}

	/**
	 * Создание бит-маски параметров очереди
	 * @param array $queueParams
	 * @return int
	 */
	private function constructQueueFlags(array $queueParams = [])
	{
		return Utils::buildParamsBitMask($queueParams + self::$queueParams); // при сложении ассоциативный массивов, совпащающее значение в первом слагаемом остается, а во втором - оно игнорируется
		// поэтому чтобы переопределить значение по умолчанию надо новый массив с параметрами складывать с массивом параметров по умолчанию
	}

	/**
	 * Привязка очереди к точке обмена
	 *
	 * @param string $exchangeName имя точки обмена (необязательно)
	 * @param string $routingKey имя ключа привязки
	 * @return bool
	 */
	public function queueBind($exchangeName = '', $routingKey = '#')
	{
		$exchangeName = $exchangeName ? $exchangeName : $this->exchangeName;
		return $this->queue->bind($exchangeName, $routingKey);
	}
}