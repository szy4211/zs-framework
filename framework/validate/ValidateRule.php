<?php
/**
 * @description: 验证规则类
 *
 * @date 2019-06-17
 * @author zornshuai@foxmail.com
 */

namespace Framework\Validate;

class ValidateRule
{
    const RULE_EXT = 'Rule';

    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    //  必填
    public function mustRule($str)
    {
        return !empty($str) || '0' == $str;
    }

    //  日期
    public function dateRule($str)
    {
        return false !== strtotime($str);
    }

    //  布尔值
    public function boolRule($str)
    {
        return in_array($str, [true, false, 0, 1, '0', '1'], true);
    }

    //  数组
    public function numberRule($str)
    {
        return ctype_digit((string) $str);
    }

    //  字母
    public function alphaRule($str) {
        return (bool)preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u', $str);
    }

    //  字母+数字
    public function alphaNumRule($str) {
        return ctype_alnum($str);
    }

    //  数组
    public function arrayRule($str)
    {
        return is_array($str);
    }

    //  验证邮箱
    public function emailRule($str)
    {
        return $this->filter($str, FILTER_VALIDATE_EMAIL);
    }

    //  手机号
    public function mobileRule($str)
    {
        return (bool)preg_match('/^1[3-9][0-9]\d{8}$/', $str);
    }

    //  验证ip
    public function ipRule($str)
    {
        return $this->filter($str, [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6]);
    }

    //  验证整数
    public function integerRule($str)
    {
        return $this->filter($str, FILTER_VALIDATE_INT);
    }

    //  验证浮点数
    public function floatRule($str)
    {
        return $this->filter($str, FILTER_VALIDATE_FLOAT);
    }

    //  验证url地址
    public function urlRule($str)
    {
        return $this->filter($str, FILTER_VALIDATE_URL);
    }

    //  php内置验证规则
    private function filter($str, $rule)
    {
        return false !== filter_var($str, is_int($rule) ? $rule : filter_id($rule));
    }
}
