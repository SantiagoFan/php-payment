<?php


namespace JoinPhpPayment\core;

use Exception;
use JoinPhpPayment\model\Model_PayOrder;
use think\facade\Log;

/**
 * 支付核心服务
 * Class PayFactory
 * @package JoinPhpPayment\core
 */
class PayFactory
{
    // 支付单单号前缀
    public static $pay_order_prefix='6';
    // 退款单单号前缀
    public static $refund_order_prefix='4';
    /**
     * 支付配置类
     * @var IPaymentConfig
     */
    public static $paymentConfig;

    /**
     * 发起支付
     * @param IPayableOrder $business_order
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public static function PayOrder(IPayableOrder $business_order, array $options): array
    {
        // 1.获取支付订单
        $tempOrder = $business_order->CreatePayOrder();
        $tempOrder["business_name"] =$business_order->GetBusinessName();
        $pay_order = Model_PayOrder::where(
            [
                'business_no'=>$tempOrder["business_no"],
                'business_name'=>$tempOrder["business_name"],
                'is_refund' => false
            ]
        )->find();
        //todo: 是否允许生成重复订单？

        // 2.支付单不存在 创建支付订单
        if($pay_order){
            if($pay_order['state']!= Model_PayOrder::STATE_APPLY){
                throw new \Exception('订单状态错误');
            }
        }
        else{
            $pay_order =  $tempOrder;
            $pay_order['id'] = self::build_order_no(self::$pay_order_prefix);
        }
        $pay_order["apply_time"] = date('Y-m-d H:i:s');
        $pay_order["pay_channel"] = self::getPayChannel($options["client"]); // 客户端类型映射 支付通道
        $pay_order["state"] = Model_PayOrder::STATE_APPLY; // 1
        $pay_order->save();

        $need_pay = true; // 是否需要实际支付（金额为0 不实际支付，走全部回调流程）
        if($pay_order['amount']==0){
            $need_pay = false;
            $param = [];
            PayFactory::PaySuccess('system', 0, $pay_order['id'], '');
        }
        else{
            $app = PayChannel::GetPayChannelApp($pay_order["pay_channel"]); // 获得支付操作类
            $param = $app->PayOrder($pay_order,$options);
        }
        $param['need_pay'] = $need_pay;
        return $param;
    }

    /**
     * 支付成功
     * @param $pay_channel
     * @param $amount
     * @param $pay_order_id
     * @param $channel_no
     * @return Model_PayOrder|void
     * @throws Exception
     */
    public static function PaySuccess($pay_channel, $amount, $pay_order_id, $channel_no)
    {
        Log::info("PayFactory-PaySuccess 开始处理：".$pay_order_id);
        $pay_order = Model_PayOrder::get($pay_order_id);
        //-------------- 检查---------------------
        if ($pay_order == null) {
            Log::error("订单{$pay_order_id}不存在！");
            return;
        };
        if ($pay_order['state'] == Model_PayOrder::STATE_SUCCESS) {
            Log::info('订单已经处理支付，跳过处理流程,订单：'.json_encode($pay_order->toArray()));
            return;
        };
        //-------------- 支付系统处理---------------------
        try {
            //更新支付流水状态
            $pay_order->PaySuccess($pay_channel,$channel_no,$amount);
            Log::info('支付订单信息更新完成');
        }
        catch (\Exception $e){
            Log::error('支付订单处理错误'.$e->getMessage());
            throw $e;
        }

        //-------------- 业务系统处理---------------------
        try {
            $business_order =  self::$paymentConfig->getBusinessOrder($pay_order['business_name']);
            $business_order->PaySuccess($pay_order);
        }
        catch (\Exception $e){
            Log::error('业务订单支付处理错误错误'.$e->getMessage());
            throw $e;
        }
        return $pay_order;
    }

