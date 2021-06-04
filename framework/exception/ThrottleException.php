<?php
/**
 * @description: 接口限制异常
 *
 * @author zornshuai@foxmail.com
 * @version V1.0
 * @date 2019/5/8
 */

namespace Framework\Exception;


class ThrottleException extends FrameworkException
{
    protected $show_code = true;

    public function __construct($headers = [])
    {
        $this->headers = $headers;
        $code          = HttpCode::Request_Overrun;
        $message       = HttpCode::getMessage($code);

        parent::__construct($message, $code);
    }
}