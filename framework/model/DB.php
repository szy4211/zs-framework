<?php

namespace Framework\Model;

use Illuminate\Database\Capsule\Manager;

class DB extends Manager
{

    use LoadConnection;

    public static function table($table, $connection = NULL)
    {
        if (empty(self::getConnection())) {
            self::loadDefaultConnection();
        }

        return parent::table($table, $connection);
    }
}