<?php


namespace tests\model;

use JoinPhpPayment\core\IPayableOrder;
use JoinPhpPayment\core\IPaymentConfig;
use JoinPhpPayment\core\PayChannel;
use JoinPhpPayment\core\PayClient;
use think\Exception;
use think\facade\Config;

/**
 * 支付测试类
 * Class PaymentConfig
 * @package model
 */
class PaymentConfig implements IPaymentConfig
{
    /**
     * 获取配置文件
     * @param string $type
     * @return mixed
     */
    public function getPayConfig(string $type){
        if($type=='wxpay'){
            return [
                'app_id' => '',
                'mch_id' => '',
                'key' => '',
                'pay_notify_url' =>'https://' . Config::get('app_host') . '/payment/notify/wxpay',
                'refund_notify_url' =>'https://' . Config::get('app_host') . '/payment/notify/wxrefund'
            ];
        }
    }

    /**
     * 获取业务订单实例
     * @param string $business_name
     * @return IPayableOrder
     * @throws Exception
     */
    public function getBusinessOrder(string $business_name): IPayableOrder
    {
        if($business_name =='my_order'){
            return new MyOrder();
        }
        else{
            throw new Exception('业务订单未定义：'.$business_name);
        }
    }

    /**
     * 客户端类型 对应支付渠道
     * @param string $client
     * @return string
     */
    public function getPayChannel(string $client): string
    {
        // 客戶端支付方式映射支付渠道
         $channel=[
            PayClient::WEIXIN_MP => PayChannel::WEIXIN_PAY_JS,
            PayClient::WEIXIN_QRCODE => PayChannel::WEIXIN_PAY_NATIVE,
            PayClient::ALI_MP => PayChannel::ALI_PAY_JS,
            PayClient::ALI_PAY_QRCODE => PayChannel::ALI_PAY_NATIVE,
        ];
        return $channel[$client];
    }
}