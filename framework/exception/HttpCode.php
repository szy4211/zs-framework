<?php
/**
 * @description: HTTP返回码
 *
 * @author zornshuai@foxmail.com
 * @version V1.0
 * @date 2019/4/17
 */

namespace Framework\Exception;


use Framework\App;

class HttpCode
{
    const OK = 200;
    const Created = 201;

    const Moved = 301;
    const Found = 302;

    const Bad_Request = 400;
    const Unauthorized = 401;
    const Forbidden = 403;
    const Not_Found = 404;
    const Unprocessable_Entity = 422;
    const Request_Overrun = 429;

    const Server_Error = 500;
    const Bad_Gateway = 502;

    //  英文消息（默认）
    static private $en_us_msg = [
        self::OK                   => 'OK',
        self::Created              => 'Created',
        self::Moved                => 'Moved Permanently',
        self::Found                => 'Found',
        self::Bad_Request          => 'Bad Request',
        self::Unauthorized         => 'Unauthorized',
        self::Forbidden            => 'Forbidden',
        self::Not_Found            => 'Not Found',
        self::Unprocessable_Entity => 'Unprocessable Entity',
        self::Request_Overrun      => 'Too Many Attempts',
        self::Server_Error         => 'Internal Server Error',
        self::Bad_Gateway          => 'Bad Gateway',
    ];

    //  繁体消息
    static private $zh_hk_msg = [
        self::OK                   => '成功',
        self::Created              => '創建成功',
        self::Moved                => '永久移動',
        self::Found                => '臨時移動',
        self::Bad_Request          => '錯誤的請求',
        self::Unauthorized         => '未登錄',
        self::Forbidden            => '禁止訪問',
        self::Not_Found            => '未找到',
        self::Unprocessable_Entity => '參數錯誤',
        self::Request_Overrun      => '請求超限',
        self::Server_Error         => '服務器內部錯誤',
        self::Bad_Gateway          => '錯誤的網關',
    ];

    //  简体消息
    static private $zh_cn_msg = [
        self::OK                   => '成功',
        self::Created              => '创建成功',
        self::Moved                => '永久移动',
        self::Found                => '临时移动',
        self::Bad_Request          => '错误的请求',
        self::Unauthorized         => '未登录',
        self::Forbidden            => '禁止访问',
        self::Not_Found            => '未找到',
        self::Unprocessable_Entity => '参数错误',
        self::Request_Overrun      => '请求超限',
        self::Server_Error         => '服务器内部错误',
        self::Bad_Gateway          => '错误的网关',
    ];

    /**
     * @description: 获取http消息
     *
     * @param int $msg_code
     * @return mixed
     * @throws \Framework\Exception\ErrorException
     * @date 2019-06-14
     */
    static public function getMessage(int $msg_code = self::OK)
    {
        $msg_array = self::getMessageByLanguage();

        return $msg_array[$msg_code] ?? $msg_array[self::Server_Error];
    }

    /**
     * @description: 根据语言设置获取对应的消息
     *
     * @return array
     * @date 2019-06-14
     */
    static private function getMessageByLanguage()
    {
        $lang = App::$base->config->get('lang');

        switch ($lang) {
            case 'zh-hk':
                $msg_array = self::$zh_hk_msg;
                break;
            case 'zh-cn':
                $msg_array = self::$zh_cn_msg;
                break;
            default:
                $msg_array = self::$en_us_msg;
                break;
        }

        return $msg_array;
    }

}