<?php

/*
* 记录日志
* File Name: Filelog.php
* Author: ym
* mail: yimin247@163.com
* Created Time: 2017-05-02 16:50:48 PM CST
* 
*/

class Filelog
{

    static $config = array();

    private function __construct()
    {
    }

    public static function ini_set($config)
    {
        $date = date('Ymd');
        self::$config = array(
            'filePath' => $config['file_path'],
            'fileName' => sprintf($config['file_name'], $date)
        );
    }

    /**
     * 运行日志,记录运行步骤
     */
    public static function add($accessMessage)
    {
        //添加数据
        $accessMessage = date(DATE_RFC822) . " -access- " . $accessMessage . "\r\n";
        //调用写入函数
        self::addToFile($accessMessage);
    }

    /**
     * 错误日志
     */
    public static function error($errorMessage)
    {
        //添加必要数据
        $errorMessage = date(DATE_RFC822) . " -error- " . $errorMessage . "\r\n";
        //调用写入函数
        self::addToFile($errorMessage);
    }

    /**
     * 写入文件函数
     */
    private static function addToFile($logMessage)
    {
        //判断存储文件策略
        self::fileConf();
        //写入配置
        file_put_contents(self::$config['filePath'] . self::$config['fileName'], $logMessage, FILE_APPEND);
    }

    /**
     * 存储文件策略
     */
    private static function fileConf()
    {
        //获得配置文件
        if (!file_exists(self::$config['filePath'])) {
            mkdir(self::$config['filePath']);
        }
        if (!file_exists(self::$config['filePath'] . self::$config['fileName'])) {
            touch(self::$config['filePath'] . self::$config['fileName']);
        }
    }

}
