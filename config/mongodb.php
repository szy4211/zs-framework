<?php
/**
 * @description: mongodb相关配置
 *
 * @date 2019-06-14
 */

return [
    'driver'   => 'mongodb',
    'host'     => 'localhost',
    'port'     => 27017,
    'database' => 'test',
    'username' => '',
    'password' => '',
    'options'  => [
        'database' => 'admin' // sets the authentication database required by mongo 3
    ]
];