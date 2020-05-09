<?php

namespace App\Manager;

use App\Model\Player;

class Logic
{
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
        DataCenter::setPlayerRoomId($player_id, $room_id);
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
         * @var Game $game_manager
         */
        $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
        if (empty(count($game_manager->getPlayers()))) {
            //第一个玩家
            $game_manager->createPlayer($player_id, 6, 1);
            Sender::sendMessage($player_id, Sender::MSG_WAIT_PLAYER);
        } else {
            //第二个玩家
            $game_manager->createPlayer($player_id, 6, 10);
            DataCenter::$global['rooms'][$room_id]['timer_id'] = $this->createGameTimer($room_id);
            Sender::sendMessage($player_id, Sender::MSG_ROOM_START);
            $this->sendGameInfo($room_id);
        }
    }

    public function closeRoom($closer_id)
    {
        $room_id = DataCenter::getPlayerRoomId($closer_id);
        if (!empty($room_id)) {
            /**
             * @var Game $game_manager
             * @var Player $player
             */
            $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
            $players      = $game_manager->getPlayers();
            foreach ($players as $player) {
                if ($player->getId() != $closer_id) {
                    Sender::sendMessage($player->getId(), Sender::MSG_OTHER_CLOSE);
                }
                DataCenter::delPlayerRoomId($player->getId());
            }
            unset(DataCenter::$global['rooms'][$room_id]);
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

        //必须倒序输出，因为游戏设定数组第一个是寻找者，第二个是躲藏者，叠加时赢的是寻找者。
        foreach (array_reverse($players) as $player) {
            $map_data[$player->getX()][$player->getY()] = $player->getId();
        }

        foreach ($players as $player) {
            $data = [
                'players'    => $players,
                'map_data'   => $this->getNearMap($map_data, $player->getX(), $player->getY()),
                'time_limit' => get_config_by_key('game_time_limit')
            ];
            Sender::sendMessage($player->getId(), Sender::MSG_GAME_INFO, $data);
        }
    }

    private function checkGameOver($room_id)
    {
        /**
         * @var Game $game_manager
         * @var Player $player
         */
        $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
        if ($game_manager->isGameOver()) {
            $players = $game_manager->getPlayers();
            $winner  = current($players)->getId();
            $this->gameOver($room_id, $winner);
        }
    }

    private function createGameTimer($room_id)
    {
        return swoole_timer_after(get_config_by_key('game_time_limit') * 1000, function () use ($room_id) {
            if (isset(DataCenter::$global['rooms'][$room_id])) {
                //游戏还未结束则主动结束游戏
                /**
                 * @var Game $game_manager
                 */
                $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
                $players      = $game_manager->getPlayers();
                $winner       = end($players)->getId();
                $this->gameOver($room_id, $winner);
            }
        });
    }

    private function gameOver($room_id, $winner)
    {
        /**
         * @var Game $game_manager
         * @var Player $player
         */
        $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
        $players      = $game_manager->getPlayers();
        DataCenter::addPlayerWinTimes($winner);
        foreach ($players as $player) {
            Sender::sendMessage($player->getId(), Sender::MSG_GAME_OVER, ['winner' => $winner]);
            DataCenter::delPlayerRoomId($player->getId());
        }
        unset(DataCenter::$global['rooms'][$room_id]);
    }

    private function getNearMap($map_data, $x, $y)
    {
        $player_display_len = get_config_by_key('player_display_len');
        $result             = [];
        for ($i = -1 * $player_display_len; $i <= $player_display_len; $i++) {
            $tmp = [];
            for ($j = -1 * $player_display_len; $j <= $player_display_len; $j++) {
                $tmp[] = $map_data[$x + $i][$y + $j] ?? 0;
            }
            $result[] = $tmp;
        }
        return $result;
    }

    public function movePlayer($direction, $player_id)
    {
        if (!in_array($direction, Player::DIRECTION)) {
            echo $direction;
            return;
        }
        $room_id = DataCenter::getPlayerRoomId($player_id);
        if (isset(DataCenter::$global['rooms'][$room_id])) {

            $this->checkGameOver($room_id);
            /**
             * @var Game $game_manager
             */
            $game_manager = DataCenter::$global['rooms'][$room_id]['manager'];
            $game_manager->playerMove($player_id, $direction);
            $this->sendGameInfo($room_id);
        }
    }

    public function makeChallenge($invite_id, $player_id)
    {
        if (empty(DataCenter::getOnlinePlayer($invite_id))) {
            Sender::sendMessage($player_id, Sender::MSG_OPPONENT_OFFLINE);
        } else {
            $data = [
                'challenger_id' => $player_id
            ];
            Sender::sendMessage($invite_id, Sender::MSG_MAKE_CHALLENGE, $data);
        }
    }

    public function acceptChallenge($challenger_id, $player_id)
    {
        $this->createRoom($challenger_id, $player_id);
    }

    public function refuseChallenge($challenger_id)
    {
        Sender::sendMessage($challenger_id, Sender::MSG_REFUSE_CHALLENGE);
    }
}
