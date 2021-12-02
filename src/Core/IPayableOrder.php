<?php

namespace app\payment\common;

use app\payment\model\Model_PayOrder;
use app\payment\model\Model_PayRefundOrder;

/**
 * 需要对接支付的业务订单 必须集成当前接口
 * Author        范文刚
 * Email         464884785@qq.com
 * Time          2021/1/26 下午12:00
 * Version       1.0 版本号
 */
Interface IPayableOrder
{
    /**
     * 业务订单转 支付订单
     * @return Model_PayOrder 支付订单
     */
    public function CreatePayOrder();

    /**
     * 当前订单发起支付
     * @param $channel
     * @return mixed
     */
    public function PayOrder($channel,$options);
    /**
     * 支付成功后业务订单处理
     * @param $pay_order Model_PayOrder 支付流水单
     * @return null
     */
    public static function SuccessProcess($pay_order);
    /**
     * 退款
     * @return mixed
     */
    public function Refund($amount,$reason);

    /**
     * 退款成功后业务订单处理
     * @param $pay_refund_order Model_PayRefundOrder 支付退款流水单
     * @return null
     */
    public static function RefundedProcess($pay_refund_order);

}