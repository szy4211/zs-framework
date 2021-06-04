<?php
/**
 * @description: 日志相关配置
 *
 * @date 2019-06-13
 */

return [
        // 是否记录日志文件
        'files' => true,
        // 自定义日志记录方法
//        'sendLog' => array('Common', 'sendLog'),
        //错误级别
        'level' => 'NOTICE',
        // 存储路径
//        'root_path' => '',
        // 慢查询阀值
        'slowQuery' => 1000,
];