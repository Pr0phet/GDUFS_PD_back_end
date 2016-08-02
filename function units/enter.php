<?php
/**
 * Created by PhpStorm.
 * User: Prophet
 * Date: 2016/7/10
 * Time: 13:11
 */

class enter
{
    private static $appid = "wx5827a471f2e6ca91";
    private static $secret = "15243b374455a855a2adc277972847df";


    public function enter()
    {
        if ($xml = file_get_contents('php://input')) {
            //下面为xml的解析与分类
            $xmlObj = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);

            $_SESSION['OpenId'] = $xmlObj->FromUserName;
            $_SESSION['CreateTime'] = $xmlObj->CreateTime;
            $_SESSION['MsgType'] = $xmlObj->MsgType;

            switch ($_SESSION['MsgType']) {
                case "even":   //url跳转
                    break;
                //      case
            }
            //根据xml内容的不同跳转到不同的控制器!!

        } else {
            //跳转到地图(考虑另起一个应用,跳转应用)
        }
    }


    private function refreshAccess_token()
    {
        $db = M();
        $new = $_GET['https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$appid.'&secret='.self::$secret];
        $row = $db ->find('1');
        $row['content'] = $new;
        $row['life']=time();
        $db -> save($row);
    }




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



}