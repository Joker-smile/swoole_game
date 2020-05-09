<?php

return [
    'host'          => '0.0.0.0',
    'server_port'   => '5230',
    'front_port'    => '5231',
    'swoole_config' => [
        'worker_num'            => 4,
        'task_worker_num'       => 4,
        'dispatch_mode'         => 5,
        'enable_static_handler' => true,
        'document_root'         => __DIR__ . '/frontend',
        //启动后台进程守护
        //        'daemonize'=>1
    ],

    'redis'              => [
        'host' => '127.0.0.1',
        'port' => '6379'
    ],

    //每局游戏时间(秒)
    'game_time_limit'    => 30,

    //玩家地图显示长度
    'player_display_len' => 2
];