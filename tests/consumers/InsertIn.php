<?php
/**
 * консьюмер вставляет данные в базу
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


class InsertIn extends \Parcsis\ConsumersMQ\Dispatcher\MessageConsumerBase
{
	/**
	 * Максимальное количество запросов обрабатываемых процессом
	 * 0 - не ограничено
	 */
	protected $maxChildRequests = 1000;

	protected function init()
	{
		parent::init();
		$this->queueDeclare(FunctionalTest::INSERT_QUEUE);
		$this->queueBind(FunctionalTest::EXCHANGE, FunctionalTest::INSERT_QUEUE);
	}


	protected function callback(\AMQPBrokerMessage $msg)
	{
		$number = $msg['body']['number'];

		$id = \DB::table('calculated')->insertGetId([
			'in'	=> $number,
			'out'	=> 0,
		]);

		if ((int)$number % 2 === 0) {
			$message = new AfterEvenInsertMessage;
		}
		else {
			$message = new AfterOddInsertMessage;
		}

		$message->setId($id);

		\Parcsis\ConsumersMQ\Publisher::publish($message, FunctionalTest::EXCHANGE);
	}
}