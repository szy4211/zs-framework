<?php
/**
 * @description: 异常处理
 *
 * @date 2019-06-14
 */

namespace Framework\Exception;


use Framework\Logger;
use Framework\Response;

class Handle
{
    public function render(\Exception $e)
    {

        if ($e instanceof HttpException) {
            return $this->renderHttpException($e);
        }

        if ($e instanceof ErrorException) {
            return $this->renderErrorException($e);
        }

        return $this->rendException($e);
    }

    protected function renderErrorException(ErrorException $e)
    {
        return $this->rendException($e);
    }

    final private function rendException(\Exception $e)
    {
        $code    = $e->getCode();

        $logMessage = sprintf("%s\n#1 %s(%s)", $e->getTraceAsString(), $e->getFile(), $line    = $e->getLine());

        if ($code === E_WARNING || $code === E_NOTICE) {
            Logger::addLog($logMessage, 'WARNING', Logger::WARNING);
        } elseif (error_reporting() & $code) {
            Logger::addError($logMessage, 'ERROR');
        } else {
            Logger::addLog($logMessage, 'NOTICE', Logger::NOTICE);
        }

        if (SYS_DEBUG) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->handleException($e);
            $whoops->register();
        } else {
            return Response::create(HttpCode::getMessage(HttpCode::Server_Error), 'json', HttpCode::Server_Error);
        }
    }

    /**
     * @description: http异常
     *
     * @param HttpException $e
     * @return \Framework\Service\Response
     * @date 2019-06-14
     */
    protected function renderHttpException(HttpException $e)
    {
        return Response::create($e->getMessage(), 'json', $e->getStatusCode());
    }
}
