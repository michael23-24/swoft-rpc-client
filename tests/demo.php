<?php
// +----------------------------------------------------------------------
// | 演示代码
// +----------------------------------------------------------------------
// | Copyright (c) 义幻科技 http://www.mobimedical.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Michael23
// +----------------------------------------------------------------------
// | date: 2020-12-15
// +----------------------------------------------------------------------
require "../src/DataFormate.php";
require "../src/SwoftRpcClient.php";

$timestamp = time();
$secret = "123";
$rpcObj = new \SwoftRpcClient\SwoftRpcClient("127.0.0.1", 18308, 100,$secret,$timestamp);

$getRes = $rpcObj->request("Request", 'get', ["https://histest.mobimedical.cn/index.php?g=Wap&m=Test&a=put&id=" . mt_rand(100, 999)]);


var_dump($getRes);
