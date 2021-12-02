<?php
/**
 * 聚合支付
 * User: santiago-范文刚
 * Date: 19-8-14
 * Time: 上午9:19
 */

namespace app\payment\channel;

use app\api\controller\PackageOrder;
use app\payment\model\Model_PayOrder;
use EasyWeChat\Factory;
use think\Exception;
use think\facade\Config;

use app\wap\common\PaymentProcess;
use think\facade\Log;// 支付成功出口

/**
 *
 * Class Payment
 * @package app\extend
 */
class WxpayClient
{
    // +----------------------------------------------------------------------
    // | 支付配置
    // +----------------------------------------------------------------------
    /**
     * @return array
     */
    private static function getWxpayConfig($type='pay')
    {
        $config = Config::get('api.wxpay');
        if($type=='pay'){
            $config['notify_url'] = 'https://' . Config::get('app_host') . '/payment/notify/wxpay';
        }else if($type=='refund'){
            $config['notify_url'] = 'https://' . Config::get('app_host') . '/payment/notify/wxrefund';
        }
        else{
            throw new \Exception('type 类型错误');
        }
        return $config;
    }
    /**
     * 获取用于调取支付的参数
     * @param $prepay
     * @return array
     */
    public static function getPayParams($prepay_id)
    {
        $config = self::getWxpayConfig();
        $app = Factory::payment($config);
        $config = $app->jssdk->bridgeConfig($prepay_id); // 返回数组
        return $config;
    }
    // 获得微信服务
    public static function getWxApp()
    {
        $config = self::getWxpayConfig();
        $app = Factory::payment($config);
        return $app;
    }
    /**
     * 下预付单-jsapi
     * @param $pay_channel  string      支付渠道 WxPay,AliPay
     * @param $title        string      订单标题
     * @param $out_trade_no string      业务订单号 商户订单号，商户网站订单系统中唯一订单号，必填
     * @param $total_fee    number      支付金额支付金额
     * @param string $openid
     * @param string $body
     */
    public static function PrepayOrder($title, $out_trade_no, $total_fee, $openid = '', $body = ''
        ,$profit_sharing ='N',$sub_merchant=null)
    {
        // 微信支付逻辑
        $config = self::getWxpayConfig();
        $app = Factory::payment($config);
        if($sub_merchant){
            $app->setSubMerchant($sub_merchant);  // 子商户 AppID 为可选项
        }
        // 测试环境处理
        if(Config::get('api.pay_test')==true){
            $total_fee= 0.01; // 测试环境 一份钱测试
        }
        $result = $app->order->unify([
            'body' => $title,
            'out_trade_no' => $out_trade_no,
            'total_fee' =>intval(round($total_fee * 100)) ,// 微信以分为单位
            'trade_type' => 'JSAPI',
            'openid' => $openid,
            'profit_sharing'=>$profit_sharing
        ]);
        return $result;
    }
    public static function PayOrder($pay_order,$options){
        // 3.生成支付参数
        $result=  self::PrepayOrder($pay_order['title'],$pay_order['id'],$pay_order['amount'],$options['openid']);
        // 4.组装参数
        if(isset($result['result_code']) && $result['result_code']=="SUCCESS"){
            $config =self::getPayParams($result['prepay_id']);
            return $config;
        }
        else{
            Log::error(json_encode($result));
            throw new \Exception("wxpay:下预付单错误");
        }
    }

    /**
     * 下预付单-native
     * @param $title
     * @param $out_trade_no
     * @param $total_fee
     * @param string $openid
     * @param string $body
     * @param string $profit_sharing
     * @param null $sub_merchant
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function PrepayOrderNative($title, $out_trade_no, $total_fee,$profit_sharing ='N',$sub_merchant=null)
    {
        // 微信支付逻辑
        $config = self::getWxpayConfig();
        $app = Factory::payment($config);
        if($sub_merchant){
            $app->setSubMerchant($sub_merchant);  // 子商户 AppID 为可选项
        }
        // 测试环境处理
        if(Config::get('api.pay_test')==true){
            $total_fee= 0.01; // 测试环境 一份钱测试
        }
        $result = $app->order->unify([
            'body' => $title,
            'out_trade_no' => $out_trade_no,
            'total_fee' =>intval(round($total_fee * 100)) ,// 微信以分为单位
            'trade_type' => 'NATIVE',
            'profit_sharing'=>$profit_sharing
        ]);
        return $result;
    }

    /**
     * 下预付单- native
     * @param $pay_order
     * @param $options
     * @return array
     * @throws \Exception
     */
    public static function PayOrderNative($pay_order,$options){
        // 3.生成支付参数
        $result=  self::PrepayOrderNative($pay_order['title'],$pay_order['id'],$pay_order['amount']);
        // 4.组装参数
        if(isset($result['result_code']) && $result['result_code']=="SUCCESS"){
            return [
                "code_url"=>$result['code_url']
            ];
        }
        else{
            Log::error(json_encode($result));
            throw new \Exception("wxpay:下预付单错误");
        }
    }
    /**
     * 退款
     * @param $pay_refund_order
     * @param $out_refund_no
     * @param $refund_fee
     * @param string $refund_desc
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function RefundOrder($pay_refund_order){

        // 微信支付逻辑
        $config = self::getWxpayConfig('refund');
        $app = Factory::payment($config);
        // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
        // 测试环境处理
//        if(Config::get('api.pay_test')==true){
//            // 测试环境 一份钱测试
//            $pay_refund_order["pay_amount"] =  0.01;
//            $pay_refund_order["refund_amount"] =  0.01;
//        }
        Log::info([
            $pay_refund_order['pay_id'],
            $pay_refund_order["id"],
            intval(round($pay_refund_order["pay_amount"]*100)),
            intval(round($pay_refund_order["refund_amount"]*100)),
            [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' => $pay_refund_order["refund_desc"],
                'notify_url'=>$config['notify_url']
            ]
        ]);
        $result = $app->refund->byOutTradeNumber(
            $pay_refund_order['pay_id'],
            $pay_refund_order["id"],
            intval(round($pay_refund_order["pay_amount"]*100)),
            intval(round($pay_refund_order["refund_amount"]*100)),
            [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' => $pay_refund_order["refund_desc"],
                'notify_url'=>$config['notify_url']
            ]
        );
        return $result;
    }
    /**
     * 查询 支付平台订单信息
     * @param $order
     */
    public static function QueryOrder($order,$sub_merchant=null)
    {
        $config = self::getWxpayConfig();
        $app = Factory::payment($config);
        if($sub_merchant){
            $app->setSubMerchant($sub_merchant);  // 子商户 AppID 为可选项
        }
        $res = $app->order->queryByOutTradeNumber($order["id"]);
//        $res = $app->order->queryByOutTradeNumber($order["id_v"]);
        //订单不存在
        if (!isset($res['result_code']) || $res['result_code'] != 'SUCCESS') {
            return null;
        }
        return $res;
    }

    /**
     * 外部订单号查询 退款单信息
     * @param $outTradeNumber
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function QueryRefundOrder($outTradeNumber){
        $config = self::getWxpayConfig();
        $app = Factory::payment($config);
        $res = $app->refund->queryByOutTradeNumber($outTradeNumber);
        return $res;
    }
}