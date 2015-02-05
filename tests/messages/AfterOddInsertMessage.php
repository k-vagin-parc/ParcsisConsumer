<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


class AfterOddInsertMessage extends BaseTestMessage
{
	protected $_message_type = FunctionalTest::TYPE_ODD;

	protected $id = 0;

	/**
	 * @param int
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
}