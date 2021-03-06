<?php

namespace App\Manager;

//专门管理与发送相关的变量和方法
class Sender
{
    //作为发送room_id的code
    const MSG_ROOM_ID = 1001;
    const MSG_WAIT_PLAYER = 1002;
    const MSG_ROOM_START = 1003;
    const MSG_GAME_INFO = 1004;
    const MSG_GAME_OVER = 1005;
    const MSG_OTHER_CLOSE = 1006;
    const MSG_OPPONENT_OFFLINE = 1007;
    const MSG_MAKE_CHALLENGE = 1008;
    const MSG_REFUSE_CHALLENGE = 1009;

    const CODE_MSG = [
        self::MSG_ROOM_ID => '房间ID',
        self::MSG_WAIT_PLAYER => '等待其他玩家中……',
        self::MSG_ROOM_START => '游戏开始啦~',
        self::MSG_GAME_INFO => 'game info',
        self::MSG_GAME_OVER => '游戏结束啦~',
        self::MSG_OTHER_CLOSE => '你的敌人跑路了',
        self::MSG_OPPONENT_OFFLINE => '对手不在线',
        self::MSG_MAKE_CHALLENGE => '发起挑战',
        self::MSG_REFUSE_CHALLENGE => '对方拒绝了你的挑战',
    ];

    public static function sendMessage($player_id, $code, $data = [])
    {
        $message  = [
            'code' => $code,
            'msg'  => self::CODE_MSG[$code] ?? '',
            'data' => $data
        ];
        $player_fd = DataCenter::getPlayerFd($player_id);
        if (empty($player_fd)) {
            return;
        }
        DataCenter::$server->push($player_fd, json_encode($message));
    }

}