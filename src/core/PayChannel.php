<?php
/**
 * 聚合支付
 * User: santiago-范文刚
 * Date: 19-8-14
 * Time: 上午9:19
 */

namespace app\payment\common;

use JoinPhpPayment\core\PayClient;
use Exception;

/**
 * 定义支付渠道
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

    const channel=[
        PayClient::WEIXIN_PAY => PayChannel::WEIXIN_PAY,
        PayClient::WEIXIN_QRCODE => PayChannel::WEIXIN_PAY_NATIVE,
        PayClient::ALI_MP => PayChannel::ALI_PAY,
        PayClient::ALI_PAY_QRCODE => PayChannel::ALI_PAY_NATIVE,
    ];

    /**
     * 设备通道 对应支付通道
     * @param string $client
     * @throws Exception
     */
    public static function GetPayChannel(string $client){
        if(isset(self::channel[$client])){
            return self::channel[$client];
        }
        else{
            throw  new Exception('未找到对应支付通道');
        }
    }
}