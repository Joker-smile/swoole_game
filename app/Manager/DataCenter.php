<?php

namespace App\Manager;

use App\Lib\Redis;

class DataCenter
{
    public static function log($info, $context = [], $level = 'INFO')
    {
        if ($context) {
            echo sprintf("[%s][%s]: %s %s\n", date('Y-m-d H:i:s'), $level, $info,
                json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            echo sprintf("[%s][%s]: %s\n", date('Y-m-d H:i:s'), $level, $info);
        }
    }

    public static function redis()
    {
        return Redis::getInstance();
    }

    public function pushPlayerToWaitList(int $player_id)
    {

    }

    public static function getPlayerId(int $fd)
    {

    }

    public static function delPlayerInfo(int $fd)
    {

    }

    public static function setPlayerInfo(int $player_id, int $fd)
    {

    }
}
