<?php

namespace JoinPhpPayment\base;

use Exception;
use JoinPhpPayment\channel\AlipayClient;
use JoinPhpPayment\channel\WxpayClient;
use JoinPhpPayment\model\Model_PayOrder;
use think\Controller;

abstract class BaseNotifyController extends Controller
{
    /**
     * 微信支付通知
     * @throws Exception
     */
    public function wxpay(){
        $app = new WxpayClient();
        $app->PayNotify(function ($pay_order){
            $this->PaySuccess($pay_order);
        });
    }
    /**
     * 微信退款通知
     * @throws Exception
     */
    public function wxrefund(){
        $app = new WxpayClient();
        $app->RefundNotify(function ($pay_refund_order){
            $this->RefundSuccess($pay_refund_order);
        });
    }

    /**
     * 支付宝支付回调
     */
    public function alipay(){
        $data = input('post.');
        $app = new AlipayClient();
        return $app->RefundNotify($data,function ($pay_order){
            $this->PaySuccess($pay_order);
        });
    }

    /**
     * 支付通知处理完成后调用
     * @param Model_PayOrder $pay_order
     */
    public function PaySuccess(Model_PayOrder $pay_order){
        // 需要全局处理 子类覆盖次方法
    }

    /**
     * 退款通知处理完成后调用
     * @param Model_PayOrder $pay_refund_order
     */
    public function RefundSuccess(Model_PayOrder $pay_refund_order){
        // 需要全局处理 子类覆盖次方法
    }
}