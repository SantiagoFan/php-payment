<?php

namespace JoinPhpPayment\core;

use Exception;
use JoinPhpPayment\channel\AlipayClient;
use JoinPhpPayment\channel\IChannelClient;
use JoinPhpPayment\channel\WxpayClient;

/**
 * 定义支付渠道
 * Class Paychannel
 * @package app\payment\common
 */
class PayChannel{
    // 原生微信
    const WEIXIN_PAY_JS ="wxpay_js";
    const WEIXIN_PAY_NATIVE = "wxpay_native";
    const WEIXIN_PAY_APP = "wxpay_app";
    // 原生支付宝
    const ALI_PAY_JS ="alipay_js";
    const ALI_PAY_NATIVE = "alipay_native";
    const ALI_PAY_APP = "alipay_app";

    /**
     * 支付方式 换取 渠道操作类
     * @param string $pay_channel
     * @return mixed
     * @throws Exception
     */
    public static function GetPayChannelApp(string $pay_channel):IChannelClient{
        switch ($pay_channel){
            // 微信支付方式
            case PayChannel::WEIXIN_PAY_NATIVE:
            case PayChannel::WEIXIN_PAY_JS:
            case PayChannel::WEIXIN_PAY_APP: return new WxpayClient();

            // 支付宝
            case PayChannel::ALI_PAY_NATIVE:
            case PayChannel::ALI_PAY_JS:
            case PayChannel::ALI_PAY_APP: return new AlipayClient();
            default:
                throw new Exception("GetPayChannelApp:支付渠道 {$pay_channel} 错误");
        }
    }
}