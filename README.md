# 如何部署？
首先clone下来，运行composer install

### 安装Swoole扩展

根据[官网教程](https://wiki.swoole.com/wiki/page/6.html)进行安装，这里就不阐述

### 修改配置文件configs.php
```
<?php
return [
　   //服务端Ｉp，换成自己的
    'host'          => '0.0.0.0',
　   //服务端监听端口，换成自己的
    'server_port'   => '5230',
　   //前端监听端口，换成自己的
    'front_port'    => '5231',

    'swoole_config' => [
        'worker_num'      => 4,
        'task_worker_num' => 4,
        'dispatch_mode'   => 5,
        'enable_static_handler' => true,
        'document_root'         => __DIR__.'/frontend',
        //启动后台进程守护
        'daemonize'=>1
    ],

    'redis'=>[
        'host'=>'127.0.0.1',
        'port'=>'6379'
    ]
];

注意: 记得将index.html下的服务端地址修改成跟配置文件一样
```
### 如何运行?
```
打开终端，运行:php index.php,在浏览器打开两个窗口，输入http://0.0.0.0:5231/index.html就可以开始游戏
```