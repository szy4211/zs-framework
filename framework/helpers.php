<?php
/**
 * @description: 快捷函数
 *
 * @date 2019-06-13
 */

if (!function_exists('dd')) {
    /**
     * @description: 仅做调试使用
     *
     * @param mixed ...$args
     * @date 2019-06-17
     */
    function dd(...$args)
    {
        echo '<pre>';
        var_dump(...$args);
        die(1);
    }
}

if (!function_exists('app')) {
    function app($name)
    {
        return \Framework\App::$base->$name;
    }
}

if (!function_exists('request')) {
    function request()
    {
        return \Framework\App::$base->request;
    }
}

if (!function_exists('response')) {
    function response($data = '', $code = 200, $header = [], $type = 'json')
    {
        return \Framework\App::$base->response::create($data, $type, $code, $header);
    }
}

if (!function_exists('config')) {
    function config($name, $default = null)
    {
        return \Framework\App::$base->config->get($name, $default);
    }
}