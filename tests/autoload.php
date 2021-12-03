<?php
// +----------------------------------------------------------------------
//
// +----------------------------------------------------------------------
define('TEST_PATH', __DIR__ . '/');
// 加载框架基础文件
require __DIR__ . '/../vendor/topthink/framework/base.php';
require __DIR__ . '/../vendor/autoload.php';

\think\Loader::addNamespace('tests', TEST_PATH);