<?php
namespace JoinPhpPayment\base;

use JoinPhpPayment\core\IPayableOrder;
use JoinPhpPayment\core\PayFactory;
use JoinPhpPayment\model\Model_PayOrder;
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
     * 创建订单
     * @return Model_PayOrder
     */
    public function CreatePayOrder(): Model_PayOrder{
        $pay_order = new Model_PayOrder();
        $pay_order['title']= $this['title'];
        $pay_order['pay_amount']=  $this['amount'];
        $pay_order['internal_no']=  $this['order_no'];
        $pay_order['business_name']= $this->GetBusinessName();
        return $pay_order;
    }

    /**
     * 发起支付
     * @param string $client
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function PayOrder(string $client, array $options): array
    {
        $options["client"] = $client;
        $options['business_name']= $this->GetBusinessName();
        // 调用服务获取参数
        return PayFactory::PayOrder($this,$options);
    }

    /**
     * 发起退款
     * @param float $amount 退款金额
     * @param string $reason 退款原因
     * @return mixed|void
     */
    public function RefundOrder(float $amount, string $reason)
    {
//        $this['state'] = '2';
//        $this->save();
        return PayFactory::RefundOrder($this,$amount,$reason);
    }
}