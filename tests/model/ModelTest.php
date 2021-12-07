<?php

namespace tests\model;

use JoinPhpPayment\core\PayClient;
use JoinPhpPayment\core\PayFactory;

use PHPUnit\Framework\TestCase;
use think\Db;


class ModelTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $config =$this->getConfig();
        Db::init($config);

        $config  = new PaymentConfig();
        PayFactory::init($config);
    }

    public function testPay(){
        $bus_order = MyOrder::get('10001');

        // 发起支付
//        $client = PayClient::WEIXIN_QRCODE; //小程序参数
//        $res = $bus_order->PayOrder($client,[]);
//        var_dump($res);

//        $client = PayClient::WEIXIN_MP; //小程序参数
//        $openid = 'ooaXI5WykGWLMM_UTtVJueqC-uz0';
//        $res = $bus_order->PayOrder($client,[
//            'openid'=>$openid
//        ]);
//        $res = $bus_order->RefundOrder(1,'用戶調用');
        echo '----完成--------';
//        echo json_encode($res);
        echo '------完成--';
    }
    private function getConfig(){
        return [
            'type'            => 'mysql',
            'hostname'        => '127.0.0.1',
            'database'        => 'testdb',
            'username'        => 'testdb',
            'password'        => 'testdb',
            'hostport'        => '3306',
            'dsn'             => '',
            'params'          => [],
            'charset'         => 'utf8mb4',
            'prefix'          => '',
            'debug'           => true,
            'deploy'          => 0,
            'rw_separate'     => false,
            'master_num'      => 1,
            'slave_no'        => '',
            'read_master'     => false,
            'fields_strict'   => true,
            'resultset_type'  => 'array',
            'auto_timestamp'  => false,
            'datetime_format' => 'Y-m-d H:i:s',
            'sql_explain'     => false,
            'builder'         => '',
            'query'           => '\\think\\db\\Query',
            'break_reconnect' => false,
            'break_match_str' => [],
        ];
    }
}