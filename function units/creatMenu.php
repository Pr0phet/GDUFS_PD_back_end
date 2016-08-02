<?php
/**
 * Created by PhpStorm.
 * User: Prophet
 * Date: 2016/7/10
 * Time: 15:08
 */
//access_token要换
$access_token = 'C_avD4LLMuxtq11c279_rJRTrbaWhvcoAykI63xzYc3P_IJ1swrDFwg7XQCPwkxw9MegULRzZTUa2kIaGYw8cdZw0meTIc3j9BcHfRqJ8VE27UbcR__tkZxwaCL9SRbuTZAjAAAJPQ';
$json ="
{
    \"button\": [
        {
            \"name\": \"主菜单\",
            \"key\": \"ver1.0-1\",
            \"sub_button\": [
                {
                    \"type\": \"click\",
                    \"name\": \"建设中\",
                    \"key\": \"main1-1\"
                },
                {
                    \"type\": \"click\",
                    \"name\": \"建设中\",
                    \"key\": \"main1-2\"
                },
                {
                    \"type\": \"click\",
                    \"name\": \"朋协介绍\",
                    \"key\": \"main1-3\"
                },
                {
                    \"type\": \"click\",
                    \"name\": \"新生大礼包\",
                    \"key\": \"main1-4\"
                },
                {
                    \"type\": \"click\",
                    \"name\": \"个人信息\",
                    \"key\": \"main1-5\"
                }
            ]
        },
        {
            \"type\": \"click\",
            \"name\": \"绑定学号\",
            \"key\": \"ver1.0-2\"
        },
        {
            \"type\": \"view\",
            \"name\": \"校内导航\",
            \"key\": \"ver1.0-3\",
            \"url\": \"https://123.207.124.18/pro\"
        }
    ]
}
";


$res = $_POST['https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token];