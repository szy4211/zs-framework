<?php
/**
 * @description: mongodb 模型类
 *
 * @date 2019-06-18
 * author zornshuai@foxmail.com
 */

namespace Framework\Model\Mongodb;

class Model
{
    /**
     * @var DB
     */
    protected static $db = null;

    /**
     * @var string 表名
     */
    protected $table = 'test';

    public function __construct()
    {
        self::db();
    }

    public function db()
    {
        if (null === self::$db) {
            self::$db = DB::instance();
            self::$db->table($this->table);
        }

        return self::$db;
    }

    public function setTable($table)
    {
        $this->table = $table;
        self::db()->table($table);
    }

    public function __call($name, $arguments)
    {
        return self::db()->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static())->$name(...$arguments);
    }
}