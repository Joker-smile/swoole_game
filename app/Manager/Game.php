<?php

namespace App\Manager;

use App\Model\Player;
use App\Model\Map;

class Game
{
    private $game_map = [];
    private $players  = [];

    public function __construct()
    {
        $this->game_map = new Map(12, 12);
    }

    public function createPlayer($player_id, $x, $y)
    {
        $player = new Player($player_id, $x, $y);
        if (!empty($this->players)) {
            $player->setType(Player::PLAYER_TYPE_HIDE);
        }
        $this->players[$player_id] = $player;
    }

    public function playerMove($player_id, $direction)
    {
        $player = $this->players[$player_id];
        if ($this->canMoveToDirection($player, $direction)) {
            $player->{$direction}();
        }
    }

    public function printGameMap()
    {
        $map_data = $this->game_map->getMapData();
        $font     = [2 => '追', 3 => '躲'];
        foreach ($this->players as $player) {
            $map_data[$player->getX()][$player->getY()] = $player->getType() + 1;
        }
        foreach ($map_data as $line) {
            foreach ($line as $item) {
                if (empty($item)) {
                    echo "墙，";
                } elseif ($item == 1) {
                    echo "    ";
                } else {
                    echo $font[$item] . '，';
                }
            }
            echo PHP_EOL;
        }
    }

    private function canMoveToDirection($player, $direction)
    {
        $x = $player->getX();
        $y = $player->getY();
        $move_coor = $this->getMoveCoor($x, $y, $direction);
        $map_data = $this->game_map->getMapData();
        if (!$map_data[$move_coor[0]][$move_coor[1]]) {
            return false;
        }
        return true;
    }

    private function getMoveCoor($x, $y, $direction)
    {
        switch ($direction) {
            case Player::UP:
                return [--$x, $y];
            case Player::DOWN:
                return [++$x, $y];
            case Player::LEFT:
                return [$x, --$y];
            case Player::RIGHT:
                return [$x, ++$y];
        }
        return [$x, $y];
    }

    public function isGameOver()
    {
        $result = false;
        $x = -1;
        $y = -1;
        $players = array_values($this->players);
        foreach ($players as $key => $player) {
            if ($key == 0) {
                $x = $player->getX();
                $y = $player->getY();
            } elseif ($x == $player->getX() && $y == $player->getY()) {
                $result = true;
            }
        }
        return $result;
    }
}
