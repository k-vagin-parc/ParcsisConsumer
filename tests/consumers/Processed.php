<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


trait Processed
{
	protected function processed($id, $isEven)
	{
		$number = \DB::table('calculated')->where('id', $id)->get();

		$mark = 0;
		$error = '';
		try {
			$mark = $this->handler($number, $isEven);
		}
		catch (\Exception $e) {
			$mark = 2;
			$error = $e->getMessage() . ' (' . __CLASS__ . ')';
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

	private function handler($number, $isEven)
	{
		if (empty($number)) {
			throw new \Exception("Empty");
		}

		$number = (array)$number[0];

		$number['in'] = (int)$number['in'];
		$number['out'] = (int)$number['out'];

		if ($number['in'] % 4 === 0 || $number['in'] % 3 === 0) {
			return 0;
		}

		if ($number['out'] !== 0) {
			throw new \Exception("Calculated yet");
		}

		if ($isEven && $number['in'] % 2 !== 0) {
			throw new \Exception("Not even");
		}
		elseif (!$isEven && $number['in'] % 2 === 0) {
			throw new \Exception("Not odd");
		}

		return 1;
	}
}