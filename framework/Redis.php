<?php
/**
 * @description: Redis操作类
 *
 * @date 2019-06-13
 */

namespace Framework;


class Redis
{
    private $handler;
    private $config;
    private $connect;

    private function __construct()
    {
        $this->config = $config = app('redis')->get('dns');
    }

    /**
     * @description: 选择 Redis 实例
     *
     * @param $name
     * @return mixed
     */
    public function choose($name)
    {
        return self::instance($name);
    }

    /**
     * @description: 创建handler
     *
     * @throws \Exception
     * @date 2019-06-13
     */
    private function connect()
    {
        $config = $this->connect;
        $client = isset($config['client']) ? $config['client'] : null;
        if ($client == 'predis' && class_exists('\Predis\Client')) {
            $this->handler = new \Predis\Client($config);
            $fd = true;
        } else {
            $this->handler = new \Redis();
            if (isset($config['keep-alive']) && $config['keep-alive']) {
                $fd = $this->handler->pconnect($config['host'], $config['port'], 1800);
            } else {
                $fd = $this->handler->connect($config['host'], $config['port']);
            }
            if ($config["password"]) {
                $this->handler->auth($config["password"]);
            }
        }

        if (!$fd) {
            throw new \Exception('Redis Connect Fail');
        }
    }

    public function get($key, $serialize=null)
    {
        if (!$this->handler){
            $this->connect();
        }
        if ($serialize === null){
            $serialize = $this->config['serialize'];
        }
        return $serialize ? unserialize($this->handler->get($key)) : $this->handler->get($key);
    }

    public function set($key, $value, $timeout=0, $serialize=null)
    {
        if (!$this->handler){
            $this->connect();
        }
        if ($serialize === null){
            $serialize = $this->config['serialize'];
        }
        $value = $serialize ? serialize($value) : $value;
        return $timeout ? $this->handler->set($key, $value, $timeout) : $this->handler->set($key, $value);
    }

    public function hget($key, $hash, $serialize=null)
    {
        if (!$this->handler){
            $this->connect();
        }
        if ($serialize === null){
            $serialize = $this->config['serialize'];
        }
        return $serialize ? unserialize($this->handler->hGet($key, $hash)) : $this->handler->hGet($key, $hash);
    }

    public function hset($key, $hash, $value, $serialize=null)
    {
        if (!$this->handler){
            $this->connect();
        }
        if ($serialize === null){
            $serialize = $this->config['serialize'];
        }
        $value = $serialize ? serialize($value) : $value;
        return $this->handler->hSet($key, $hash, $value);
    }

    public function __call($method, $arguments)
    {
        if (!$this->handler){
            $this->connect();
        }
        return call_user_func_array([$this->handler, $method], $arguments);
    }
}