<?php

namespace App\Manager;

use App\Model\Player;

class Logic
{
    const PLAYER_DISPLAY_LEN = 2;

    public function matchPlayer($player_id)
    {
        //将用户放入队列中
        DataCenter::pushPlayerToWaitList($player_id);
        //发起一个Task尝试匹配,通过code来区分每个task任务
        DataCenter::$server->task(['code' => TaskManager::TASK_CODE_FIND_PLAYER]);
    }

    public function createRoom($red_player, $blue_player)
    {
        $room_id = uniqid('room_', true);
        $this->bindRoomWorker($red_player, $room_id);
        $this->bindRoomWorker($blue_player, $room_id);
    }

    private function bindRoomWorker($player_id, $room_id)
    {
        $player_fd = DataCenter::getPlayerFd($player_id);
        DataCenter::$server->bind($player_fd, crc32($room_id));
        DataCenter::$server->push($player_fd, $room_id);
        Sender::sendMessage($player_id, Sender::MSG_ROOM_ID, ['room_id' => $room_id]);
    }

    public function startRoom($room_id, $player_id)
    {
        if (!isset(DataCenter::$global['rooms'][$room_id])) {
            DataCenter::$global['rooms'][$room_id] = [
                'id'      => $room_id,
                'manager' => new Game()
            ];
        }

        /**
         * @var Game $gameManager
         */
        $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
        if (empty(count($game_manager->getPlayers()))) {
            //第一个玩家
            $game_manager->createPlayer($player_id, 6, 1);
            Sender::sendMessage($player_id, Sender::MSG_WAIT_PLAYER);
        } else {
            //第二个玩家
            $game_manager->createPlayer($player_id, 6, 10);
            Sender::sendMessage($player_id, Sender::MSG_ROOM_START);
            $this->sendGameInfo($room_id);
        }
    }

    private function sendGameInfo($room_id)
    {
        /**
         * @var Game $game_manager
         * @var Player $player
         */
        $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
        $players      = $game_manager->getPlayers();
        $map_data     = $game_manager->getMapData();
        foreach ($players as $player) {
            $map_data[$player->getX()][$player->getY()] = $player->getId();
        }
        foreach ($players as $player) {
            $data = [
                'players'  => $players,
                'map_data' => $this->getNearMap($map_data, $player->getX(), $player->getY())
            ];
            Sender::sendMessage($player->getId(), Sender::MSG_GAME_INFO, $data);
        }
    }

    private function getNearMap($map_data, $x, $y)
    {
        $result = [];
        for ($i = -1 * self::PLAYER_DISPLAY_LEN; $i <= self::PLAYER_DISPLAY_LEN; $i++) {
            $tmp = [];
            for ($j = -1 * self::PLAYER_DISPLAY_LEN; $j <= self::PLAYER_DISPLAY_LEN; $j++) {
                $tmp[] = $map_data[$x + $i][$y + $j] ?? 0;
            }
            $result[] = $tmp;
        }
        return $result;
    }
}
