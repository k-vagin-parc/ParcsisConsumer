<?php
/**
 * консьюмер обрабатывает числа, делящиеся на 4 или 3
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


class CalculateOther extends \Parcsis\ConsumersMQ\Dispatcher\MessageConsumerBase
{
	protected function init()
	{
		parent::init();
		$this->queueDeclare(FunctionalTest::PROCESSED_Q . '-3');
		$this->queueBind(FunctionalTest::EXCHANGE, FunctionalTest::TYPE_EVEN);
		$this->queueBind(FunctionalTest::EXCHANGE, FunctionalTest::TYPE_ODD);
	}

	protected function callback(\AMQPBrokerMessage $msg)
	{
		$id = $msg['body']['id'];

		if ($id < 0) {
			// сигнал остановки
			echo 'End.', PHP_EOL;
			exit(0);
		}

		$number = \DB::table('calculated')->where('id', $id)->get();

		$mark = 0;
		$error = '';
		try {
			$mark = $this->handler($number);
		}
		catch (\Exception $e) {
			$mark = 2;
			$error = $e->getMessage() . ' (consumer other)';
		}
		finally {
			// mark:
			if ($mark > 0) {
				\DB::table('calculated')->where('id', $id)->update([
					'out'	=> $mark,
					'error'	=> $error,
					'owner'	=> empty($number[0]->owner) ? __CLASS__ : $number[0]->owner // сохраняем предыдущего обработчика
				]);
			}
		}
	}

	private function handler($number)
	{
		if (empty($number)) {
			throw new \Exception("Empty");
		}

		$number = (array)$number[0];

		$number['in'] = (int)$number['in'];
		$number['out'] = (int)$number['out'];

		if ($number['in'] % 4 !== 0 && $number['in'] % 3 !== 0) {
			//не свои цифры просто пропускаем
			return 0;
		}

		if ($number['out'] !== 0) {
			throw new \Exception("Calculated yet");
		}

		return 1;
	}
}