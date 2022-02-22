<?php


namespace JoinPhpPayment\core;

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
     * 注入配置到支付服务中
     * @return mixed
     */
    public static function init();

    /**
     * 获取业务名称映射表
     * @return mixed
     */
    public function getBusinessMap();
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