    /**
     * 发起退款
     * @param IPayableOrder $business_order
     * @param $refund_amount
     * @param $refund_reason
     * @throws \Exception
     */
    public static function RefundOrder(IPayableOrder $business_order,$refund_amount,$refund_reason){
        Log::info("--------开始发起退款------------");
        // 1.获取支付订单
        $tempOrder = $business_order->CreatePayOrder();
        $tempOrder["business_name"] =$business_order->GetBusinessName();
        $pay_order = Model_PayOrder::where(
            [
                'business_no'=>$tempOrder["business_no"],
                'business_name'=>$tempOrder["business_name"],
                'is_refund' => false
            ]
        )->find();
        // 可退金额限制
        $refundable_amount = $pay_order->getRefundableAmount();
        if(bcsub($refundable_amount,$refund_amount,2)<0){
            throw new Exception("可退款金额已超过支付金额");
        }
        $refund_order_id =  self::build_order_no(self::$refund_order_prefix);
        $refund_order = $pay_order->createRefundOrder($refund_order_id,$refund_amount,$refund_reason);
        $refund_order["apply_time"] = date('Y-m-d H:i:s');
        $refund_order['state'] = Model_PayOrder::STATE_DEFAULT;
        $refund_order->save();
        // 开始退款
        if(abs($refund_order['amount']) == 0){
            $refund_order['state'] = Model_PayOrder::STATE_APPLY;
//            $refund_order->save();
            self::RefundSuccess($refund_order_id,0,$pay_order['original_id'],'');
        }
        else{
            $app = PayChannel::GetPayChannelApp($pay_order["pay_channel"]); // 获得支付操作类
            $res = $app->RefundOrder($refund_order);
            //检查状态
            if($res['code'] == 'success'){
                $refund_order['state'] = Model_PayOrder::STATE_APPLY;
                $refund_order->save();
                self::RefundSuccess(
                    $refund_order_id,
                    $refund_order_id['amount'],
                    $pay_order['original_id'],
                    $res['refund_channel_no']
                );
            }
            elseif ($res['code'] =='pending'){ //异步结果
                $refund_order['state'] = Model_PayOrder::STATE_APPLY;
                $refund_order->save();
            }
            elseif ($res['code'] =='error'){
                return ["code"=>50000,"message"=>$res["message"]];
            }
            else{
               return ["code"=>50000,"message"=>"PayFactory.RefundOrder 退款状态错误"];
            }
        }
        return ["code"=>20000,"message"=>"success"];
    }

    /**
     * 退款成功
     * @param $pay_refund_id
     * @param $refund_amount
     * @param $pay_id
     * @param $channel_refund_no
     * @return Model_PayOrder|void
     * @throws Exception
     */
    public static function RefundSuccess($pay_refund_id,$refund_amount,$pay_id,$channel_refund_no){
        Log::info("---------Payment::RefundProcess 开始处理 ------------");
        // 更新退款订单状态
        $refund_order = Model_PayOrder::get($pay_refund_id);

        //-------------- 检查---------------------
        if($refund_order == null){  Log::error('退款订单不存在'); return; }
        if ($refund_order['state'] == Model_PayOrder::STATE_SUCCESS) { Log::info('订单已经处理过退款，跳过处理流程'); return; };

        //-------------- 支付系统处理---------------------
        try {
            $refund_order->RefundSuccess($channel_refund_no,$refund_amount);
        }catch (\Exception $e){
            Log::error('支付订单处理错误'.$e->getMessage());
            throw $e;
        }

        //-------------- 业务系统处理---------------------
        try {
            $business_order = self::$paymentConfig->getBusinessOrder($refund_order['business_name']);
            $business_order->RefundedSuccess($refund_order);
        }
        catch (\Exception $e){
            Log::error('业务订单退款处理错误'.$e->getMessage());
            throw $e;
        }
        return $refund_order;
    }

    //生成唯一订单号
    private static function build_order_no(string $prefix = ''): string
    {
        return $prefix.date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).rand(1000,9999);
    }
    /**
     * 初始化
     * @param IPaymentConfig $payment_config
     */
    public static function init(IPaymentConfig $payment_config){
        self::$paymentConfig = $payment_config;
    }

    /**
     * @param $client
     * @return mixed|string
     */
    public static function getPayChannel($client){
        return self::$paymentConfig->getPayChannel($client);
    }

    /**
     * 获取参数
     * @param $key
     * @return mixed
     */
    public static function getPayConfig($key){
        return self::$paymentConfig->getPayConfig($key);
    }
}