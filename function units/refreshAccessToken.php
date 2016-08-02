<?php
/**
 * Created by PhpStorm.
 * User: Prophet
 * Date: 2016/7/11
 * Time: 16:53
 */

class basic
{
    private static $appid = "wx5827a471f2e6ca91";
    private static $secret = "15243b374455a855a2adc277972847df";

    private function refreshAccess_token()
    {
        $db = M();
        $new = $_GET['https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$appid.'&secret='.self::$secret];
        $row = $db ->find('1');
        $row['content'] = $new;
        $row['life']=time();
        $db -> save($row);
    }

    public function refresh()
    {
        $this -> refreshAccess_token();
        return 1;
    }

}

$db =M();
if(($db -> field('life') ->find('1')) - time() >7200 )
{
    $basic = new basic();
    $basic -> refresh();
}

$_SESSION['aT'] = $db -> field('content') -> find('1');