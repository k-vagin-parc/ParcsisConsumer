<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;

/**
 * Тестовое сообщение в консьюмер
 * Class TestMessage
 * @package Parcsis\ConsumersMQ
 */
class TestMessage extends \MessageBase
{
	const
		QUEUE		= 'consumers.mq.test',
		EXCHANGE 	= 'consumers-mq-test-exchange';

	/**
	 * Тип сообщения
	 *
	 * @var string
	 * @access protected
	 */
	protected $_message_type = self::QUEUE;

	protected $message = '';

	/**
	 * Валидация сообщения перед отправкой
	 *
	 */
	protected function validate()
	{

	}
} 