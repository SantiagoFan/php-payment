<?php
/**
 * 聚合支付
 * User: santiago-范文刚
 * Date: 19-8-14
 * Time: 上午9:19
 */

namespace app\payment\common;

use think\Exception;

/**
 * 定义支付方式
 * Class Paychannel
 * @package app\payment\common
 */
class PayChannel{
    // 原生微信
    const WEIXIN_PAY ="wxpay";
    const WEIXIN_PAY_NATIVE = "wxpay_native";
    // 原生支付宝
    const ALI_PAY ="alipay";
    const ALI_PAY_NATIVE = "alipay_native";

    /**
     * 设备通道 对应支付通道
     * @param $provider
     * wxpay 微信小程序
     * wxpay_native 自助机微信
     * alipay  支付宝小程序
     * alipay_native 自助机支付宝
     * @throws Exception
     */
    public static function GetPayChannel($provider){
        // 原生支付
//        switch ($provider){
//            case 'wxpay':return self::WEIXIN_PAY;
//            case 'wxpay_native':return self::WEIXIN_PAY_NATIVE;
//            case 'alipay':return self::ALI_PAY;
//            case 'alipay_native':return self::ALI_PAY_NATIVE;
//            default: throw  new Exception('未找到对应支付通道');
//        }
        // 浦发
        switch ($provider){
            case 'wxpay':return self::SPDB_WEIXIN_PAY;
            case 'wxpay_native':return self::SPDB_WEIXIN_PAY_NATIVE;
            case 'alipay':return self::SPDB_ALI_PAY;
            case 'alipay_native':return self::ALI_PAY_NATIVE;
            default: throw  new Exception('未找到对应支付通道');
        }
    }
}