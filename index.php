<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Manager\Game;
use App\Model\Player;

new \App\Server();

$game    = new Game();
$red_id  = "red_player";
$blue_id = "blue_player";

//添加玩家
$game->createPlayer($red_id, 6, 1);

//添加玩家
$game->createPlayer($blue_id, 6, 10);
for ($i = 0; $i <= 300; $i++) {
    $red_direct = mt_rand(0, 3);
    $game->playerMove($red_id, Player::DIRECTION[$red_direct]);
    if ($game->isGameOver()) {
        $game->printGameMap();
        echo "game_over" . PHP_EOL;
        return;
    }
    $blue_direct = mt_rand(0, 3);
    $game->playerMove($blue_id, Player::DIRECTION[$blue_direct]);
    if ($game->isGameOver()) {
        $game->printGameMap();
        echo "game_over" . PHP_EOL;
        return;
    }
    //打印移动后战局
    echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
    $game->printGameMap();
    usleep(200000);
}
