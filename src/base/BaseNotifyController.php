<?php

namespace JoinPhpPayment\core;

use Exception;
use JoinPhpPayment\channel\WxpayClient;
use JoinPhpPayment\model\Model_PayOrder;
use think\Controller;

abstract class BaseNotifyController extends Controller
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
     * dsadad
     * @param Model_PayOrder $pay_order
     * @return mixed
     */
    public abstract function PaySuccess(Model_PayOrder $pay_order);
    public abstract function RefundSuccess(Model_PayOrder $pay_refund_order);
}