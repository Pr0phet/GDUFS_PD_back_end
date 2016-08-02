<?php
/**
 * Created by PhpStorm.
 * User: Prophet
 * Date: 2016/7/11
 * Time: 15:42
 */

class action
{
    private function bind($number)
    {
        //第一步绑定学号到数据库(即数据条与微信openId绑定)
        $db = M();  //待加入表名称
        $user = $db -> where() -> find();   //待完善where判断
           //修改相应的openID

        //第二步利用数据库查询到的姓名绑定到微信用户的备注名
        $name = $user['name'];
        $url = "https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=";
        $OpenId = $_SESSION['OpenId'];
        $data = "
        {
            \"openid\":$OpenId,
            \"remark\":$name

        }
        ";
        $postJson = A('postJson');
        $postJson -> postJson($url,$data);

        //完成绑定,推送出欢迎信息



    }
}