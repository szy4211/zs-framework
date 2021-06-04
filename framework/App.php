<?php
/**
 * @description: 核心
 *
 * @date 2019-06-13
 */

namespace Framework;


use Framework\Exception\ErrorException;
use Framework\Exception\ThrowableError;


/**
 * Framework App核心
 * @property Config $config
 * @property Request $request
 * @property Response $response
 * @property Router $router
 * @property Cache $cache
 * @property Redis $redis
 * @property Middleware $middleware
 */
class App
{
    /**
     * @var App
     */
    public static $base;

    protected static $instances = [];

    protected static $lazyLoader = [
        'config'     => Config::class,
        'request'    => Request::class,
        'response'   => Response::class,
        'redis'      => Redis::class,
        'router'     => Router::class,
        'middleware' => Middleware::class,
    ];

    private static function init()
    {
        self::$base = new self();
        self::define();

        date_default_timezone_set(self::$base->config->get('timezone', 'RPC'));

        set_error_handler([__CLASS__, 'handleError']);
        set_exception_handler([__CLASS__, 'handleException']);
        register_shutdown_function([__CLASS__, 'handleShutdown']);

        //注册路由对应控制器
        Router::$baseNamespace = '\\App\\Controllers\\';
        Router::loadRouter();
    }

    /**
     * 初始化定义
     */
    private static function define()
    {
        defined('ROOT_PATH') or define('ROOT_PATH', dirname(dirname(__FILE__)));
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        //定义保护
        defined('SYS_DEBUG') or define('SYS_DEBUG', false);
        defined('SYS_CONSOLE') or define('SYS_CONSOLE', false);
        defined('isMaintenance') or define('isMaintenance', false);

        defined('ENV_DEV') or define('ENV_DEV', SYS_ENV === 'dev');
        defined('ENV_PRE') or define('ENV_PRE', SYS_ENV === 'pre');
        defined('ENV_PUB') or define('ENV_PUB', SYS_ENV === 'pub');

        //定义模板存储位置
        defined('VIEW_BASE_PATH') or define('VIEW_BASE_PATH', ROOT_PATH . DS . 'app' . DS . 'views' . DS);
    }

    public function run()
    {
        self::init();

        $request = self::$base->request;
        $this->middleware->dispatch($request);

        return Router::dispatch($request);
    }

    public static function handleException($e)
    {
        if (!$e instanceof \Exception) {
            $e = new ThrowableError($e);
        }

        $exceptionHandler = self::$base->config->get('exception_handler', '\\Framework\\Exception\\Handle');
        $handle           = new $exceptionHandler();

        return $handle->render($e)->send();
    }

    public static function handleError($code, $message, $file, $line)
    {
        throw new ErrorException($message, $code, $file, $line);
    }

    public static function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && self::isFatal($error['type'])) {
            // 将错误信息托管至ErrorException
            $exception = new ErrorException($error['message'], $error['type'], $error['file'], $error['line']);

            self::handleException($exception);
        }
    }

    /**
     * 获取单例全局量
     * @param $name
     * @return mixed
     * @throws ErrorException
     */
    public function __get($name)
    {
        return self::lazyLoader($name);
    }

    protected static function lazyLoader($name)
    {
        if (isset(self::$lazyLoader[$name])) {
            if (!isset(self::$instances[$name])) {
                self::$instances[$name] = self::make(self::$lazyLoader[$name]);
            }

            return self::$instances[$name];
        }

        throw new ErrorException('Not Method ');
    }

    /**
     * @description: 使用反射构建对象
     *
     * @param $className
     * @return object
     * @throws \ReflectionException
     * @date 2019-06-19
     */
    public static function make($className)
    {
        // 通过反射获得该类
        $reflect = new \ReflectionClass($className);

        $constructor = $reflect->getConstructor();
        if (!$constructor) {
            $object = new $className;
        } else {
            $params = $constructor->getParameters();
            if (empty($params)) {
                $object = new $className;
            } else {
                $instanceArgs = [];

                foreach ($params as $param) {
                    $argsClass = $param->getClass();
                    if ($param->isDefaultValueAvailable()) {
                        $instanceArgs[] = $param->getDefaultValue();
                    } else {
                        if (is_object($argsClass)) {
                            $instanceArgs[] = self::make($argsClass->getName());
                        } else {
                            $instanceArgs[] = null;
                        }
                    }
                }

                $object = $reflect->newInstanceArgs($instanceArgs);
            }
        }

        return $object;
    }

    /**
     * @description: 反射调用函数
     *
     * @param $methodName
     * @return mixed
     * @throws \ReflectionException
     * @date 2019-06-19
     */
    public function callFunction($methodName)
    {
        $method = new \ReflectionFunction($methodName);

        $params       = $method->getParameters();
        $instanceArgs = [];
        if (!empty($params)) {
            foreach ($params as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $instanceArgs[] = $param->getDefaultValue();
                } else {
                    $argsClass = $param->getClass();
                    if (is_object($argsClass)) {
                        $instanceArgs[] = self::make($argsClass->getName());
                    } else {
                        $instanceArgs[] = null;
                    }
                }
            }
        }

        return $method->invokeArgs($instanceArgs);
    }

    /**
     * @description: 反射调用方法
     *
     * @param object $object
     * @param $methodName
     * @return mixed
     * @throws \ReflectionException
     * @date 2019-06-19
     */
    public static function callMethod($object, $methodName)
    {
        $method = new \ReflectionMethod($object, $methodName);

        $params       = $method->getParameters();
        $instanceArgs = [];
        if (!empty($params)) {
            foreach ($params as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $instanceArgs[] = $param->getDefaultValue();
                } else {
                    $argsClass = $param->getClass();
                    if (is_object($argsClass)) {
                        $instanceArgs[] = self::make($argsClass->getName());
                    } else {
                        $instanceArgs[] = null;
                    }
                }
            }
        }

        return $method->invokeArgs($object, $instanceArgs);
    }

    protected static function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}