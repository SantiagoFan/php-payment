<?php

namespace tests\model;

use app\payment\common\PaymentService;
use JoinPhpPayment\core\PayClient;
use JoinPhpPayment\model\BasePayableOrder;
use JoinPhpPayment\model\Model_PayOrder;

class MyOrder extends BasePayableOrder
{
    protected $pk ='order_no';
    protected $business_name = 'my_order';
    /**
     * 因为字段不一样 覆盖父级方法
     * @return Model_PayOrder
     */
    public function CreatePayOrder():Model_PayOrder
    {
        $pay_order = $this->newPayOrder(
            $this['name'],
            $this['price'],
            $this['order_no']
        );
        return $pay_order;
    }

    public static function PaySuccess(Model_PayOrder $pay_order)
    {
        // TODO: Implement PaySuccess() method.
    }

    public function RefundOrder(float $amount, string $reason)
    {
        // TODO: Implement Refund() method.
    }

    public static function RefundedSuccess(Model_PayOrder $pay_refund_order)
    {
        // TODO: Implement RefundedSuccess() method.
        parent::RefundedSuccess($pay_refund_order);
    }
}