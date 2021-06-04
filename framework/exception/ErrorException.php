<?php
/**
 * @description: 异常基类
 *
 * @date 2019-06-14
 */

namespace Framework\Exception;


class ErrorException extends \Exception
{

    /**
     * 用于保存错误级别
     * @var int
     */
    protected $severity;

    /**
     * 错误异常构造函数
     *
     * @param  integer $severity 错误级别
     * @param  string  $message  错误详细信息
     * @param  string  $file     出错文件路径
     * @param  integer $line     出错行号
     */
    public function __construct($message,  $severity = 1, $file = __FILE__, $line = __LINE__)
    {
        $this->severity = $severity;
        $this->message  = $message;
        $this->file     = $file;
        $this->line     = $line;
        $this->code     = 0;
    }

    /**
     * 获取错误级别
     *
     * @return int 错误级别
     */
    final public function getSeverity()
    {
        return $this->severity;
    }
}