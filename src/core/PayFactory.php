<?php


namespace JoinPhpPayment\core;

use app\payment\common\PayChannel;
use JoinPhpPayment\model\Model_PayOrder;

/**
 * 支付核心服务
 * Class PayFactory
 * @package JoinPhpPayment\core
 */
class PayFactory
{
    /**
     * 发起支付
     * @param IPayableOrder $business_order
     * @param array $options
     */
    public static function PayOrder(IPayableOrder $business_order, array $options){
        // 1.获取支付订单
        $tempOrder = $business_order->CreatePayOrder();
        $pay_order = Model_PayOrder::where(
            [
                'internal_no'=>$tempOrder["internal_no"],
                'business_name'=>$tempOrder["business_name"]
            ]
        )->find();
        //todo: 是否允许生成重复订单？

        // 2.支付单不存在 创建支付订单
        if($pay_order){
            if($pay_order['state']!=0){ return ['code'=>50000,'message'=>'订单状态错误']; }
        }
        else{
            $pay_order =  $tempOrder;
            $pay_order['id'] = self::build_order_no();
        }
        $pay_order["pay_apply_time"] = date('Y-m-d H:i:s');
        // 客户端类型映射 支付通道
        $pay_order["pay_channel"] =PayChannel::GetPayChannel($options["client"]);
        $pay_order["business_type"] =$options["business_type"];
        $pay_order["state"] =0;
        $pay_order->save();

    }

}