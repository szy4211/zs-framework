<?php
/**
 * @description: 配置
 *
 * @date 2019-06-13
 */

namespace Framework;


class Config
{
    private static $cfgCaches = [];
    private $cfgExt = '.php';

    /**
     * @description: 获取配置
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     * @date 2019-06-13
     */
    public function get($name, $default = null)
    {
        $name = explode('.', strtolower($name));

        if (!isset($name[1])) {
            $module = 'app';
        } else {
            $module = $name[0];
        }
        $config = $this->load($module);

        if (!isset($name[1])) {
            $config = $config[$name[0]] ?? $default;
        } elseif (!empty($name[1])) {
            $config = $config[$name[1]] ?? $default;
        }

        return $config;
    }

    /**
     * @description: 获取整个配置
     *
     * @param $module
     * @return mixed
     * @date 2019-06-13
     */
    public function pull($module)
    {
        return $this->load($module);
    }

    /**
     * @description: 加载配置文件
     *
     * @param $module
     * @return mixed
     */
    private function load($module)
    {
        if (!isset(self::$cfgCaches[$module])) {
            $basePath = ROOT_PATH . DS . 'config' . DS;
            $path     = $basePath . $module . $this->cfgExt;

            $envModule = $module . (ENV_DEV ? '_dev' : (ENV_PRE ? '_pre' : (ENV_PUB ? '_pub' : '')));
            $envPath   = $basePath . $envModule . $this->cfgExt;
            if (is_readable($path) || is_readable($envPath)) {
                $config                   = is_readable($path) ? require($path) : [];
                $config                   = is_readable($envPath) ? array_merge($config, require($envPath)) : $config;
                self::$cfgCaches[$module] = $config;
            } else {
                self::$cfgCaches[$module] = [];
            }
        }

        return self::$cfgCaches[$module];
    }
}