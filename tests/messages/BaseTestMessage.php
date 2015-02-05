<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;


abstract class BaseTestMessage extends \MessageBase
{
	/**
	 * Валидация сообщения перед отправкой
	 *
	 */
	protected function validate()
	{}
} 