<?php
/**
 * Created by PhpStorm.
 * User: Prophet
 * Date: 2016/7/10
 * Time: 21:01
 */

include_once "refreshAccessToken.php";
class sendData
{
    private function send($url,$data)
    {
        $targetUrl = $url.$_SESSION['aT'];
        $act = curl_init();
        curl_setopt($act,CURLOPT_POST,1);
        curl_setopt($act,CURLOPT_URL,$targetUrl);
        curl_setopt($act,CURLOPT_POSTFIELDS,$data);
        curl_setopt($act,CURLOPT_HEADER,array('Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data)));
        $feedback = curl_exec($act);
        curl_close($act);
        var_dump($feedback);
    }


    public function postJson($url,$data)
    {
        $this->send($url,$data);
    }
}


$obj = new sendData();
$obj -> postJson($url,$json);