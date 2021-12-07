<?php

namespace JoinPhpPayment\channel;

use Alipay\EasySDK\Kernel\Factory;
use think\Exception;
use think\facade\Config;
use think\facade\Log;// 支付成功出口

/**
 * 支付 （微信、支付宝）
 * 步骤一： pc端生成聚合支付二维码、移动版直接跳到聚合支付链接
 * 步骤二：根据不同客户端USER_AGENT，下预付单->展示调用不同支付业务的界面
 *
 *
 * Class Payment
 * @package app\extend
 */
class AlipayClient implements IChannelClient
{
    // +----------------------------------------------------------------------
    // | 支付配置
    // +----------------------------------------------------------------------
    /**
     * @return array
     */
    private static function getAlipayConfig()
    {
        $cfg =  \config('api.alipay_mini');
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
        $options->notifyUrl = 'https://' . Config::get('app_host') . '/payment/notify/alipay';

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        $options->encryptKey =  $cfg['encryptKey'];
        return $options;
    }

    /**
     * 获得支付宝服务
     * @return \Alipay\EasySDK\Kernel\Payment
     */
    public static function getAlipayApp()
    {
        $config = self::getAlipayConfig();
        Factory::setOptions($config);
        $app = Factory::payment();
        return $app;
    }
    /**
     * 下预付单
     * @param $pay_channel  string      支付渠道 WxPay,AliPay
     * @param $pay_order        Model_PayOrder      订单标题
     * @param $extend array      业务订单号 商户订单号，商户网站订单系统中唯一订单号，必填
     */
    public static function PrepayOrder(\JoinPhpPayment\model\Model_PayOrder $pay_order, array $extend)
    {
        // 支付宝支付逻辑
        $config = self::getAlipayConfig();
        Log::info($config);
        Factory::setOptions($config);
        $app = Factory::payment();
//        if($sub_merchant){
//            $app->setSubMerchant($sub_merchant);  // 子商户 AppID 为可选项
//        }
        // 测试环境处理
        if(Config::get('api.pay_test')==true){
            $total_fee= 0.01; // 测试环境 一份钱测试
        }
        //拓展参数 建立返佣参数
        $extend_params=[
            'sys_service_provider_id'=>'2088331553170600'
        ];
        $result = $app->common()
            ->optional('extend_params',$extend_params)
            ->create(
            $pay_order,
            $extend,
            $total_fee,
            $openid);
        return $result;
    }
    public static function PayOrder($pay_order,$options){
        try {
            // 3.生成支付参数
            $result=  self::PrepayOrder($pay_order['title'],$pay_order['id'],$pay_order['amount'],$options['openid']);
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
     * @param $pay_refund_order
     * @return \Alipay\EasySDK\Payment\Common\Models\AlipayTradeRefundResponse
     * @throws \Exception
     */
    public static function RefundOrder($pay_refund_order){
        $config = self::getAlipayConfig();
        Factory::setOptions($config);
        $app = Factory::payment();
        // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
        // 测试环境处理
        if(Config::get('api.pay_test')==true){
            // 测试环境 一份钱测试
            $pay_refund_order["pay_amount"] =  0.01;
            $pay_refund_order["refund_amount"] =  0.01;
        }
        $result =  $app->common()
            ->optional("refund_reason",$pay_refund_order["refund_desc"])
            ->optional("out_request_no",$pay_refund_order["id"])
            ->refund(
                $pay_refund_order['pay_id'],// $outTradeNo,
                $pay_refund_order["refund_amount"] //$refundAmount
            );
        return $result;
    }

    /**
     * 查询 支付平台订单信息
     * @param $pay_order
     * @return \Alipay\EasySDK\Payment\Common\Models\AlipayTradeQueryResponse
     * @throws \Exception
     */
    public static function QueryOrder($pay_order)
    {
        $config = self::getAlipayConfig();
        Factory::setOptions($config);
        $app = Factory::payment();
        $res = $app->common()->query($pay_order['id']);
        return $res;
    }
}