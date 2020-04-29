<?php

namespace App\Http\Controllers;

use App\Services\GameService;

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

        //移动坐标
        $this->game->playerMove($red_id, 'up');
        $this->game->playerMove($red_id, 'up');
        $this->game->playerMove($red_id, 'up');

        //打印地图
        $this->game->printGameMap();
    }
}
