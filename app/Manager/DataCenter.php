<?php

namespace App\Manager;

use App\Lib\Redis;

class DataCenter
{
    const PREFIX_KEY = "game";
    public static $server;
    public static $global = [];

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

    public static function getPlayerWaitListLen()
    {
        $key = self::PREFIX_KEY . ":player_wait_list";
        return self::redis()->lLen($key);
    }

    public static function pushPlayerToWaitList($player_id)
    {
        $key = self::PREFIX_KEY . ":player_wait_list";
        self::redis()->lPush($key, $player_id);
    }

    public static function popPlayerFromWaitList()
    {
        $key = self::PREFIX_KEY . ":player_wait_list";
        return self::redis()->rPop($key);
    }

    public static function getPlayerFd($player_id)
    {
        $key = self::PREFIX_KEY . ":player_fd:" . $player_id;
        return self::redis()->get($key);
    }

    public static function setPlayerFd($player_id, $player_fd)
    {
        $key = self::PREFIX_KEY . ":player_fd:" . $player_id;
        self::redis()->set($key, $player_fd);
    }

    public static function delPlayerFd($player_id)
    {
        $key = self::PREFIX_KEY . ":player_fd:" . $player_id;
        self::redis()->del($key);
    }

    public static function getPlayerId($player_fd)
    {
        $key = self::PREFIX_KEY . ":player_id:" . $player_fd;
        return self::redis()->get($key);
    }

    public static function setPlayerId($player_fd, $player_id)
    {
        $key = self::PREFIX_KEY . ":player_id:" . $player_fd;
        self::redis()->set($key, $player_id);
    }

    public static function delPlayerId($player_fd)
    {
        $key = self::PREFIX_KEY . ":player_id:" . $player_fd;
        self::redis()->del($key);
    }

    public static function setPlayerInfo($player_id, $player_fd)
    {
        self::setPlayerId($player_fd, $player_id);
        self::setPlayerFd($player_id, $player_fd);
    }

    public static function delPlayerInfo($player_fd)
    {
        $player_id = self::getPlayerId($player_fd);
        self::delPlayerFd($player_id);
        self::delPlayerId($player_fd);
    }

    public static function initDataCenter()
    {
        //清空匹配队列
        $key = self::PREFIX_KEY . ':player_wait_list';
        self::redis()->del($key);

        //清空玩家ID
        $key    = self::PREFIX_KEY . ':player_id*';
        $values = self::redis()->keys($key);
        foreach ($values as $value) {
            self::redis()->del($value);
        }

        //清空玩家房间ID
        $key    = self::PREFIX_KEY . ':player_room_id*';
        $values = self::redis()->keys($key);
        foreach ($values as $value) {
            self::redis()->del($value);
        }

        //清空玩家FD
        $key    = self::PREFIX_KEY . ':player_fd*';
        $values = self::redis()->keys($key);
        foreach ($values as $value) {
            self::redis()->del($value);
        }
    }

    public static function setPlayerRoomId($player_id, $room_id)
    {
        $key = self::PREFIX_KEY . ':player_room_id:' . $player_id;
        self::redis()->set($key, $room_id);
    }

    public static function getPlayerRoomId($player_id)
    {
        $key = self::PREFIX_KEY . ':player_room_id:' . $player_id;
        return self::redis()->get($key);
    }

    public static function delPlayerRoomId($player_id)
    {
        $key = self::PREFIX_KEY . ':player_room_id:' . $player_id;
        self::redis()->del($key);
    }
}
