<?php


namespace JoinPhpPayment\Core;

/**
 * 支付渠道 实现接口
 *
 * Author        范文刚
 * Email         san_fan@qq.com
 * Time          2021/2/4 下午4:41
 * Version       1.0 版本号
 */
Interface IChannelClient
{
    /**
     * 通过支付获取支付参数
     * @param $pay_order 支付订单
     * @param $extend   拓展参数
     */
    public function PrepayOrder($pay_order,$extend);

    /**
     * 支付订单退款
     * @param $pay_order
     * @return mixed
     */
    public function RefundOrder($pay_order);

    public function QueryOrder();

}