<?php
/**
 * @description: App异常基类
 *
 * @author zornshuai@foxmail.com
 * @version V1.0
 * @date 2019/4/15
 */

namespace Framework\exception;

use Throwable;

class FrameworkException extends \Exception
{
    protected $headers = [];

    protected $show_code = false;

    public function __construct(string $message = "", int $code = HttpCode::Bad_Request, Throwable $previous = null)
    {
        empty($message) && $message = HttpCode::getMessage($code);

        parent::__construct($message, $code, $previous);
    }

    /**
     * @description: 设置header
     *
     * @param array $headers
     * @return $this
     * @author zornshuai@foxmail.com
     * @version 1.0
     * @date 2019/5/8
     */
    public function withHeaders($headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function showCode($show_code = null)
    {
        if (!is_null($show_code)) {
            $this->show_code = (bool)$show_code;
        }

        return $this->show_code;
    }


}