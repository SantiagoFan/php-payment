<?php

namespace JoinPhpPayment\core;

use JoinPhpPayment\model\Model_PayOrder;

/**
 * 需要对接支付的业务订单 必须集成当前接口
 */
Interface IPayableOrder
{
    /**
     * 业务订单转 支付订单
     * @return Model_PayOrder 支付订单
     */
    public function CreatePayOrder(): Model_PayOrder;

    /**
     * 支付
     * @param string $client PayClient
     * @param array $options
     * @return mixed
     */
    public function PayOrder(string $client, array $options);

    /**
     * 支付成功后回调
     * @param $pay_order Model_PayOrder 支付流水单
     * @return null
     */
    public function PaySuccess(Model_PayOrder $pay_order);

    /**
     * 退款
     * @param float $amount 支付金额
     * @param string $reason 支付流水单
     * @return mixed
     */
    public function RefundOrder(float $amount, string $reason);

    /**
     * 退款成功后业务订单处理
     * @param $pay_refund_order Model_PayOrder 支付退款流水单
     * @return null
     */
    public function RefundedSuccess(Model_PayOrder $pay_refund_order);

}