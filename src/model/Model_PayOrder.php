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
     * 交易状态
     */
    // 交易关闭
    const STATE_CLOSE = -1;
    // 默认
    const STATE_DEFAULT = 0;
    // 交易中
    const STATE_APPLY = 1 ;
    // 支付完成
    const STATE_SUCCESS = 2 ;

    /**
     * 支付成功 更新状态
     * @param string $pay_channel
     * @param string $pay_channel_no
     * @param float $pay_amount 实际支付金额
     * @return Model_PayOrder
     */
    public function PaySuccess(string $pay_channel, string $pay_channel_no, float $pay_amount): Model_PayOrder
    {
        $this["pay_channel_no"] = $pay_channel_no;
        $this['pay_channel'] = $pay_channel;
        $this['real_amount'] = $pay_amount;
        $this["state"] = self::STATE_SUCCESS;
        $this['complete_time'] = date("Y-m-d H:i:s ");
        $this->save();
        return $this;
    }

    /**
     * 退款成功 更新状态
     * @param string $pay_channel_no
     * @param string $refund_amount 实际退款金额
     * @return $this
     */
    public function RefundSuccess(string $pay_channel_no, string $refund_amount): Model_PayOrder
    {
        //更新支付流水状态
        $this["pay_channel_no"] = $pay_channel_no;
        $this['real_amount'] = $refund_amount;
        $this["state"] = self::STATE_SUCCESS;;
        $this['complete_time'] = date("Y-m-d H:i:s ");
        $this->save();
        return $this;
    }

    /**
     * 查询订单可退金额
     */
    public function getRefundableAmount(){
        if($this['state']!=self::STATE_SUCCESS){ //交易成功
            return 0;
        }
        // 已退金额
        $refund_total = self::where([
            'original_id'=>$this['id'],
            'is_refund'=>true,
        ])->whereIn('state',[self::STATE_SUCCESS,self::STATE_APPLY]) //退款中和退款完成
            ->sum('amount');
        // 可退金额
        $refundable_amount = bcsub($this['real_amount'],$refund_total,2);
        return  $refundable_amount > 0 ? $refundable_amount:0;
    }

    /**
     * 依据当前支付单创建退款单
     * @param $refund_order_id
     * @param $refund_amount
     * @param $refund_reason
     * @return Model_PayOrder
     */
    public function createRefundOrder($refund_order_id,$refund_amount,$refund_reason): Model_PayOrder
    {
        $refund_order = new self();
        $refund_order["id"]=$refund_order_id;
        $refund_order["original_id"] = $this["id"];
        $refund_order["original_amount"] = $this["real_amount"];
        $refund_order["business_name"]=$this["business_name"];
        $refund_order["business_no"]=$this["business_no"];
        $refund_order["pay_channel"]= $this["pay_channel"];
        $refund_order["amount"]= '-'.$refund_amount; //负数标记退款
        $refund_order["title"] = $refund_reason;
        $refund_order["is_refund"] = true;
        return $refund_order;
    }
}