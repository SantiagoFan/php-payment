<?php

namespace JoinPhpPayment\channel;

use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Payment;
use JoinPhpPayment\core\PayFactory;
use JoinPhpPayment\model\Model_PayOrder;
use think\Exception;
use think\facade\Config;
use think\facade\Log;

// 支付成功出口

/**
 * Class AlipayClient
 * @package JoinPhpPayment\channel
 */
class AlipayClient implements IChannelClient
{
    /**
     * @var Payment
     */
    private $app;
    /**
     * 配置
     * @var array
     */
    private $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
        Factory::setOptions($this->config);
        $this->app = Factory::payment();
    }
    // +----------------------------------------------------------------------
    // | 支付配置
    // +----------------------------------------------------------------------
    /**
     * 支付成功通知处理
     * @param Closure $callback
     * @return string
     * @throws \Exception
     */
    public function PayNotify($data,Closure $callback): string
    {
        try {
            $ver = $this->app->common()->verifyNotify($data);
            if(!$ver){ return '参数校验失败'; }
        }
        catch (\Exception $e){
            return '参数错误:'.$e->getMessage();
        }

        $pay_order_id =$data['out_trade_no'];//商户订单号
        Log::info("****************** 参数格式正确  ：${$pay_order_id}******************");
        $pay_order = Model_PayOrder::get($pay_order_id);
        if($pay_order==null){
            Log::error($pay_order_id."订单查询错误！，请查看具体逻辑代码");
            return '订单号错误';
        }

        Log::info("查询订单");
        // 重新查询远程订单状态
        $res = $this->QueryOrder($pay_order);
        Log::info(json_encode($res));

        if($res->tradeStatus!="TRADE_SUCCESS"){
            return '订单未正确支付';
        }
        $amount =$data['total_amount'];
        $channel_no = $data['trade_no']; //支付宝支付订单号
        if ($data['trade_status'] === 'TRADE_SUCCESS') {
            $pay_channel = $this->trade_type[$data['trade_type']];
            $pay_order = PayFactory::PaySuccess($pay_channel, $amount, $pay_order_id, $channel_no); //更新支付订单
            call_user_func($callback,$pay_order);

            $pay_order = PayFactory::PaySuccess($pay_channel, $amount, $pay_order_id, $channel_no); //更新支付订单
            call_user_func($callback,$pay_order);
        } else {
            return '通信失败，请稍后再通知我';
        }
        return 'success';
    }


    /**
     * @return \Alipay\EasySDK\Kernel\Config
     */
    private function getConfig()
    {
        $cfg = PayFactory::getPayConfig('alipay');
        $options = new \Alipay\EasySDK\Kernel\Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';
        $options->appId = $cfg['app_id'];
        $options->merchantPrivateKey = $cfg['merchantPrivateKey'];//应用私钥

//        $options->alipayCertPath = '<-- 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt -->';
//        $options->alipayRootCertPath = '<-- 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt" -->';
//        $options->merchantCertPath = '<-- 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt -->';

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        $options->alipayPublicKey = $cfg['alipayPublicKey'];

        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = $cfg['pay_notify_url'];

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        $options->encryptKey =  $cfg['encryptKey'];
        return $options;
    }

    /**
     * 下预付单
     * @param Model_PayOrder $pay_order   订单标题
     * @param $extend array      业务订单号 商户订单号，商户网站订单系统中唯一订单号，必填
     * @return \Alipay\EasySDK\Payment\Common\Models\AlipayTradeCreateResponse
     * @throws \Exception
     */
    public function PrepayOrder(Model_PayOrder $pay_order, array $extend)
    {
        return $this->app->common()
            ->create(
            $pay_order['title'],
            $pay_order['id'],
            $pay_order['amount'],
            $extend['openid']);
    }

    /**
     * @throws \Exception
     */
    public function PayOrder($pay_order, $options): string
    {
        try {
            // 3.生成支付参数
            $result=  $this->PrepayOrder($pay_order,$options);
            //3. 处理响应或异常
            if (!empty($result->code) && $result->code == 10000) {
                return $result->tradeNo;
            } else {
                throw new \Exception("alipay:下预付单错误-".$result->msg.",".$result->sub_msg);
            }
        } catch (Exception $e) {
            throw new \Exception("alipay:下预付单错误-".$e->getMessage());
        }
    }

    /**
     * 支付宝退款
     * @param Model_PayOrder $pay_refund_order
     * @return string[]
     * @throws \Exception
     */
    public function RefundOrder(Model_PayOrder $pay_refund_order):array{

        // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
        $result =  $this->app->common()
            ->optional("refund_reason",$pay_refund_order["refund_desc"])
            ->optional("out_request_no",$pay_refund_order["id"])
            ->refund(
                $pay_refund_order['pay_id'],// $outTradeNo,
                $pay_refund_order["refund_amount"] //$refundAmount
            );
        // 返回状态
        if ($result->code === '10000') {
            return [ "code"=>"success" ];
        } else {
            Log::error('支付宝退款错误：');
            Log::error(json_encode($result));
            return [ "code"=>"error","message"=> $result->msg.$result->subMsg ];
        }
    }

    /**
     * 查询 支付平台订单信息
     * @param $pay_order
     * @throws \Exception
     */
    public function QueryOrder($pay_order)
    {
        return $this->app->common()->query($pay_order['id']);
    }
}