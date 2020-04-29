<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use App\Services\PlayerService;

class TestController extends Controller
{
    protected $game;

    public function __construct(GameService $game)
    {
        $this->game = $game;
    }

    public function init()
    {
        $red_id  = "red_player";
        $blue_id = "blue_player";

        //添加玩家
        $this->game->createPlayer($red_id, 6, 1);

        //添加玩家
        $this->game->createPlayer($blue_id, 6, 10);
        for ($i = 0; $i <= 300; $i++) {
            $red_direct = mt_rand(0, 3);
            $this->game->playerMove($red_id, PlayerService::DIRECTION[$red_direct]);
            if ($this->game->isGameOver()) {
                $this->game->printGameMap();
                echo "game_over" . PHP_EOL;
                return;
            }
            $blue_direct = mt_rand(0, 3);
            $this->game->playerMove($blue_id, PlayerService::DIRECTION[$blue_direct]);
            if ($this->game->isGameOver()) {
                $this->game->printGameMap();
                echo "game_over" . PHP_EOL;
                return;
            }
            //打印移动后战局
            echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
            $this->game->printGameMap();
            usleep(200000);
        }
    }
}
