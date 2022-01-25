# PHP-JOIN-PAYMENT php 支付工程
GITHUB  https://github.com/SantiagoFan/php-payment
GITEE  https://gitee.com/san_fan/php-paymentgit
### composer 安装
需要 composer 版本2+

![avatar](doc/支付中间件（draw可编辑）.svg)
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
    // 注入配置
    public static function init(){
        $config  = new self();
        PayFactory::init($config);
    }
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
配置示例
```
return [
    // +----------------------------------------------------------------------
    // | 微信 支付参数
    // +----------------------------------------------------------------------
    "wxpay"=>[
        'app_id' => '',
        'mch_id' => '',
        'key' => '',
        'pay_notify_url' =>'https://' . Config::get('app_host') . '/payment/notify/wxpay',
        'refund_notify_url' =>'https://' . Config::get('app_host') . '/payment/notify/wxrefund'
    ],
    // +----------------------------------------------------------------------
    // | 支付宝 支付参数
    // +----------------------------------------------------------------------
    alipay=>[
        'app_id' => '',
        'merchantPrivateKey' => '',
        'alipayPublicKey'=>'',
        'encryptKey'=>'',
        'pay_notify_url' => 'https://' . Config::get('app_host') . '/payment/notify/alipay';
    ]
]
```
支付前注入配置
```php
PaymentConfig::init();
```
### 4.集成异步通知 Controller
此通知入口对应地址需要和配置类的通知地址一致
```php
class NotifyController extends BaseNotifyController
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        PaymentConfig::init(); //如果全局钩子函数注入配置则不用写次函数
    }
}
```
如果需要全局处理 支付成功后或者退款后的业务，notify 类覆盖父类方法PaySuccess
或者 RefundSuccess。  
如果需要处理业务订单成功后的业务，请在相关业务model 里的PaySuccess方法处理

### 5.编写业务类 Model
编写业务类需要实现 IPayableOrder 或者直接继承 BasePayableOrder 基类  
根据不同业务类型可创建不同的model
```php
class MyOrder extends BasePayableOrder
{
    // 业务名称
    protected $pk ='order_no';
    protected $business_name = 'my_order';
    /**
     * 因为字段不一样 覆盖父级方法
    * 如果业务类包含：title、amount、order_no 则无需编写次方法
     * @return Model_PayOrder
     */
    public function CreatePayOrder():Model_PayOrder
    {
//        $pay_order = new Model_PayOrder();
//        $pay_order['title']= $this['name'];
//        $pay_order['amount']=  $this['price'];
//        $pay_order['business_no']=  $this['order_no'];
//        $pay_order['business_name']= $this->GetBusinessName();
//        return $pay_order;
    }
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
### 6.编写业务调用支付
```php
        // 查询或者创建订单
        // $bus_order =  new MyOrder();
        $bus_order = MyOrder::get('10001');
        //支付客户端类型
        $client = PayClient::WEIXIN_QRCODE; //小程序参数
        $params = [];
        // 获得支付参数
        $res = $bus_order->PayOrder($client, $params);
```