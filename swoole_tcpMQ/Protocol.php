<?php

/*
* swoole 服务处理类
* File Name: Protocol.php
* Author: ym
* mail: yimin247@163.com
* Created Time:2017-05-02 14:46:48 AM CST
* 
*/

class Protocol
{

    public $swoole_config;
    public $redis_config;
    public $worker_num;
    public $log_config;
    public $time_rate;
    public $limit;
    public $msg_conut_key;
    public $serv;

    public function __construct($config)
    {
        $this->initConfig($config);
        Filelog::ini_set($this->log_config);
    }

    //初始化配置
    public function initConfig($config)
    {
        $this->swoole_config = $config['swoole'];
        $this->redis_config = $config['redis'];
        $this->time_rate = $config['system']['time_rate'];
        $this->limit = $config['system']['limit'];
        $this->msg_conut_key = $config['system']['msg_conut_key'];
        $this->worker_num = $config['swoole']['worker_num'];
        $this->log_config = $config['log'];
    }

    //监听连接进入事件
    public function onConnect($serv, $fd)
    {
        var_dump($fd, $serv->stats());
        echo "Client: Connect.\n";
    }

    //监听数据接收事件
    public function onReceive($serv, $fd, $from_id, $data)
    {
        $msg = "Server: $fd ;data: " . $data;

        $serv->send($fd, $msg);
    }

    //woke 进程中循环
    public function onWorkerStart($serv, $workerId)
    {
        RedisModule::connect($this->redis_config);
        Filelog::add('Worker start : ' . $workerId);
        $this->serv = $serv;
        if ($workerId < $this->worker_num) {//
            swoole_timer_tick($this->time_rate, function () {
                $this->onSendMsg($this->serv);
            });
        }
    }

    /**
     * @param $serv
     * @return bool
     * 转发消息
     */
    public function onSendMsg($serv)
    {
        if (!RedisModule::info()) {
            RedisModule::reload($this->redis_config);
        }
        $msgData = RedisModule::lpop(MAIN_MSG_KEY);
		//$msgData = RedisModule::rpoplpush(MAIN_MSG_KEY,VICE_MSG_KEY);//方案一：为了防止取出数据丢失，用安全队列，onFinish成功后删除VICE_MSG_KEY，这个测试删除没通过，原因是lrem删除是value里面有单引号删除失败
        if (empty($msgData)) return false;
		//方案二：为了防止取出数据丢失，取出队列，存入hash                
		$field = md5($msgData);
		RedisModule::hset(TEMPORARY_MSG_KEY,$field,$msgData);
                
        $serv->task($msgData);
    }

    //监听连接关闭事件
    public function onClose($serv, $fd)
    {
        echo "Client: $fd Close.\n";
    }

    public function onTask($serv, $fd, $fromId, $jsonData)
    {
        Filelog::add("onTask serv : $serv fd : $fd from_id : $fromId data : $jsonData");
        if (empty($jsonData)) {
            return array(
                'code' => 100,
                'data' => $jsonData
            );
        }
		//转发处理函数
		$ret = SendMsg::Handle($jsonData);
		return $ret;
    }

    /**
     * @param $data
     * 添加副队列 (相对配置)
     */
    public function addViceQueue($key,$data)
    {
        $curLen = RedisModule::rpush($key, $data);
        if (0 < $curLen) {
            Filelog::add('addViceQueue data :' . $data);
        } else {
            Filelog::error('addViceQueue error data :' . $data);
        }
    }

    /**
     * @param $data
     * 获取失败队列总数 (相对配置)
     */
    public function getViceQueue($key,$field)
    {
        $curLen = RedisModule::hget($key, $field);
        if (0 < $curLen) {
            Filelog::add('getViceQueue data :' . $field);
        } else {
            Filelog::error('getViceQueue error data :' . $field);
        }
    }

    /**
     * @param $data
     * 设置过期 (相对配置)
     */
    public function pexpireat($key,$num)
    {
        $curLen = RedisModule::pexpireat($key, $num);
        if (0 < $curLen) {
            Filelog::add('pexpireat data :' . $field);
        } else {
            Filelog::error('pexpireat error data :' . $field);
        }
    }
    /**
     * @param $data
     * 累计失败队列数 (相对配置)
     */
    public function hincrby($key,$field,$num)
    {
        $curLen = RedisModule::hincrby($key, $field,$num);
        if (0 < $curLen) {
            Filelog::add('hincrby data :' . $field);
        } else {
            Filelog::error('hincrby error data :' . $field);
        }
    }

    /**
     * @param $data
     * 删除缓存队列 (相对配置)
     */
    public function delTemporaryQueue($data)
    {
		$field = md5($data);
        $curLen = RedisModule::hdel(TEMPORARY_MSG_KEY, $field);
        if (0 < $curLen) {
            Filelog::add('delTemporaryQueue data :' . $data);
        } else {
            Filelog::error('delTemporaryQueue error data :' . $data);
        }
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $data
     * @return bool
     * 监控task 完成事件
     */
    public function onFinish($serv, $taskId, $data)
    {
        if (200 == $data['code']) {
			//成功删除缓存队列
			Filelog::add("onFinish Success code : {$data['code']} data : {$data['jsonData']}");
			$this->delTemporaryQueue($data['jsonData']);
        }else{
			//失败 添加副队列 相对配置
			if(PLATFORM_QUEUE=='main'){//主队列  main
                $this->addViceQueue(VICE_MSG_KEY,$data['jsonData']);
            }
			if(PLATFORM_QUEUE=='vice'|| PLATFORM_QUEUE=='die'){//副队列  vice 死队列 die
                $field = md5($data['jsonData']);
                $num = 1;
                $count = $this->getViceQueue($this->msg_conut_key,$field);
                if($count>$this->limit){
					$this->addViceQueue(VICE_MSG_KEY,$data['jsonData']);//死队列
                    $this->pexpireat($this->msg_conut_key,1);
                }else{
                    $this->hincrby($this->msg_conut_key,$field,$num);//计数 +1
                    $this->addViceQueue(MAIN_MSG_KEY,$data);//副队列
                }
            }
            Filelog::error(PLATFORM_QUEUE." onFinish Error code : {$data['code']} data : {$data['jsonData']}");
		}

    }

}


