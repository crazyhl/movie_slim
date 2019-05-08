<?php


namespace App\Validator;

use Slim\Http\Request;

/**
 * 验证器的根类
 * Class AbstractValidator
 * @package App\Validator
 */
abstract class AbstractValidator
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $extraParams;

    /**
     * 保存条件的数组
     * @var array
     * rules 的 key 是验证的变量名称
     * rules 的 value 是个数组
     * value 可包含 type type是声明变量的类型，可包含 string | integer | float | double | array | regex 其他的需要用到的时候在添加
     *       不同的类型会依赖后续不同的参数
     * value 包含 require 这个类型是 true | false, 如果是 true 就是必填项，如果请求参数不包含这个就会返回 false，默认 false
     * value 可包含 message 出错的提示信息，如果出错没有 message 的时候就考大家在 validation 里面自己实现默认的消息了,
     *       如果包含 type 键，这个 message 就是 type 出错的 message，如果没有 type 这个就可以是 require 为 true 的错误消息
     * value 可包含 requireMessage 这个是 require 为 true 时候的出错消息，如果这个没有，就返回默认的信息 'xxx必须填写'
     */
    abstract public function rules();

    public function validation($request, $extraParams)
    {
        $this->request = $request;
        $this->extraParams = $extraParams;

        return $this->doValidation();
    }

    /**
     * 执行验证流程
     */
    public function doValidation()
    {
        /**
         * @var array $rules
         */
        $rules = $this->rules();

        if (!is_array($rules)) {
            return true;
        }

        // 获取请求方法
        $requestMethod = $this->request->getMethod();
        // 获取参数，如果是get就直接获取get参数，如果是其他请求方法
        // 则优先获取post参数，如果不存在在获取get参数，如果依然不存在
        // 就获取 extraParams
        // 每个字段的rules 也改变为数组就ok了
        foreach ($rules as $paramName => $fieldRules) {

            $value = $this->request->getParsedBodyParam($paramName);

            if (strtoupper($requestMethod) !== 'GET') {
                $value = $this->request->getQueryParam($paramName);
            }


            if (array_key_exists($paramName, $this->extraParams)) {
                $value = $this->extraParams[$paramName];
            }

            $fieldType = 'string';
            foreach ($fieldRules as $rule) {
                if ($rule['type'] !== 'require' && empty($value)) {
                    continue;
                }
                switch ($rule['type']) {
                    case 'require':
                        if (empty($value)) {
                            return [false, $rule['message'] ?: $paramName . '必须存在'];
                        }
                        break;
                    case 'string':
                        break;
                    case 'integer':
                        if (!is_numeric($value) || ($value + 0) !== intval($value)) {
                            return [false, $rule['message'] ?: ''];
                        }

                        $value = intval($value);
                        $fieldType = 'integer';
                        break;
                    case 'float':
                    case 'double':
                        if (!is_numeric($value) || ($value + 0) !== floatval($value)) {
                            return [false, $rule['message'] ?: ''];
                        }
                        $value = floatval($value);
                        $fieldType = 'double';
                        break;
                    case 'regex':
                        // 正则
                        if (!preg_match('~' . $rule['pattern'] . '~iu', $value)) {
                            return [false, $rule['message'] ?: ''];
                        }
                        break;
                    case 'array':
                        // 数组
                        if (!is_array($value)) {
                            return [false, $rule['message'] ?: ''];
                        }
                        $fieldType = 'array';
                        break;
                    case 'min':
                        switch ($fieldType) {
                            case 'string':
                                if (mb_strlen($value) < $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'integer':
                            case 'double':
                                if ($value < $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'array':
                                if (count($value) < $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                        }
                        break;
                    case 'max':
                        switch ($fieldType) {
                            case 'string':
                                if (mb_strlen($value) > $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'integer':
                            case 'double':
                                if ($value > $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'array':
                                if (count($value) > $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                        }
                        break;
                    case 'length':
                        switch ($fieldType) {
                            case 'string':
                                if (mb_strlen($value) !== $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'integer':
                            case 'double':
                                if ($value !== $rule['value']) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'array':
                                if (count($value) !== $rule['value']) {
                                    return [false, $rule['message'] . count($value) ?: ''];
                                }
                                break;
                        }
                        break;
                    case 'list':
                        if (!in_array($value, $rule['value'], true)) {
                            return [false, $rule['message'] ?: ''];
                        }
                        break;
                    case 'between':
                        $valueArr = explode(':', $rule['value']);
                        $min = $valueArr[0] ?: null;
                        $max = $valueArr[1] ?: null;
                        switch ($fieldType) {
                            case 'string':
                                if ($min !== null && mb_strlen($value) < $min) {
                                    return [false, $rule['message'] ?: ''];
                                }

                                if ($max !== null && mb_strlen($value) > $max) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'integer':
                            case 'double':
                                if ($min !== null && $value < $min) {
                                    return [false, $rule['message'] ?: ''];
                                }

                                if ($max !== null && $value > $max) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                            case 'array':
                                if ($min !== null && count($value) < $min) {
                                    return [false, $rule['message'] ?: ''];
                                }

                                if ($max !== null && count($value) > $max) {
                                    return [false, $rule['message'] ?: ''];
                                }
                                break;
                        }
                        break;
                }
            }
        }

        return [true, ''];
    }
}
