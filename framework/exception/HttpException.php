<?php
/**
 * @description: Http异常
 *
 * @date 2019-06-14
 */

namespace Framework\Exception;


class HttpException extends \RuntimeException
{
    private $statusCode;
    private $headers;

    public function __construct($statusCode = HttpCode::Bad_Request, $message = '', \Exception $previous = null, array $headers = [], $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;

        $message = empty($message) ? HttpCode::getMessage($statusCode) : $message;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}