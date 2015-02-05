<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


class AfterEvenInsertMessage extends BaseTestMessage
{
	protected $_message_type = FunctionalTest::TYPE_EVEN;

	protected $id = 0;

	/**
	 * @param int
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
}