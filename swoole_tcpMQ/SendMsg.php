<?php

/**
* 处理转发数据的类
* Created by PhpStorm.
* Author: ym
* mail: yimin247@163.com
* Created Time:2017-05-02 11:46:48 AM CST
*
*/
class SendMsg
{

    public static function Handle($jsonData)
    {
        $reqData = json_decode($jsonData, true);
        $uri = $reqData['uri'];
        $body = $reqData['body'];
        $method = $reqData['method'];

        switch (strtoupper($method)) {
            case 'GET':
                $result = self::get_curl($uri,$jsonData);
                break;
            default:
                $result = self::post_curl($uri, $body,$jsonData);
                break;
        }

        return $result;
    }

    /**
     *
     * curl  函数  GET访问
     * @param string $url		访问地址
     * @return mix
     */
    private static function get_curl($url,$jsonData)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($curl);
        curl_close($curl);
        if (!$response) {
            return false;
        }
        $reqArr = json_decode($response, true);
        if (!empty($reqArr['code']) && $reqArr['code'] == 200) {
            $msg['code'] = '200';
        }else{
            $msg['code'] = '201';//失败
        }
		$msg['jsonData'] = $jsonData;//数据
        return $msg;
    }
    /**
     *
     * curl  函数  POST访问
     * @param string $url		访问地址
     * @return mix
     */
    private static function post_curl($uri, $body,$jsonData)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($body));
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response = curl_exec($curl);
        curl_close($curl);
        if (!$response) {
            return false;
        }
        $reqArr = json_decode($response, true);

        if (!empty($reqArr['code']) && $reqArr['code'] == 200) {
            $msg['code'] = '200';
        }else{
            $msg['code'] = '201';//失败
        }
		$msg['jsonData'] = $jsonData;//数据
        return $msg;
    }

}