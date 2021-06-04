<?php

namespace Framework;


class Middleware
{
    protected $queue = [];
    protected $config = [
        'namespace' => 'App\\Http\\Middleware\\',
    ];

    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @description: 注册中间件
     *
     * @param $middleware
     * @date 2019-06-19
     */
    public function add($middleware)
    {
        if (is_array($middleware)) {
            foreach ($middleware as $value) {
                $this->queue[] = $this->config['namespace'] . $value;
            }
        } else {
            $this->queue[] = $this->config['namespace'] . $middleware;
        }
    }

    /**
     * @description: 中间件调度
     *
     * @param Request $request
     * @throws \ErrorException
     * @throws \ReflectionException
     * @date 2019-06-19
     */
    public function dispatch(Request $request)
    {
        foreach ($this->queue as $middleware) {
            $middleware = App::make($middleware);
            $request    = $middleware->handler($request, function () use ($request) {
                return $request;
            });

            if (!$request instanceof Request) {
                throw new \ErrorException('Middleware' . get_class($middleware) . ' Error');
            }
        }
    }
}
