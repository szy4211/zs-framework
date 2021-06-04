<?php
/**
 * @description: 验证类
 *
 * @date 2019-06-17
 * @author zornshuai@foxmail.com
 */

namespace Framework;

use Framework\Validate\ValidateRule;

class Validate
{
    /**
     * 是否批量验证
     * @var bool
     */
    protected $batch = false;
    /**
     * 验证规则
     * @var array
     */
    protected $rules = [];
    /**
     * 验证字段描述
     * @var array
     */
    protected $field = [];
    /**
     * 验证提示信息
     * @var array
     */
    protected $message = [];
    /**
     * 错误信息
     * @var array
     */
    protected $errorMsg = [];
    /**
     * 默认规则
     * @var array
     */
    protected static $defaultMsg = [
        'must'     => ':attribute must',
        'date'     => ':attribute not a valid datetime',
        'bool'     => ':attribute must be bool',
        'number'   => ':attribute must be numeric',
        'alpha'    => ':attribute must be alpha',
        'alphaNum' => ':attribute must be alpha-numeric',
        'array'    => ':attribute must be a array',
        'email'    => ':attribute not a valid email address',
        'mobile'   => ':attribute not a valid mobile',
        'ip'       => ':attribute not a valid ip',
        'integer'  => ':attribute must be integer',
        'float'    => ':attribute must be float',
        'url'      => ':attribute not a valid url',
    ];

    protected $data = [];

    public function __construct(array $rules = [], array $message = [], array $field = [])
    {
        $this->rules   = array_merge($this->rules, $rules);
        $this->message = array_merge($this->message, $message);
        $this->field   = array_merge($this->field, $field);
    }

    /**
     * @description: 验证全部
     *
     * @param bool $flag
     * @return $this
     * @date 2019-06-17
     */
    public function batch($flag = true)
    {
        $this->batch = $flag;

        return $this;
    }

    /**
     * @description: 获取错误信息
     *
     * @param bool $isAll  是否获取所有
     * @return array|bool
     * @date 2019-06-17
     */
    public function getError($isAll = false)
    {
        $message = $this->errorMsg;
        if (!$isAll) {
            $message = current($this->errorMsg) ?? false;
        }
        return $message;
    }

    /**
     * @description: 验证全部
     *
     * @param array $data
     * @return bool
     * @date 2019-06-17
     */
    public function check($data)
    {
        $this->errorMsg = [];

        foreach ($this->rules as $key => $rule) {
            // 字段验证
            $title  = $this->field[$key] ?? $key;
            $result = $this->checkItem($title, $rule, $data);

            if (true !== $result) {
                if (!empty($this->batch)) {
                    // 批量验证
                    if (is_array($result)) {
                        $this->errorMsg = array_merge($this->errorMsg, $result);
                    } else {
                        $this->errorMsg[$key] = $result;
                    }
                } else {
                    $this->errorMsg = $result;
                    return false;
                }
            }
        }

        return empty($this->errorMsg);
    }

    /**
     * @description: 校验单条规则
     *
     * @param string $title
     * @param array $rules
     * @param array $data
     * @return array|true
     * @date 2019-06-17
     */
    public function checkItem($title, $rules, $data)
    {
        $message = [];
        $value  = $data[$title] ?? null;
        //  优先验证必填规则
        $mustValue = 'must';
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        $mustKey = array_search($mustValue, $rules);
        if (false !== $mustKey) {
            $ruleFunName = $mustValue . ValidateRule::RULE_EXT;
            $result = ValidateRule::instance()->{$ruleFunName}($value);
            if (false === $result) {
                $message[$mustKey] = $this->getRuleMsg($title, $mustValue);
            }

            unset($rules[$mustKey]);
        } elseif(!isset($data[$title])) {
            return true;
        }

        foreach ($rules as $key => $rule) {
            $ruleFunName = $rule . ValidateRule::RULE_EXT;
            if (method_exists(ValidateRule::class, $ruleFunName)) {
                $result = ValidateRule::instance()->{$ruleFunName}($value);
            } else {
                $result = true;
            }

            if (false === $result) {
                $message[$rule] = $this->getRuleMsg($title, $rule);
            }
        }

        return !empty($message) ? $message : true;
    }

    /**
     * @description: 获取规则信息
     *
     * @param $title
     * @param $rule
     * @return mixed|string
     * @date 2019-06-17
     */
    protected function getRuleMsg($title, $rule)
    {
        if (isset($this->message[$title . '.' . $rule])) {
            $msg = $this->message[$title . '.' . $rule];
        } elseif (isset($this->message[$title][$rule])) {
            $msg = $this->message[$title][$rule];
        } elseif (isset($this->message[$title])) {
            $msg = $this->message[$title];
        } elseif (isset(self::$defaultMsg[$rule])) {
            $msg = self::$defaultMsg[$rule];
        } else {
            $msg = $title . ' not conform to the rules';
        }

        if (!is_string($msg)) {
            return $msg;
        }

        if (false !== strpos($msg, ':')) {
            $msg = str_replace(':attribute', $title, $msg);

            if (strpos($msg, ':rule')) {
                $msg = str_replace(':rule', $rule, $msg);
            }
        }

        return $msg;
    }
}
