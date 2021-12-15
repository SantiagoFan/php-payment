# PHP-JOIN-PAYMENT php 支付工程

### composer 安装
需要 composer 版本2+

```
composer require join/php-payment
```
包地址
https://packagist.org/packages/join/php-payment

### require 依赖

php：>=7.2.0

## 代码结构


### 如何参与开发成为代码贡献人员

1. 将项目fork到自己帐号
2. 修改代码完成测试
3. 提交commit push 到自己的仓库
3. New pull request(简称pr) 合并请求到主库等待合并


## 使用教程
### 1.安装包
```
composer require join/php-payment
```
### 2.创建数据表
复制源码文件夹的sql 脚本创建 交易流水表
```
vendor/join/php-payment/doc/model/model-[版本].sql
```
### 3.编写配置类（实现 IPaymentConfig 接口）
```php
class PaymentConfig implements IPaymentConfig{
    // 注入配置信息
    public function getPayConfig(string $type){
        if($type=='wxpay'){
            return Config::get('payment.wxpay');
        }
    }
    // 获取业务类实例
    public function getBusinessOrder(string $business_name): IPayableOrder
    {
        // 具体请映射业务类
        if($business_name =='my_order'){
//            return new MyOrder();
        }
        else{
            throw new Exception('业务订单未定义：'.$business_name);
        }
    }
    // 配置客户端 对应支付通道
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
```
### 4.集成异步通知
```php
class NotifyController extends BaseNotifyController
{
    
    public function PaySuccess(Model_PayOrder $pay_order)
    {
        // 支持成功处理完成后回调-无特殊业务写空方法即可
    }

    public function RefundSuccess(Model_PayOrder $pay_refund_order)
    {
        // 退款成功处理完成后回调-无特殊业务写空方法即可
    }
}
```

### 5.编写业务类
编写业务类需要实现 IPayableOrder 或者直接继承 BasePayableOrder 基类
```php
class MyOrder extends BasePayableOrder
{
    // 业务名称
    protected $business_name = 'my_order';
    public function PaySuccess(Model_PayOrder $pay_order)
    {
        // 支付成功后 业务单 后续逻辑
    }
    public function RefundedSuccess(Model_PayOrder $pay_refund_order)
    {
        // 退款成功后 业务单 后续逻辑
    }
}
```