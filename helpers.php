<?php

use App\Config\Config;

/**
 * @param string $key
 * @return string|array
 */
function get_config_by_key(string $key)
{
    $config = new Config();
    $config->loadConf(__DIR__ . '/configs.php');

    return $config->getConfig($key);
}