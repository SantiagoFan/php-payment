<?php

namespace JoinPhpPayment\core;

/**
 * 客户端类型
 * Class PayClient
 */
class PayClient{
    // 原生微信 js 小程序
    const WEIXIN_MP ="weixin_mp";
    // 二维码
    const WEIXIN_QRCODE = "wxpay_qrcode";

    // 支付宝 js 小程序
    const ALI_MP ="ali_mp";
    // 二维码
    const ALI_PAY_QRCODE = "alipay_qrcode";
}