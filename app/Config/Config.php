<?php

namespace App\Config;

class Config
{
    protected static $config;

    // 加载配置文件
    public function loadConf($conf_file)
    {
        if (is_file($conf_file)) {
            self::$config = include $conf_file;
        }
    }

    public function getConfig($name)
    {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        } else {
            return " config $name is undefined ";
        }
    }
}