<?php
/**
 * Created by PhpStorm.
 * User: Prophet
 * Date: 2016/7/15
 * Time: 11:09
 */
$pattern = '/sh(?=e)(\w*)/i';
$str = "Shelly sells seashells by the seashore";
preg_match($pattern, $str, $match);
print_r($match);