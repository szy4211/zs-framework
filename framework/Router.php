<?php

namespace Framework;


/**
 * @method static Router get(string $route, Callable $callback)
 * @method static Router post(string $route, Callable $callback)
 * @method static Router put(string $route, Callable $callback)
 * @method static Router patch(string $route, Callable $callback)
 * @method static Router delete(string $route, Callable $callback)
 * @method static Router options(string $route, Callable $callback)
 * @method static Router head(string $route, Callable $callback)
 */
class Router
{

    public static $routes = [];

    public static $baseNamespace = '\\';

    public static $prefix = [];

    public static $error_callback;

    /**
     * add filter for your routes
     */
    public static function filter($filter, $result)
    {
        if ($filter()) {
            $result();
        }
    }

    /**
     * Defines a route w/ callback and method
     */
    public static function __callStatic($method, $params)
    {
        $uri = $params[0];
        if ($uri === '') {
            $uri = '/';
        } else if ($uri === '/') {
            // do nothing
        } else {
            if (strpos($uri, '/') === 0) {
                $uri = substr($uri, 1);
            }
        }
        $callback = $params[1];
        if ($method == 'any') {
            self::pushToArray($uri, 'get', $callback);
            self::pushToArray($uri, 'post', $callback);
            self::pushToArray($uri, 'put', $callback);
            self::pushToArray($uri, 'patch', $callback);
            self::pushToArray($uri, 'delete', $callback);
            self::pushToArray($uri, 'options', $callback);
            self::pushToArray($uri, 'head', $callback);
        } else {
            self::pushToArray($uri, $method, $callback);
        }
    }

    /**
     * @description: 注册路由
     *
     * @param $uri
     * @param $method
     * @param $callback
     * @date 2019-06-19
     */
    public static function pushToArray($uri, $method, $callback)
    {
        self::$routes[$uri][$method] = $callback;
    }

    /**
     * @description: 未匹配记录异常
     *
     * @param $callback
     * @date 2019-06-19
     */
    public static function error($callback)
    {
        self::$error_callback = $callback;
    }

    /**
     * @description: 自动加载路由
     *
     * @date 2019-06-19
     */
    public static function loadRouter()
    {
        if (empty(self::$routes)) {
            $routes = glob(ROOT_PATH . DS . 'routes' . DS . '*.php');

            foreach ($routes as $route) {
                require_once $route;
            }
        }
    }

    /**
     * @description: 管理解析
     *
     * @return false|string|null
     * @throws \ReflectionException
     * @date 2019-06-19
     */

    /**
     * @description: 路由调度
     *
     * @return Response|mixed
     * @throws \ReflectionException
     * @date 2019-06-19
     */
    public static function dispatch()
    {
        $uri    = self::detectUri();
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        if (isset(self::$routes[$uri][$method])) {
            $callback = self::$routes[$uri][$method];

            if (!is_object($callback)) {
                $parts      = explode('/', $callback);
                $last       = end($parts);
                $segments   = explode('@', $last);
                $controller = App::make(self::$baseNamespace . $segments[0]);

                $data = App::callMethod($controller, $segments[1]);
            } else {
                $data = App::callFunction($callback);
            }

            if ($data instanceof Response) {
                $response = $data;
            } else {
                $response = Response::create($data);
            }

            return $response;
        }

        if (!self::$error_callback) {
            self::$error_callback = function () {
                throw new \Exception('404 Not Found', 404);
            };
        }
        call_user_func(self::$error_callback);
    }

    /**
     * @description: 解析uri
     *
     * @return string
     * @date 2019-06-19
     */
    private static function detectUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        }

        if ('/' == $uri || empty($uri)) {
            return '/';
        }

        $uri = parse_url($uri, PHP_URL_PATH);
        if (null === $uri) {
            return '/';
        }

        return str_replace(array('//', '../'), '/', trim($uri, '/'));
    }
}