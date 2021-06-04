<?php
/**
 * @description: 入口文件
 *
 * @date 2019-06-13
 */

//  定义环境配置
define('SYS_ENV', 'dev');
define('SYS_DEBUG', true);

$app = require __DIR__.'/../bootstrap/app.php';


$app->run()->send();