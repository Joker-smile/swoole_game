<?php

namespace App\Manager;

//跟task有关的常量、方法都归于这个类来管理

class TaskManager
{
    //用于发起寻找玩家task任务
    const TASK_CODE_FIND_PLAYER = 1;

    public static function findPlayer()
    {
        //当匹配队列长度大于等于2时，弹出队列前两个玩家的player_id并返回
        $player_list_len = DataCenter::getPlayerWaitListLen();
        if ($player_list_len >= 2) {
            $red_player = DataCenter::popPlayerFromWaitList();
            $blue_player = DataCenter::popPlayerFromWaitList();
            return [
                'red_player' => $red_player,
                'blue_player' => $blue_player
            ];
        }
        return false;
    }
}