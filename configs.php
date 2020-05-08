<?php

return [
    'host'          => '0.0.0.0',
    'server_port'   => '5230',
    'front_port'    => '5231',
    'swoole_config' => [
        'worker_num'      => 4,
        'task_worker_num' => 4,
        'dispatch_mode'   => 5,
        'enable_static_handler' => true,
        'document_root'         => __DIR__.'/frontend'
    ],

    'redis'=>[
        'host'=>'127.0.0.1',
        'port'=>'6379'
    ]
];