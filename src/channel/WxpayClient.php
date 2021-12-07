<?php

namespace JoinPhpPayment\channel;

use EasyWeChat\Factory;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JoinPhpPayment\core\PayChannel;
use JoinPhpPayment\core\PayFactory;
use JoinPhpPayment\model\Model_PayOrder;
use think\facade\Log;


// 支付成功出口

/**
 *
 * Class Payment
 * @package app\extend
 */
class WxpayClient implements IChannelClient
{
    /**
     * 支付操作类
     * @var \EasyWeChat\Payment\Application
     */
    private $app;
    /**
     * 配置
     * @var array
     */
    private $config;
    /**
     * 渠道类型  对应  交易类型
     * @var string[]
     */
    private $trade_type = [
        PayChannel::WEIXIN_PAY_NATIVE => 'NATIVE',
        PayChannel::WEIXIN_PAY_JS => 'JSAPI',
    ];
    public function __construct()
    {
        $this->config = PayFactory::getPayConfig('wxpay');
        $this->app = Factory::payment($this->config);
    }
    // +----------------------------------------------------------------------
    // | 接口方法
    // +----------------------------------------------------------------------
    /**
     * 支付订单 获取支付参数
     * @param Model_PayOrder $pay_order
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function PayOrder(Model_PayOrder $pay_order, array $options): array
    {
        // 3.生成支付参数
        $result = $this->PrepayOrder($pay_order,$options);
        // 4.组装参数
        if(isset($result['result_code']) && $result['result_code']=="SUCCESS"){
            // js 支付参数
            $trade_type =  $this->trade_type[$pay_order['pay_channel']];
            if($trade_type == 'JSAPI'){
                $config = $this->app->jssdk->bridgeConfig($result['prepay_id']);
                return ['config'=>$config];
            }
            // native 支付参数
            else if($trade_type == 'NATIVE'){
                return [ "code_url"=>$result['code_url'] ];
            }
            else{
                throw new Exception('支付类型异常');
            }
        }
        else{
            Log::error(json_encode($result));
            throw new Exception("WxpayClient:下预付单错误".$result['err_code_des']);
        }
    }

    /**
     * 支付成功通知处理
     * @throws Exception
     */
    public function PayNotify(){
        $response = $this->app->handlePaidNotify(function($data, $fail) {
            $pay_order_id =$data['out_trade_no'];//商户订单号
            $amount = $data['total_fee']/100; // 单位 分转元
            $channel_no = $data['transaction_id']; //微信支付订单号

            Log::info("****************** 参数格式正确  ：${$pay_order_id}******************");

            $pay_order = Model_PayOrder::get($pay_order_id);
            if($pay_order==null){
                Log::error($pay_order_id."订单查询错误！，请查看具体逻辑代码");
                return $fail('订单号错误');
            }
            Log::info("查询订单");
            // 重新查询远程订单状态
            $res = $this->QueryOrder($pay_order);
            //微信记录
            Log::info(json_encode($res));
            if($res['trade_state']!="SUCCESS"){
                return $fail('订单未正确支付');
            }

            if ($data['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if ($data['result_code'] === 'SUCCESS') {
                    $pay_channel = $this->trade_type[$data['trade_type']];
                    PayFactory::PaySuccess($pay_channel, $amount, $pay_order_id, $channel_no); //更新支付订单
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        });
        $response->send();
    }

    /**
     * @throws Exception
     */
    public function RefundNotify(){
        $response = $this->app->handleRefundedNotify(function ($message,$data,$fail) {
            // 参数
            $pay_refund_id = $data['out_refund_no'];
            $pay_id = $data["out_trade_no"];
            $channel_refund_no = $data['refund_id']; //微信退款订单号
            $refund_amount = $data['refund_fee']/100; // 单位 分转元

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                if ($data['refund_status'] === 'SUCCESS') {
                    PayFactory::RefundSuccess($pay_refund_id,$refund_amount,$pay_id,$channel_refund_no); // 通知支付退款订单
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        $response->send();
    }

    // +----------------------------------------------------------------------
    // | 私有方法
    // +----------------------------------------------------------------------

    /**
     * 下预付单-jsapi
     * @param Model_PayOrder $pay_order 订单标题
     * @param $extend array      业务订单号 商户订单号，商户网站订单系统中唯一订单号，必填
     * @throws Exception|GuzzleException
     */
    public function PrepayOrder(Model_PayOrder $pay_order, array $extend)
    {
        if(isset($extend['sub_merchant'])){
            $this->app->setSubMerchant($extend['sub_merchant']);
        }
        $pay_fee = intval(round($pay_order['amount'] * 100));
        $trade_type = $this->trade_type[$pay_order['pay_channel']];
        // 下单参数
        $params = [
            'body' => $pay_order['title'],
            'out_trade_no' => $pay_order['id'],
            'total_fee' => $pay_fee,// 微信以分为单位
            'trade_type' => $trade_type,
            'notify_url'=>$this->config['pay_notify_url']
        ];

        if($trade_type == 'JSAPI'){
            $params['openid'] = $extend['openid'];
        }
        return $this->app->order->unify($params);
    }


    /**
     * @param Model_PayOrder $pay_refund_order
     */
    public function RefundOrder(Model_PayOrder $pay_refund_order): array{

        $total_fee = intval(round($pay_refund_order["original_amount"]*100));
        $refund_fee = intval(round($pay_refund_order["amount"]*100));

        $result = $this->app->refund->byOutTradeNumber(
            $pay_refund_order['original_id'],
            $pay_refund_order["id"],
            $total_fee,
            $refund_fee,
            [
                'refund_desc' => $pay_refund_order["title"],
                'notify_url'=>$this->config['refund_notify_url']
            ]
        );
        // 返回状态
        if ($result['return_code'] === 'SUCCESS'&& $result['result_code'] === 'SUCCESS') {
            return [ "code"=>"pending" ];
        } else {
            return [ "code"=>"error","message"=> $result['return_msg'] ];
        }
    }
    /**
     * 查询 支付平台订单信息
     * @param Model_PayOrder $pay_order
     */
    public function QueryOrder($pay_order,$sub_merchant=null)
    {
        if($sub_merchant){
            $this->app->setSubMerchant($sub_merchant);  // 子商户 AppID 为可选项
        }
        $res = $this->app->order->queryByOutTradeNumber($pay_order["id"]);
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
//        $config = self::getWxpayConfig();
//        $app = Factory::payment($config);
//        $res = $app->refund->queryByOutTradeNumber($outTradeNumber);
//        return $res;
    }
}