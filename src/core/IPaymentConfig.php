<?php


namespace JoinPhpPayment\Core;

use JoinPhpPayment\model\Model_PayOrder;

/**
 * 支付渠道 实现接口
 *
 * Author        范文刚
 * Email         san_fan@qq.com
 * Time          2021/2/4 下午4:41
 * Version       1.0 版本号
 */
Interface IPaymentConfig
{
    /**
     * 工厂方法：根据业务类型获取 业务类
     * @param string $business_name
     * @return IPayableOrder
     */
    public function getBusinessOrder(string $business_name):IPayableOrder;

    /**
     * 获取支付配置
     * @param string $type
     * @return mixed
     */
    public function getPayConfig(string $type);

    /**
     * 客户端映射 支付类型
     * @param string $client
     * @return mixed
     */
    public function getPayChannel(string $client):string;
}