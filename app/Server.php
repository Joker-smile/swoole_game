<?php

namespace App;

use App\Manager\DataCenter;

class Server
{
    private $ws;

    public function __construct()
    {
        $this->ws = new \Swoole\WebSocket\Server(get_config_by_key('host'), get_config_by_key('server_port'));
        $this->ws->set(get_config_by_key('swoole_config'));
        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('workerStart', [$this, 'onWorkerStart']);
        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('close', [$this, 'onClose']);
        $this->ws->listen(get_config_by_key('host'), get_config_by_key('front_port'), SWOOLE_SOCK_TCP);
        $this->ws->start();
    }

    public function onStart($server)
    {
        swoole_set_process_name('hide-and-seek');
        echo sprintf("master start (listening on %s:%d)\n",
            get_config_by_key('host'), get_config_by_key('host'));
    }

    public function onWorkerStart($server, $workerId)
    {
        echo "server: onWorkStart,worker_id:{$server->worker_id}\n";
    }
    public function onOpen($server, $request)
    {
        DataCenter::log(sprintf('client open fd：%d', $request->fd));
    }
    public function onMessage($server, $request)
    {
        DataCenter::log(sprintf('client open fd：%d，message：%s', $request->fd, $request->data));
        $server->push($request->fd, 'server:hello client!');
    }
    public function onClose($server, $fd)
    {
        DataCenter::log(sprintf('client close fd：%d', $fd));
    }
}
