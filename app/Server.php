<?php

namespace App;

use App\Manager\DataCenter;
use App\Manager\Logic;
use App\Manager\TaskManager;

class Server
{
    const CLIENT_CODE_MATCH_PLAYER = 600;

    const CLIENT_CODE_START_ROOM = 601;

    private $ws;

    private $logic;

    public function __construct()
    {
        $this->logic = new Logic();
        $this->ws    = new \Swoole\WebSocket\Server(get_config_by_key('host'), get_config_by_key('server_port'));
        $this->ws->set(get_config_by_key('swoole_config'));
        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('workerStart', [$this, 'onWorkerStart']);
        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('close', [$this, 'onClose']);
        $this->ws->on('task', [$this, 'onTask']);
        $this->ws->on('finish', [$this, 'onFinish']);
        $this->ws->listen(get_config_by_key('host'), get_config_by_key('front_port'), SWOOLE_SOCK_TCP);
        $this->ws->start();
    }

    //onStart回调的是Master进程
    public function onStart($server)
    {
        swoole_set_process_name('hide-and-seek');
        echo sprintf("master start (listening on %s:%d)\n",
            get_config_by_key('host'), get_config_by_key('host'));
        DataCenter::initDataCenter();
    }

    //onWorkerStart回调的是Worker进程，只有Worker进程才可以发起Task任务
    public function onWorkerStart($server, $worker_id)
    {
        DataCenter::$server = $server;
        echo "server: onWorkStart,worker_id:{$server->worker_id}\n";
    }

    public function onOpen($server, $request)
    {
        DataCenter::log(sprintf('client open fd：%d', $request->fd));
        $player_id = $request->get['player_id'];
        DataCenter::setPlayerInfo($player_id, $request->fd);
    }
    public function onMessage($server, $request)
    {
        DataCenter::log(sprintf('client open fd：%d，message：%s', $request->fd, $request->data));
        $data      = json_decode($request->data, true);
        $player_id = DataCenter::getPlayerId($request->fd);
        switch ($data['code']) {
            case self::CLIENT_CODE_MATCH_PLAYER:
                $this->logic->matchPlayer($player_id);
                break;

            case self::CLIENT_CODE_START_ROOM:
                $this->logic->startRoom($data['room_id'], $player_id);
                break;
        }
    }

    public function onClose($server, $fd)
    {
        DataCenter::log(sprintf('client close fd：%d', $fd));

        DataCenter::delPlayerInfo($fd);
    }

    public function onTask($server, $task_id, $src_worker_id, $data)
    {
        DataCenter::log("onTask", $data);
        $result = [];
        switch ($data['code']) {
            case TaskManager::TASK_CODE_FIND_PLAYER:
                $ret = TaskManager::findPlayer();
                if (!empty($ret)) {
                    $result['data'] = $ret;
                }
                break;
        }

        if (!empty($result)) {
            $result['code'] = $data['code'];
            return $result;
        }
    }

    public function onFinish($server, $task_id, $data)
    {
        DataCenter::log("onFinish", $data);
        switch ($data['code']) {
            case TaskManager::TASK_CODE_FIND_PLAYER:
                $this->logic->createRoom($data['data']['red_player'],
                    $data['data']['blue_player']);
                break;
        }
    }
}
