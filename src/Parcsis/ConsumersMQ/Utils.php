<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;

/**
 * хелперы
 * Class Utils
 * @package Parcsis\ConsumersMQ
 */
class Utils
{
	/**
	 * Маппинг параметров очереди на флаги AMQP-extension
	 * @var array
	 */
	private static $queueParamsToTlagsMap = array(
		'passive'     => AMQP_PASSIVE,
		'durable'     => AMQP_DURABLE, // Восстанавливать очередь после перезагрузки. такую очередь можно привязать только к точке с durable = true
		'exclusive'   => AMQP_EXCLUSIVE,
		'auto_delete' => AMQP_AUTODELETE, // Удаление очереди, после дисконекта клиента
		'nowait'      => AMQP_NOWAIT,
	);

	public static function buildParamsBitMask(array $params = [])
	{
		$flags = 0;
		foreach ($params as $key => $param) {
			if ($param) {
				$flags |= self::$queueParamsToTlagsMap[$key];
			}
		}

		return $flags;
	}
}