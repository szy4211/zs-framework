<?php
/**
 * @description: 路由
 *
 * @date 2019-06-13
 */
use Framework\Router;

Router::get('/', 'HomeController@home');
Router::get('/pdo', 'HomeController@pdo');