<?php

namespace Framework;

use Framework\Exception\ErrorException;

class Request
{
    /**
     * host 信息
     * @var null
     */
    protected $hostInfo = null;
    /**
     * 请求类型
     * @var string
     */
    protected $method;
    /**
     * 当前SERVER参数
     * @var array
     */
    protected $server = [];
    /**
     * 端口号
     * @var null
     */
    protected $port = null;
    /**
     * 是否为安全连接
     * @var null
     */
    protected $isSecure = null;
    /**
     * 配置
     * @var array
     */
    protected $config = [];
    /**
     * 默认过滤函数
     * @var string
     */
    protected $filter = ['htmlspecialchars'];
    /**
     * 当前请求参数
     * @var array
     */
    protected $param = [];
    /**
     * 当前GET参数
     * @var array
     */
    protected $get = [];
    /**
     * 当前POST参数
     * @var array
     */
    protected $post = [];
    /**
     * 当前REQUEST参数
     * @var array
     */
    protected $request = [];
    /**
     * 当前PUT参数
     * @var array
     */
    protected $put;
    /**
     * php://input内容
     * @var string
     */
    protected $input;
    /**
     * 上传文件信息
     * @var array
     */
    protected $file;
    /**
     * 调度信息
     * @var null
     */
    protected $dispatch = null;

    public function __construct()
    {
        $this->server = $_SERVER;
        $this->request = $_REQUEST;
        $this->input = file_get_contents('php://input');
    }

    /**
     * @description: 设置获取调度信息
     *
     * @param mixed $dispatch
     * @return mixed
     * @date 2019-06-17
     */
    public function dispatch($dispatch = null)
    {
        if (!is_null($dispatch)) {
            $this->dispatch = $dispatch;
        }

        return $this->dispatch;
    }

    /**
     * 获取当前请求的参数
     * @access public
     * @param mixed $name 变量名
     * @param mixed $default 默认值
     * @param bool $isFilter 是否过滤
     * @return mixed
     */
    public function param($name = '', $default = null, $isFilter = true)
    {
        $method = $this->method(true);

        // 自动获取请求变量
        switch ($method) {
            case 'POST':
                $vars = $this->post(false);
                break;
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                $vars = $this->put(false);
                break;
            default:
                $vars = [];
        }

        // 当前请求参数和URL地址中的参数合并
        $this->param = array_merge($this->param, $this->get(false), $vars);

        if (true === $name) {
            // 获取包含文件上传信息的数组
            $file = $this->file();
            $data = is_array($file) ? array_merge($this->param, $file) : $this->param;

            return $this->input($data, '', $default, $isFilter);
        }

        return $this->input($this->param, $name, $default, $isFilter);
    }

