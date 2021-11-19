<?php


namespace JoinPhpCommon\example\utils;


use JoinPhpCommon\utils\Text;

class TextTest
{
    public function index(){
        $order_code = Text::build_order_no();
        echo $order_code;
    }
}