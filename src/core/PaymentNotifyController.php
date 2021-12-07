<?php


namespace JoinPhpPayment\core;


use Exception;
use JoinPhpPayment\channel\WxpayClient;
use JoinPhpPayment\model\Model_PayOrder;
use think\Controller;

class PaymentNotifyController extends Controller
{
    /**
     * 微信支付回调
     * @throws Exception
     */
    public function wxpay(){
        $app = new WxpayClient();
        $app->PayNotify($this->PaySuccess);
    }

    /**
     *
     * @throws Exception
     */
    public function wxrefund(){
        $app = new WxpayClient();
        $app->RefundNotify($this->RefundSuccess);
    }

    /**
     * 支付成功后调用
     * @param Model_PayOrder $pay_order
     */
    public function PaySuccess(Model_PayOrder $pay_order){
        // 业务系统可重写次方法
    }

    /**
     * 退款成功回调
     * @param Model_PayOrder $pay_refund_order
     */
    public function RefundSuccess(Model_PayOrder $pay_refund_order){
        // 业务系统可重写次方法
    }
}