    /**
     * 获取变量 支持过滤和默认值
     * @access public
     * @param array $data 数据源
     * @param string|false $name 字段名
     * @param mixed $default 默认值
     * @param bool $isFilter 是否过滤
     * @return mixed
     */
    public function input($data = [], $name = '', $default = null, $isFilter = true)
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }

        $name = (string)$name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            }

            $data = $this->getData($data, $name);

            if (is_null($data)) {
                return $default;
            }

            if (is_object($data)) {
                return $data;
            }
        }
        if ($isFilter) {
            if (is_array($data)) {
                array_walk_recursive($data, [$this, 'filterValue']);
            } else {
                $this->filterValue($data, $name);
            }
        }

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }

        return $data;
    }

    /**
     * 获取GET参数
     * @access public
     * @param string|false $name 变量名
     * @param mixed $default 默认值
     * @param bool $isFilter 是否过滤
     * @return mixed
     */
    public function get($name = '', $default = null, $isFilter = true)
    {
        if (empty($this->get)) {
            $this->get = $_GET;
        }

        return $this->input($this->get, $name, $default, $isFilter);
    }

    /**
     * 获取POST参数
     * @access public
     * @param string|false $name 变量名
     * @param mixed $default 默认值
     * @param bool $isFilter 是否过滤
     * @return mixed
     */
    public function post($name = '', $default = null, $isFilter = true)
    {
        if (empty($this->post)) {
            $this->post = !empty($_POST) ? $_POST : $this->getInputData($this->input);
        }

        return $this->input($this->post, $name, $default, $isFilter);
    }

    /**
     * 获取PUT参数
     * @access public
     * @param string|false $name 变量名
     * @param mixed $default 默认值
     * @param bool $isFilter 是否过滤
     * @return mixed
     */
    public function put($name = '', $default = null, $isFilter = true)
    {
        if (is_null($this->put)) {
            $this->put = $this->getInputData($this->input);
        }

        return $this->input($this->put, $name, $default, $isFilter);
    }

    /**
     * 获取DELETE参数
     * @access public
     * @param string|false $name 变量名
     * @param mixed $default 默认值
     * @param bool $isFilter 是否过滤
     * @return mixed
     */
    public function delete($name = '', $default = null, $isFilter = true)
    {
        return $this->put($name, $default, $isFilter);
    }

    /**
     * 获取PATCH参数
     * @access public
     * @param string|false $name 变量名
     * @param mixed $default 默认值
     * @param bool $isFilter 是否过滤
     * @return mixed
     */
    public function patch($name = '', $default = null, $isFilter = true)
    {
        return $this->put($name, $default, $isFilter);
    }

    /**
     * 获取上传的文件信息
     * @access public
     * @param string $name 名称
     * @return null|array
     */
    public function file($name = '')
    {
        if (empty($this->file)) {
            $this->file = isset($_FILES) ? $_FILES : [];
        }

        if (!empty($name)) {
            return $this->file[$name] ?? null;
        }

        return $this->file;
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getCookie($key = null)
    {
        if ($key) {
            return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
        } else {
            return $_COOKIE;
        }
    }

    /**
     * 设置cookie
     * @param $key
     * @param $value
     * @param int $expire
     * @param string $path
     * @param null $domain
     */
    public function setCookie($key, $value, $expire = 86400, $path = '/', $domain = null)
    {
        if (!session_id()) {
            session_start();
        }

        setcookie($key, $value, time() + $expire, $path, $domain);
    }


    /**
     * 获取header内容
     * @param $key
     * @return null
     */
    public function header($key)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    /**
     * 是否异步请求
     * @return bool
     */
    public function isAjax()
    {
        return $this->header('X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    /**
     * @param bool $query
     * @return mixed|string
     * @throws ErrorException
     */
    public function getUrl($query = true)
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new ErrorException('Get Url Fail ');
        }
        if (!$query && strpos($requestUri, '?')) {
            $requestUri = strstr($requestUri, '?', true);
        }
        return $requestUri;
    }

    /**
     * @return null|string
     */
    public function getHostInfo()
    {
        if ($this->hostInfo === null) {
            $secure = $this->getIsSecureConnection();
            $http   = $secure ? 'https' : 'http';
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            } else {
                $this->hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port           = $this->getPort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                    $this->hostInfo .= ':' . $port;
                }
            }
        }

        return $this->hostInfo;
    }

    /**
     * @return int|null
     */
    public function getPort()
    {
        if ($this->port === null) {
            $this->port = !$this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
        }

        return $this->port;
    }

    /**
     * @return bool|null
     */
    public function getIsSecureConnection()
    {
        if ($this->isSecure === null) {
            $this->isSecure = isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
                || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
        }
        return $this->isSecure;
    }

    /**
     * @return mixed
     */
    public function getServerName()
    {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * @return int
     */
    public function getServerPort()
    {
        return (int)$_SERVER['SERVER_PORT'];
    }

    /**
     * @return null
     */
    public function getReferrer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    /**
     * @return null
     */
    public function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    /**
     * 获取ServerIP
     * @return string
     */
    public function getServerIP()
    {
        return $_SERVER['SERVER_ADDR'] ?: gethostbyname($_SERVER['SERVER_NAME']);
    }

    /**
     * 获取ip
     * @return null
     */
    public function getUserIP()
    {
        if (isset($this->config['userIP']) && $this->config['userIP']) {
            $userIP = $this->header($this->config['userIP']);
            return $userIP ?: (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
        } else {
            return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        }
    }

    /**
     * 通关ua判断是否为手机
     * @return bool
     */
    public function isMobile()
    {
        //正则表达式,批配不同手机浏览器UA关键词。
        $regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
        $regex_match .= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
        $regex_match .= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
        $regex_match .= "symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
        $regex_match .= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320×320|240×320|176×220";
        $regex_match .= "|mqqbrowser|juc|iuc|ios|ipad";
        $regex_match .= ")/i";

        return isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']) or preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']));
    }

    /**
     * 获取ContentType
     * @return mixed
     */
    public function getContentType()
    {
        return $_SERVER['CONTENT_TYPE'];
    }

    //设置默认编码
    public function setContentType($contentType = 'text/html', $charset = 'utf-8')
    {
        header('Content-type: ' . $contentType . '; charset=' . $charset);
    }

    public function redirect($url)
    {
        header("Location:$url");
        exit();
    }

    /**
     * 当前的请求类型
     * @access public
     * @param  bool $origin  是否获取原始请求类型
     * @return string
     */
    public function method($origin = false)
    {
        if ($origin) {
            // 获取原始请求类型
            return $this->server('REQUEST_METHOD') ?: 'GET';
        } elseif (!$this->method) {
            if (isset($_POST[$this->config['var_method']])) {
                $method = strtolower($_POST[$this->config['var_method']]);
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    $this->method    = strtoupper($method);
                    $this->{$method} = $_POST;
                } else {
                    $this->method = 'POST';
                }
                unset($_POST[$this->config['var_method']]);
            } elseif ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            } else {
                $this->method = $this->server('REQUEST_METHOD') ?: 'GET';
            }
        }

        return $this->method;
    }

    /**
     * 获取server参数
     * @access public
     * @param  string        $name 数据名称
     * @param  string        $default 默认值
     * @return mixed
     */
    public function server($name = '', $default = null)
    {
        if (empty($name)) {
            return $this->server;
        } else {
            $name = strtoupper($name);
        }

        return isset($this->server[$name]) ? $this->server[$name] : $default;
    }

    protected function getInputData($content)
    {
        if (false !== strpos($this->getContentType(), 'application/json') || 0 === strpos($content, '{"')) {
            return (array)json_decode($content, true);
        } elseif (strpos($content, '=')) {
            parse_str($content, $data);
            return $data;
        }

        return [];
    }

    /**
     * 获取数据
     * @access public
     * @param array $data 数据源
     * @param string|false $name 字段名
     * @return mixed
     */
    protected function getData(array $data, $name)
    {
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                return [];
            }
        }

        return $data;
    }

    /**
     * 强制类型转换
     * @access public
     * @param string $data
     * @param string $type
     * @return mixed
     */
    private function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array)$data;
                break;
            // 数字
            case 'd':
                $data = (int)$data;
                break;
            // 浮点
            case 'f':
                $data = (float)$data;
                break;
            // 布尔
            case 'b':
                $data = (boolean)$data;
                break;
            // 字符串
            case 's':
                if (is_scalar($data)) {
                    $data = (string)$data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
                break;
        }
    }

    /**
     * 递归过滤给定的值
     * @access public
     * @param mixed $value 键值
     * @param mixed $key 键名
     * @return mixed
     */
    private function filterValue(&$value, $key)
    {
        foreach ($this->filter as $filter) {
            if (function_exists($filter)) {
                $value = $filter($value);
            }
        }

        return $value;
    }
}