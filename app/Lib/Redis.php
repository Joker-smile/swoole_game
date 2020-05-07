<?php

namespace App\Lib;

class Redis
{
    protected static $instance;

    /**
     * 获取redis实例
     *
     * @return \Redis|\RedisCluster
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            $instance = new \Redis();
            $instance->connect(
                get_config_by_key('redis')['host'],
                get_config_by_key('redis')['port']
            );
            self::$instance = $instance;
        }
        return self::$instance;
    }
}