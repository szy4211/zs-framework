<?php
/**
 * @description: mongodb
 *
 * @date 2019-06-18
 * author zornshuai@foxmail.com
 */

namespace Framework\Model\Mongodb;


use Framework\Exception\ErrorException;
use Framework\MongoDb;

class DB
{
    /**
     * @var MongoDb
     */
    protected static $instance = null;

    private function __construct()
    {
    }

    public static function instance($table = '')
    {
        if (null === self::$instance) {
            self::$instance = new MongoDb(config('mongodb.'));
        }

        return self::$instance;
    }

    public function __call($name, $arguments)
    {
        if (method_exists(self::instance(), $name)) {
            return self::$instance->$name(...$arguments);
        }

        throw new ErrorException('Method ' . $name . ' not exists ');
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(self::instance(), $name)) {
            return self::$instance->$name(...$arguments);
        }

        throw new ErrorException('Method ' . $name . ' not exists ');
    }
}