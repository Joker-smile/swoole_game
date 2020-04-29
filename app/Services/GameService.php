<?php


namespace App\Services;


class GameService
{
    private $game_map = [];
    private $players  = [];

    public function __construct()
    {
        $this->game_map = new MapService(12, 12);
    }

    public function createPlayer($player_id, $x, $y)
    {
        $player = new PlayerService($player_id, $x, $y);
        if (!empty($this->players)) {
            $player->setType(PlayerService::PLAYER_TYPE_HIDE);
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

    }
}
