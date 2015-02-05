<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


class OnInsertInMessage extends BaseTestMessage
{
	protected $_message_type = FunctionalTest::INSERT_QUEUE;

	protected $number = 0;

	/**
	 * @param int $number
	 */
	public function setNumber($number)
	{
		$this->number = $number;
	}
}