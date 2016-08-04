<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller
{


    public function index()
    {
//        $echostr = $_GET["echostr"];
//        if($this->checkSignature())
//        {
//            echo $echostr;
//        }else{
//            echo 'fail';
//        }

        $xml = $GLOBALS["HTTP_RAW_POST_DATA"];
        if ($xml) {
            //下面为xml的解析与分类
            $xmlObj = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
//            $this->refreshAccess_token();
            $_SESSION['MsgType'] = trim($xmlObj->MsgType);

            switch ($_SESSION['MsgType']) {
                case "event" : $this->event($xmlObj);  //处理event类型信息
                    break;
                case "text" : $this->receiveText($xmlObj); //处理文本信息
            }
//            根据xml内容的不同跳转到不同的控制器!!

        } else {
            $this -> display();
            //跳转到地图(考虑直接display或另起应用,跳转应用)
        }
    }


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




    private function refreshAccess_token()
    {
        $db = M('status');
        $condition['name'] = "access_token";
        $aT = $db -> where($condition) ->find();
        if(time() - $aT['life']>7200 )
        {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . C('APP_ID') . "&secret=" . C('APP_SECRET');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
            $jsoninfo = json_decode($output, true);
            $access_token = $jsoninfo["access_token"];

            $aT['code'] = $access_token;
            $aT['life'] = time();
            $db->save($aT);
        }
            $_SESSION['aT'] = $aT['code'];

    }




    private function sendJson($url,$data)
    {
        $this -> refreshAccess_token();
        $targetUrl = $url.$_SESSION['aT'];
        $act = curl_init();
        curl_setopt($act,CURLOPT_POST,1);
        curl_setopt($act,CURLOPT_URL,$targetUrl);
        curl_setopt($act,CURLOPT_POSTFIELDS,$data);
        curl_setopt($act,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($act,CURLOPT_HEADER,array('Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data)));
        $feedback = curl_exec($act);
        curl_close($act);
        return $feedback;
    }




    private function event($obj)  //点击菜单事件或订阅事件
    {
        if($obj -> Event == "subscribe")
        {
            $welcomeMessage = "恭喜你发现了镇会之宝,本服务号可以绑定微信并查询个人信息.
            如果校内导航定位不够精确请复制链接到手机浏览器中进行~";
            echo $this->packText($welcomeMessage,$obj);
            //无绑定用户欢迎信息
        }
        elseif($obj -> Event == "unsubscribe")
        {
            //取消关注信息
        }
        elseif($obj -> Event == "CLICK")
        {
            switch($obj -> EventKey)
            {
                case "ver1.0-2":echo $this -> packText("若绑定请输入姓名+身份证后6位验证身份,如输入:榨汁机＋666666.若是重新绑定也请直接输入",$obj);
                    break;
                case "main1-1": echo $this -> packText("",$obj);
                    break;
                case "main1-2": $list = $this ->listNews();
                                echo $this -> packText($list,$obj);
                    break;
                case "main1-3": $body = $this -> getNews(-2);
                                echo $this -> packNews($body,$obj);
                    break;
                case "main1-4":
                                $body = $this -> getNews(-1);
                                echo $this -> packNews($body,$obj);
                    break;
                case "main1-5":
                                $user = $this -> checkBind($obj);
                                echo $this -> showInfo($user,$obj);
                    break;

            }
        }

    }


    Private function receiveText($obj)
    {
        if(preg_match("/.+\+[0-9]{6}/",$obj -> Content))
        {
            //拆分用户字符串
            $number = explode("+",$obj -> Content);
            $feedback = $this -> bind($number,$obj);
            echo $this -> packText($feedback,$obj);
        }
        else
        {
            echo $this -> packText("请输入合法的姓名+身份证后6位",$obj);
        }
    }


    private function checkBind($obj)
    {
        $db = M('wechatuser');
        $openid = $obj -> FromUserName;
        $condition['openid'] = (string)$openid;
        $data = $db -> where($condition) -> find();
        if($data)
        {
            $db2 = M('newstudent');
            $res = $db2 -> WHERE('id = '.$data['studentid']) -> find();
            return $res;
        }
            else
        {
            echo $this->packText("哈,看起来你好像还没有绑定哦,请戳菜单第二栏查看绑定方法",$obj);
            exit;
        }

    }


    private function bind($number,$obj)
    {
        //第一步绑定学号到数据库(即数据条与微信openId绑定)
        $db = M('newstudent');
        $user = $db -> query("select * FROM __TABLE__ WHERE name = '$number[0]' ");  //寻找数据库中有无此姓名
        $num = 0;
        //修改相应的openID
        if ($user != NULL || $user != FALSE)    //判断姓名寻找结果
        {
            for($i = 0; $i < count($user) ;$i++)    //排除重名情况
            {
                if (substr($user[$i]['idcard'], -6) == $number[1])    //验证身份证后6位
                {
                    $openid = (string)$obj->FromUserName;
                    $id  = $user[$i]['id'];
                    $wechatuser = M('wechatuser');
                    $res = $wechatuser -> query("SELECT * FROM wechatuser WHERE openid = '$openid'");
                    if($res)
                    {
                         $wechatuser -> execute("UPDATE wechatuser SET studentid = '$id' WHERE openid = '$openid'"); //更新微信用户对应id
                    }
                        else
                    {
                        $wechatuser -> execute("INSERT INTO wechatuser (`openid`,`studentid`) VALUES('$openid','$id')"); //新增微信用户
                    }
                    $num = $i;
                    break;
                }
            }
            if($num == count($user))    //寻找不了匹配的身份证
            {
                $this->packText("身份证后六位错误", $obj);
                exit;
            }
        }
        else
        {
            echo $this->packText("查无此姓名", $obj);
            exit;
        }


        //第二步利用数据库查询到的姓名绑定到微信用户的备注名
        $name = $user[$num]['name'];
        $url = "https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=";
        $openid = $obj -> FromUserName;
        $data = "
        {
            \"openid\":\"$openid\",
            \"remark\":\"$name\"
        }
        ";
        $this -> sendJson($url,$data);

        $data = "{
                \"openid_list\" :
                        [
                            \"$openid\"
                        ],
                \"tagid\":100
         }";

        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=";
        $this -> sendJson($url,$data);

        //第三步完成绑定,推送出欢迎信息
        $Welcome_TPL = "绑定成功!欢迎您,%s";
        $welcome = sprintf($Welcome_TPL,$name);
        return $welcome;
    }


    private function packText($data,$obj)
    {
        $XMLTPL = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
        $content = sprintf($XMLTPL,$obj -> FromUserName,$obj -> ToUserName,time(),$data);
        return $content;
    }


    private function packNews($data,$obj)
    {
        $XMLTPL = "
        <xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>1</ArticleCount>
            <Articles>
            <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>
            </Articles>
        </xml>
        ";
        $content = sprintf($XMLTPL,$obj -> FromUserName ,$obj -> ToUserName ,time(), $data -> title,$data -> digest ,$data -> thumb_url ,$data -> url);
        return $content;
    }


    private function showInfo($data,$obj)
    {
        $XMLTPL = "
        <xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>1</ArticleCount>
            <Articles>
            <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl></PicUrl>
            <Url><![CDATA[www.gdufs.edu.cn]]></Url>
            </item>
            </Articles>
        </xml>
        ";
        $INFO =
            "姓名:".$data['name']."
            学号:".$data['studentcode']."
            学院:".$data['college']."
            班级:".$data['class']."
            宿舍:".$data['domi']."
            朋辈导师:".$data['studenttutorname']."
            朋导电话:".$data['studenttutorphone']."
            辅导员姓名:".$data['headtutorname']."
            辅导员电话:".$data['headtutorphone']. "
            点击阅读全文进入广外官网";
        $content = sprintf($XMLTPL, $obj -> FromUserName, $obj -> ToUserName, time(), "个人信息", $INFO);
        return $content;
    }


    private function getNews($num)
    {
        $data = "
        {
            \"type\":\"news\",
            \"offset\":0,
            \"count\":20
        }
        ";
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=";

        $news = $this -> sendJson($url,$data);
        $body = explode("{",$news,2);
        $news = "{".$body[1];
        $news = json_decode($news);
        $content = $news -> item;
        $lenth = count($content);
        $num = $num >0 ? $num : $lenth-abs($num);
        $res = $content[$num] -> content -> news_item[0];
        return $res;
    }


    public function listNews()
    {
        $data = "
        {
            \"type\":\"news\",
            \"offset\":0,
            \"count\":20
        }
        ";
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=";

        $news = $this -> sendJson($url,$data);
        $body = explode("{",$news,2);
        $news = "{".$body[1];
        $news = json_decode($news);
        $content = $news -> item;
        $lenth = count($content);
        $list = "";
        for($i = $lenth-1;$i >= 0; $i--)
        {
            $title = $content[$i] -> content -> news_item[0] -> title;
            $news_url = $content[$i] -> content -> news_item[0] -> url;
            $list = $list.(string)$title ." ";
            $list = $list.(string)$news_url ." ";
        }
        return (string)$list;
    }



    public function createSpecialMenu()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=";
        $json ="
      {
    \"button\": [
        {
            \"type\": \"click\",
            \"name\": \"个人信息\",
            \"key\": \"main1\"
        },
        {
            \"name\": \"主菜单\",
            \"sub_button\": [
                {
                    \"type\": \"click\",
                    \"name\": \"1\",
                    \"key\": \"p2-1\"
                },
                {
                    \"type\": \"click\",
                    \"name\": \"2\",
                    \"key\": \"p2-2\"
                },
                {
                    \"type\": \"click\",
                    \"name\": \"3\",
                    \"key\": \"p2-3\"
                }
            ]
        }
    ],
    \"matchrule\": {
        \"tag_id\": \"2016student\"
    }
}  ";
        $this -> sendJson($url,$json);
    }





    private function logger($log_content)
    {
//        $max_size = 5242880; //单个日志文件大小上限
        $path = "../Common/log/";
        $filename = $path.date("Y.M.D");
        if(file_exists($filename))
        {
            file_put_contents($filename , $log_content.PHP_EOL , FILE_APPEND|LOCK_EX);
        }
            else
        {
            fopen($filename,"a");
            fwrite($filename,$log_content.PHP_EOL);
            fclose($filename);
        }
    }



}