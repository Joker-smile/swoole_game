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
        return self::redis()->sCard($key);
    }

    public static function pushPlayerToWaitList($player_id)
    {
        $key = self::PREFIX_KEY . ":player_wait_list";
        self::redis()->sAdd($key, $player_id);
    }

    public static function popPlayerFromWaitList()
    {
        $key = self::PREFIX_KEY . ":player_wait_list";
        return self::redis()->sPop($key);
    }

    public static function delPlayerFromWaitList($player_id)
    {
        $key = self::PREFIX_KEY . ":player_wait_list";
        self::redis()->sRem($key, $player_id);
    }

    public static function getPlayerFd($player_id)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'player_fd:' . $player_id;
        return self::redis()->hGet($key, $field);
    }

    public static function setPlayerFd($player_id, $player_fd)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'player_fd:' . $player_id;
        self::redis()->hSet($key, $field, $player_fd);
    }

    public static function delPlayerFd($player_id)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'player_fd:' . $player_id;
        self::redis()->hDel($key, $field);
    }

    public static function getPlayerId($player_fd)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'player_id:' . $player_fd;
        return self::redis()->hGet($key, $field);
    }

    public static function setPlayerId($player_fd, $player_id)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'player_id:' . $player_fd;
        self::redis()->hSet($key, $field, $player_id);
    }

    public static function delPlayerId($player_fd)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'player_id:' . $player_fd;
        self::redis()->hDel($key, $field);
    }

    public static function setPlayerInfo($player_id, $player_fd)
    {
        self::setPlayerId($player_fd, $player_id);
        self::setPlayerFd($player_id, $player_fd);
        self::setOnlinePlayer($player_id);
    }

    public static function delPlayerInfo($player_fd)
    {
        $player_id = self::getPlayerId($player_fd);
        self::delPlayerFd($player_id);
        self::delPlayerId($player_fd);
        self::delOnlinePlayer($player_id);
        self::delPlayerFromWaitList($player_id);
    }

    public static function initDataCenter()
    {
        //清空匹配队列
        $key = self::PREFIX_KEY . ':player_wait_list';
        self::redis()->del($key);

        //清空在线玩家
        $key = self::PREFIX_KEY . ':online_player';
        self::redis()->del($key);

        //清空玩家信息
        $key = self::PREFIX_KEY . ':player_info';
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
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'room_id:' . $player_id;
        self::redis()->hSet($key, $field, $room_id);
    }

    public static function getPlayerRoomId($player_id)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'room_id:' . $player_id;
        return self::redis()->hGet($key, $field);
    }

    public static function delPlayerRoomId($player_id)
    {
        $key = self::PREFIX_KEY . ':player_info';
        $field = 'room_id:' . $player_id;
        self::redis()->hDel($key, $field);
    }

    public static function setOnlinePlayer($player_id)
    {
        $key = self::PREFIX_KEY . ':online_player';
        self::redis()->hSet($key, $player_id, 1);
    }

    public static function getOnlinePlayer($player_id)
    {
        $key = self::PREFIX_KEY . ':online_player';
        return self::redis()->hGet($key, $player_id);
    }

    public static function delOnlinePlayer($player_id)
    {
        $key = self::PREFIX_KEY . ':online_player';
        self::redis()->hDel($key, $player_id);
    }

    public static function lenOnlinePlayer()
    {
        $key = self::PREFIX_KEY . ':online_player';
        return self::redis()->hLen($key);
    }

    public static function addPlayerWinTimes($playerId)
    {
        $key = self::PREFIX_KEY . ':player_rank';
        self::redis()->zIncrBy($key, 1, $playerId);
    }

    public static function getPlayersRank()
    {
        $key = self::PREFIX_KEY . ':player_rank';
        return self::redis()->zRevRange($key, 0, 9, true);
    }

}
