<?php


namespace JoinPhpCommon\example\utils;


use JoinPhpCommon\utils\HttpHelper;

class HttpHelperTest
{
    public function http(){
        $http = new HttpHelper();
        $res = $http->post_json('http://t.cn/index/index/timeout',['a'=>1,'b'=>2]);
        vdump($res);
    }
}