<?php
/**
 * Created by PhpStorm.
 * User: Administrator-范文刚
 * Date: 2019/3/30
 * Time: 15:11
 */

namespace JoinPhpCommon\utils;

class Text
{
    /**
     * 生成随机唯一订单号
     * @return string
     */
    public static function build_order_no(){
        return
            date('Ymd').substr(
                implode(null,array_map('ord',str_split(substr(uniqid(), 7, 13), 1))),
                0, 8);
    }

}