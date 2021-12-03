<?php
namespace JoinPhpPayment\model;

use JoinPhpPayment\core\IPayableOrder;
use JoinPhpPayment\core\PayFactory;
use think\Model;

/**
 * 可支付订单抽象基类
 * Class Model_PayOrder
 */
abstract class BasePayableOrder extends Model implements IPayableOrder
{
    /**
     * 业务类型
     * @var string
     */
    protected $business_name = 'business_order';

    /**
     * 获取业务类型
     * @return string
     */
    public function GetBusinessName(): string
    {
        return  $this->business_name;
    }
    /**
     * 依据当前对象创建支付单
     * @param string $order_no  业务订单的 订单唯一标识
     * @param float $amount     支付金额
     * @param string $title     业务描述
     * @param string $business_type  业务类型
     * @return Model_PayOrder
     */
    protected function newPayOrder(string $order_no,float $amount, string $title,string $business_name): Model_PayOrder
    {
        $pay_order = new Model_PayOrder();
        $pay_order['title']= $title;
        $pay_order['amount']= $amount;
        $pay_order['internal_no']= $order_no;
        $pay_order['business_name']= $this->GetBusinessName();
        return $pay_order;
    }
    /**
     * 创建订单
     * @return Model_PayOrder
     */
    public function CreatePayOrder(): Model_PayOrder{
        $pay_order =  $this->newPayOrder(
            $this['title'],
            $this['amount'],
            $this['internal_no'],
            $this->GetBusinessName()
        );
        return $pay_order;
    }

    /**
     * 发起支付
     * @param string $client
     * @param array $options
     * @return array
     */
    public function PayOrder(string $client, array $options): array
    {
        $options["client"] = $client;
        $options['business_type']= $this->GetBusinessName();
        // 调用服务获取参数
        $pay_order = $this->CreatePayOrder();
        $params = PayFactory::PayOrder();
        return $options;
    }

    public static function PaySuccess(Model_PayOrder $pay_order)
    {
    }
    public static function RefundedSuccess(Model_PayOrder $pay_refund_order)
    {
    }

}