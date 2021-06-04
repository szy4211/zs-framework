<?php
/**
 * @description: 加载DB连接所需配置
 *
 * @date 2019-06-13
 */

namespace Framework\Model;


use Framework\Exception\ErrorException;
use Illuminate\Database\Capsule\Manager;

trait LoadConnection
{
    /**
     * @description: 设置数据库配置
     * @param array $config
     *
     * @date 2019-06-13
     */
    public static function loadConnection(array $config)
    {

        $capsule = new Manager;
        $capsule->addConnection($config);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * @description: 加载默认配置
     *
     * @throws ErrorException
     * @date 2019-06-13
     */
    protected static function loadDefaultConnection()
    {
        $config = app('config')->pull('database');

        if (!$config) {
            throw new ErrorException('Db Config Error ');
        }

        self::loadConnection($config);
    }
}