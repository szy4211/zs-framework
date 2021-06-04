<?php

namespace Framework\View;

class View
{

    protected $view;
    protected $data;
    protected $isJson;

    public function __construct($view, $isJson = false)
    {
        $this->view   = $view;
        $this->isJson = $isJson;
    }

    public static function make($viewName = null)
    {
        if (!defined('VIEW_BASE_PATH')) {
            throw new \InvalidArgumentException("VIEW_BASE_PATH is undefined!");
        }
        if (!$viewName) {
            throw new \InvalidArgumentException("View name can not be empty!");
        } else {

            $viewFilePath = self::getFilePath($viewName);
            if (is_file($viewFilePath)) {
                return new View($viewFilePath);
            } else {
                throw new \UnexpectedValueException("View file does not exist!");
            }
        }
    }

    public static function json($arr)
    {
        if (!is_array($arr)) {
            throw new \UnexpectedValueException("View::json can only recieve Array!");
        } else {
            return new View($arr, true);
        }
    }

    /**
     * @description: 解析
     *
     * @param View $view
     * @return false|string|null
     * @date 2019-06-14
     */
    public static function process($view)
    {
        if ($view->isJson) {
            $data = json_encode($view->view);
        } else {
            ob_start();
            if ($view->data) {
                extract($view->data);
            }
            require $view->view;

            $data = ob_get_clean();
        }

        return $data;
    }

    public function with($key, $value = null)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function data()
    {
        if ($this->isJson) {
            return $this->view;
        }

        return $this->data;
    }

    private static function getFilePath($viewName)
    {
        $filePath = str_replace('.', '/', $viewName);
        return VIEW_BASE_PATH . $filePath . '.php';
    }
}