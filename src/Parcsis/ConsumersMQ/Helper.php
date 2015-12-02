<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ;


class Helper
{
    /**
     * вернуть количество сообщение в очереди на данный момент по ее имени
     * @param string $queueName
     * @param string $user
     * @param string $password
     * @param string $host
     * @param int $httpPort  порт web интерфейса кролика
     * @return int
     * @throws \Exception
     */
    public static function getQueueMessagesCount($queueName, $user, $password, $host, $httpPort)
    {
        $url = sprintf("http://%s:%s@%s:%s/api/queues/%s/%s", $user, $password, $host, $httpPort, '%2F', $queueName);

        $json = file_get_contents($url);
        $raw = json_decode($json, true);

        if (!array_key_exists('messages', $raw)) {
            throw new \Exception("bad response!");
        }

        return (int)$raw['messages'];
    }
}