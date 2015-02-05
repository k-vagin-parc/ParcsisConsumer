<?php
/**
 * консьюмер обрабатывает нечетные числа
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


class CalculateOdd extends \Parcsis\ConsumersMQ\Dispatcher\MessageConsumerBase
{
	use Processed;

	protected function init()
	{
		parent::init();
		$this->queueDeclare(FunctionalTest::PROCESSED_Q . '-2');
		$this->queueBind(FunctionalTest::EXCHANGE, FunctionalTest::TYPE_ODD);
	}

	protected function callback(\AMQPBrokerMessage $msg)
	{
		$id = $msg['body']['id'];

		if ($id <= 0) {
			// сигнал остановки
			echo 'End.', PHP_EOL;
			exit(0);
		}

		$this->processed($id, false);
	}
}