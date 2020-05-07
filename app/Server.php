<?php

namespace App;

class Server
{
    const HOST   = '0.0.0.0';
    const PORT   = 5230;
    const FRONT_PORT = 5231;
    const CONFIG = [
        'worker_num' => 4,
        'enable_static_handler' => true,
        'document_root' =>
            '/mnt/htdocs/HideAndSeek/frontend',
    ];

    private $ws;

    public function __construct()
    {
        $this->ws = new \Swoole\WebSocket\Server(self::HOST, self::PORT);
        $this->ws->set(self::CONFIG);
        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('workerStart', [$this, 'onWorkerStart']);
        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('close', [$this, 'onClose']);
        $this->ws->listen(self::HOST, self::FRONT_PORT, SWOOLE_SOCK_TCP);
        $this->ws->start();
    }

    public function onStart($server)
    {
        swoole_set_process_name('hide-and-seek');
        echo sprintf("master start (listening on %s:%d)\n",
            self::HOST, self::PORT);
    }

    public function onWorkerStart($server, $workerId)
    {
        echo "server: onWorkStart,worker_id:{$server->worker_id}\n";
    }

    public function onOpen($server, $request)
    {

    }

    public function onClose($server, $fd)
    {

    }

    public function onMessage($server, $request)
    {

    }
}

new Server();
