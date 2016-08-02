<?php
/**
 * Created by PhpStorm.
 * User: Prophet
 * Date: 2016/7/8
 * Time: 20:09
 */
//define(TOKEN,'Pr0phet');
class check
{
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = 'Pr0phet';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature )
        {
            return true;
        }else{
            return false;
        }
    }

    public function feedback()
    {
        $echostr = $_GET["echostr"];
        if($this->checkSignature())
        {
            echo $echostr;
        }else{
            echo 'fail';
        }

    }

}

$method = new check;
$method->feedback();