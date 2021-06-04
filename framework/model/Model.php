<?php

namespace Framework\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    use LoadConnection;

    protected static $fired = false;

    protected static $config = [];

    public function __construct(array $attributes = [])
    {
        if (!self::$fired) {
            self::loadDefaultConnection();
            self::$fired = true;
        }

        parent::__construct($attributes);
    }
}