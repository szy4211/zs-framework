<?php
/**
 * @description: TODO
 *
 * @date 2019-06-14
 */

namespace Framework\Exception;


use Framework\Service\Response;

class HttpResponseException extends \RuntimeException
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

}