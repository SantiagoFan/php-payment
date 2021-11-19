<?php
namespace JoinPhpCommon\example\utils;
class PinyinTest{
    function index(){
        var_dump('xxx');
        print_r('xxx');
        $res = Pinyin::convert('我是个神仙');
        echo  $res;
    }
}
