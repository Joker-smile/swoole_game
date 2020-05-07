<?php

return [
    'host'          => '0.0.0.0',
    'server_port'   => '5230',
    'front_port'    => '5231',
    'swoole_config' => [
        'worker_num'            => 4,
        'enable_static_handler' => true,
        'document_root'         => __DIR__.'/frontend'
    ]
];