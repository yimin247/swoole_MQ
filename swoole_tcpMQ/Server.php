<?php

/*
* swoole ·şÎñÀà
* File Name: Server.php
* Author: ym
* mail: yimin247@163.com
* Created Time:2017-05-02 12:50:48 AM CST
* 
*/

class Server
{
    static $svr;

    private function __construct()
    {
    }

    public static function create($host, $port, $mode = SWOOLE_PROCESS, $sock_type = SWOOLE_SOCK_TCP)
    {
        self::$svr = new swoole_server($host, $port, $mode, $sock_type);
    }

    public static function setProtocol($protocol)
    {
        self::$svr->on('connect', array($protocol, 'onConnect'));
        self::$svr->on('receive', array($protocol, 'onReceive'));
        self::$svr->on('WorkerStart', array($protocol, 'onWorkerStart'));
        self::$svr->on('task', array($protocol, 'onTask'));
        self::$svr->on('finish', array($protocol, 'onFinish'));
        self::$svr->on('close', array($protocol, 'onClose'));

    }

    public static function run($config)
    {
        self::$svr->set($config);
        self::$svr->start();
    }
}

