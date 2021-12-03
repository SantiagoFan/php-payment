<?php
namespace JoinPhpPayment\model;

use think\Model;

/**
 * 支付记录表
 * Class Model_PayOrder
 */
class Model_PayOrder extends Model
{
    protected $table = 'pay_order';

    /**
     * 支付状态
     */
    // 支付关闭
    const STATE_PAY_CLOSE = -1;
    // 支付中
    const STATE_PAY_APPLY = 0 ;
    // 支付完成
    const STATE_PAY_SUCCESS = 1 ;
    // 退款中
    const STATE_REFUND_APPLY = 2 ;
    // 退款完成
    const STATE_REFUND_SUCCESS = 3 ;


}