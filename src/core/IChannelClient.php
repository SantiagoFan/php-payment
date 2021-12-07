<?php


namespace JoinPhpPayment\Core;

use JoinPhpPayment\model\Model_PayOrder;

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
     * @param Model_PayOrder $pay_order 支付订单
     * @param array $options   拓展参数
     */
    public function PayOrder(Model_PayOrder $pay_order, array $options);

    /**
     * 支付订单退款
     * @param Model_PayOrder $pay_refund_order
     * @return array success 成功 error 失败 pending 异步
     */
    public function RefundOrder(Model_PayOrder $pay_refund_order): array;

    /**
     * 查询订单
     * @param $pay_order
     * @return mixed
     */
    public function QueryOrder($pay_order);

}