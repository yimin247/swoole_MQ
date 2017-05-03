<?php
/*
* File Name: main.php
* Author: ym
* mail: yimin247@163.com
* Created Time:2017-05-02 11:46:48 AM CST
*/

date_default_timezone_set('PRC');
ini_set('memory_limit', '50M');

require(__DIR__ . '/autoload.php');

function runMain($config)
{
    Server::create($config['system']['tcp_host'], $config['system']['tcp_port']);
    $protocol = new Protocol($config);

    Server::setProtocol($protocol);

    Server::run($config['swoole']);
}

if (empty($argv[1])) {
    echo '请选择运行模式 [main] or [vice] or [die] ', PHP_EOL;
    exit;
}
$queueType = strtolower($argv[1]);
$config = parse_ini_file('config.ini', true);
if (empty($config[$queueType])) {
    echo '配置不存在', PHP_EOL;
    exit;
}
$config = $config[$queueType];
define('PLATFORM_QUEUE', $queueType);
define('MAIN_MSG_KEY', $config['system']['main_msg_key']);
define('VICE_MSG_KEY', $config['system']['vice_msg_key']);
define('TEMPORARY_MSG_KEY', $config['system']['temporary_msg_key']);

runMain($config);

