<?php

namespace tests\model;

use JoinPhpPayment\base\BasePayableOrder;
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
        $pay_order = new Model_PayOrder();
        $pay_order['title']= $this['name'];
        $pay_order['amount']=  $this['price'];
        $pay_order['business_no']=  $this['order_no'];
        $pay_order['business_name']= $this->GetBusinessName();
        return $pay_order;
    }

    public function PaySuccess(Model_PayOrder $pay_order)
    {
        $this['state'] = '1';
        $this->save();
    }



    public function RefundedSuccess(Model_PayOrder $pay_refund_order)
    {
        // TODO: Implement RefundedSuccess() method.
    }
}