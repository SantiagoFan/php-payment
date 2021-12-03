<?php

namespace model;

use JoinPhpPayment\model\Model_PayOrder;
use PHPUnit\Framework\TestCase;
use think\Db;
use think\facade\Config;

class ModelTest extends TestCase
{
    public function testModel(){
        $config =$this->getConfig();
        Db::init($config);
        $res = Model_PayOrder::where(['id'=>'1'])
            ->fetchSql()
            ->select();
        echo $res;
        $this->assertEquals('','');
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