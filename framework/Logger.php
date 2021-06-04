<?php

namespace Framework;

class Logger
{
    const INFO = 100;
    const DEBUG = 200;
    const NOTICE = 300;
    const WARNING = 400;
    const ERROR = 500;

    private static $instance = null;
    private static $config = [];

    private static $LEVELS = [
        self::INFO    => 'INFO',
        self::DEBUG   => 'DEBUG',
        self::NOTICE  => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR   => 'ERROR',
    ];

    private function __construct()
    {

    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::loadConfig();
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static $ConsoleOut = [];

    /**
     * 计算内存消耗
     * @param $size
     * @return string
     */
    private static function convert($size)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    /**
     * 事件触发写sql
     * @param $e
     * @param $sql
     */
    public function event($e, $sql)
    {
        $this->addLog($sql);
        $this->logger($sql, $e, "info");
    }

    /**
     * @param $message
     * @param $key
     * @param string $level
     */
    protected function logger($message, $key, $level = "info")
    {
        self::$ConsoleOut[] = ['value' => $message, 'key' => $key, 'type' => $level];
    }

    public static function log($message, $key = "phpLogs")
    {
        self::instance()->logger($message, $key, "log");
    }

    public static function memory($key = "memory")
    {
        self::instance()->logger(self::convert(memory_get_usage()), $key, "warn");
    }

    public static function time($key = "time")
    {
        self::instance()->logger(microtime(true), $key, "warn");
    }

    public static function info($message, $key = "phpLogs")
    {
        self::instance()->logger($message, $key, "info");
    }

    public static function warn($message, $key = "phpLogs")
    {
        self::instance()->logger($message, $key, "warn");
    }

    public static function error($message, $key = "phpLogs")
    {
        self::instance()->logger($message, $key, "error");
    }

    public static function display($message)
    {
        echo '<pre>';
        $message = (is_object($message) && method_exists($message, '__toLogger')) ? $message->__toLogger() : $message;
        print_r($message !== NULL && $message !== '' ? $message : "NULL");
        echo '</pre>';
    }

    /**
     *
     * 获取实例
     * @param $obj
     * @return array
     */
    private static function object_to_array($obj)
    {
        $arr        = [];
        $class      = new \ReflectionClass($obj);
        $properties = $class->getProperties();
        foreach ($properties as $propertie) {
            $value                      = $propertie->isPrivate() ? ":private" :
                ($propertie->isProtected() ? ":protected" :
                    ($propertie->isPublic() ? ":public" : ""));
            $arr[$propertie->getName()] = $value;
        }
        return [$class->getName() => $arr];
    }

    /**
     * 格式化输出项
     */
    public static function format()
    {
        foreach (self::$ConsoleOut as &$Out) {
            $value = $Out['value'];
            if (is_object($value)) {
                if (method_exists($value, '__toLogger')) {
                    $value = $value->__toLogger();
                } else {
                    $value = self::object_to_array($value);
                }
            } elseif ($value === null) {
                $value = 'NULL';
            } else if (is_bool($value)) {
                $value = $value ? "true" : "false";
            }
            $Out['value'] = $value;
        }
        unset($Out);
    }


    /**
     * 返回所有日志
     */
    public static function showLogs()
    {
        if (self::$ConsoleOut) {
            self::format();

            self::$ConsoleOut = [];
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if (App::$base->request && !App::$base->request->isAjax()) {
            self::showLogs();
        }
    }

    public static function getLevel($errorStr)
    {
        $level = self::ERROR;
        foreach (self::$LEVELS as $key => $value) {
            if ($errorStr == $value) {
                $level = $key;
            }
        }

        return $level;
    }

    /**
     * @description: 记录错误日志
     *
     * @param $message
     * @param string $key
     * @param int $level
     *
     * @date 2019-06-14
     */
    public static function addError($message, $key = '', $level = self::ERROR)
    {
        self::loadConfig();
        $errorLevel   = self::getLevel(strtoupper(self::$config['level']));

        if ($errorLevel > $level) {
            return;
        }

        self::addLog($message, $key, $level);

    }

    /**
     * 记录日志
     * @param $message
     * @param $key
     * @param int $level
     */
    public static function addLog($message, $key = '', $level = self::INFO)
    {
        self::loadConfig();
        // 自定义日志方法
        if (isset(self::$config['sendLog']) && is_callable(self::$config['sendLog'])) {
            call_user_func_array(self::$config['sendLog'], [$message, $key, isset(self::$LEVELS[$level]) ? self::$LEVELS[$level] : 'INFO']);
        }
        // 记录文件日志
        if (self::$config['files']) {
            if (is_array($message) || is_object($message)) {
                $message = var_export($message, true);
            }
            $header   = sprintf("[%s]%s:%s [%s] %s", isset(self::$LEVELS[$level]) ? self::$LEVELS[$level] : 'INFO',
                date('Y-m-d H:i:s'), substr(microtime(), 2, 3), App::$base->request->getUserIp(),
                $key ? "$key => " : '');
            $message  = "$header$message\n\n\n";
            $logDir = self::$config['root_path'] . '/' . date('Y/m');
            $filename = sprintf("%s/log_%s.log", $logDir , date('d'));

            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0775, true);
            }

            file_put_contents($filename, $message, FILE_APPEND | LOCK_EX);
        }
    }

    private static function loadConfig()
    {
        if (empty(self::$config)) {
            self::$config = App::$base->config->pull('logger');
            self::$config['root_path'] = !empty(self::$config['root_path']) ? self::$config['root_path'] : ROOT_PATH . DS . 'logs';
        }
    }
}