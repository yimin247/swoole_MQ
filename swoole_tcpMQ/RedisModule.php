<?php

/*
* redis链接类
* File Name: RedisModule.php
* Author: ym
* mail: yimin247@163.com
* Created Time:2017-05-02 16:50:48 AM CST
*/

class RedisModule
{

    static $redis;

    private function __construct()
    {
    }

    public static function connect($config)
    {
        if (!empty(self::$redis) && self::$redis->isConnected()) {

            return self::$redis;
        }

        try {
            $redis = new redis();
            $redis->pconnect($config['host'], $config['port']);
            !empty($config['pwd']) && $redis->auth($config['pwd']);
        } catch (Exception $e) {
            Filelog::error('connect: ' . $e->getMessage());

            return false;
        }

        return self::$redis = $redis;
    }

    public static function reload($config)
    {
        self::$redis = null;

        return self::connect($config);
    }

    public static function lpop($key)
    {
        if (empty(self::$redis)) return false;
        try {
            $msgData = self::$redis->lpop($key);
        } catch (\Exception $e) {
            Filelog::error('lpop: ' . $e->getMessage());
            return false;
        }

        return $msgData;
    }

    public static function rpush($key, $msgData)
    {
        if (empty(self::$redis)) return false;
        try {
            $curLen = self::$redis->rpush($key, $msgData);
        } catch (\Exception $e) {
            Filelog::error('rpush: ' . $e->getMessage());
            return false;
        }

        return $curLen;
    }

    public static function rpoplpush($source_key, $destination_key)
    {
        if (empty(self::$redis)) return false;
        try {
            $curLen = self::$redis->rpoplpush($source_key, $destination_key);
        } catch (\Exception $e) {
            Filelog::error('rpoplpush: ' . $e->getMessage());
            return false;
        }

        return $curLen;
    }

    public static function hget($key, $field)
    {
        if (empty(self::$redis)) return false;
        try {
            $curLen = self::$redis->hget($key, $field);
        } catch (\Exception $e) {
            Filelog::error('hget: ' . $e->getMessage());
            return false;
        }

        return $curLen;
    }

    public static function hset($key, $field, $data)
    {
        if (empty(self::$redis)) return false;
        try {
            $curLen = self::$redis->hset($key, $field, $data);
        } catch (\Exception $e) {
            Filelog::error('hset: ' . $e->getMessage());
            return false;
        }

        return $curLen;
    }

    public static function hdel($key, $field)
    {
        if (empty(self::$redis)) return false;
        try {
            $curLen = self::$redis->hdel($key, $field);
        } catch (\Exception $e) {
            Filelog::error('hdel: ' . $e->getMessage());
            return false;
        }

        return $curLen;
    }
	//过期 
    public static function pexpireat($key, $num)
    {
        if (empty(self::$redis)) return false;
        try {
            $curLen = self::$redis->pexpireat($key, $num);
        } catch (\Exception $e) {
            Filelog::error('pexpireat: ' . $e->getMessage());
            return false;
        }

        return $curLen;
    }
	//累加 
    public static function hincrby($key, $field,$num)
    {
        if (empty(self::$redis)) return false;
        try {
            $curLen = self::$redis->hincrby($key, $field,$num);
        } catch (\Exception $e) {
            Filelog::error('hincrby: ' . $e->getMessage());
            return false;
        }

        return $curLen;
    }

    public static function info()
    {
        if (empty(self::$redis)) return false;
        try {
            $info = self::$redis->info('Clients');
        } catch (\Exception $e) {
            Filelog::error('info(clients): ' . $e->getMessage());
            return false;
        }

        return !empty($info['connected_clients']);
    }
}
