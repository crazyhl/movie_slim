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
            return [true, ''];
        }

        // 获取请求方法
        $requestMethod = $this->request->getMethod();
        // 获取参数，如果是get就直接获取get参数，如果是其他请求方法
        // 则优先获取post参数，如果不存在在获取get参数，如果依然不存在
        // 就获取 extraParams
        // 每个字段的rules 也改变为数组就ok了
        foreach ($rules as $fieldName => $fieldRule) {
            $value = null;
            $getValue = $this->request->getQueryParam($fieldName, null);

            if ($getValue !== null) {
                $value = $getValue;
            }

            if (strtoupper($requestMethod) !== 'GET') {
                $postValue = $this->request->getParsedBodyParam($fieldName, null);
                if ($postValue !== null) {
                    $value = $postValue;
                }
            }


            if (array_key_exists($fieldName, $this->extraParams)) {
                $value = $this->extraParams[$fieldName];
            }
            $messageFieldName = $fieldRule['name'] ?: $fieldName;

            $fieldType = 'string';

            $isRequire = false;

            if ($fieldRule['require']) {
                $isRequire = $fieldRule['require'];
            }

            // 必填项
            if ($isRequire && (null === $value || mb_strlen($value) == 0)) {
                return [false, $fieldRule['requireMessage'] ?: $messageFieldName . '必须存在'];
            }

            // 如果设定了类型，则选用自定义的类型
            if ($fieldRule['type']) {
                $fieldType = $fieldRule['type'];
            }

            // 定义更多限定的方法名称，方便后面一步限定
            $minFunction = '';
            $maxFunction = '';
            $lengthFunction = '';
            $betweenFunction = '';
            $listFunction = 'in_array';
            $typeErrorMessagePrefix = '';

            switch ($fieldType) {
                case 'string':
                    // 字符串
                    $minFunction = 'mb_strlen';
                    $maxFunction = 'mb_strlen';
                    $lengthFunction = 'mb_strlen';
                    $betweenFunction = 'mb_strlen';
                    $typeErrorMessagePrefix = '长度';
                    break;
                case 'integer':
                    // 数字
                    if (!is_numeric($value) || ($value + 0) !== intval($value)) {
                        return [false, $fieldRule['message'] ?: $messageFieldName . '必须是数字'];
                    }
                    $value = intval($value);
                    break;
                case 'float':
                case 'double':
                    if (!is_numeric($value) || ($value + 0) !== floatval($value)) {
                        return [false, $fieldRule['message'] ?: $messageFieldName . '必须是浮点数'];
                    }
                    $value = floatval($value);
                    break;
                case 'regex':
                    if ($fieldRule['pattern'] && !preg_match('~' . $fieldRule['pattern'] . '~iu', $value)) {
                        return [false, $fieldRule['message'] ?: $messageFieldName . '格式不正确'];
                    }
                    $typeErrorMessagePrefix = '长度';
                    break;
                case 'array':
                    if (!is_array($value)) {
                        return [false, $fieldRule['message'] ?: $messageFieldName . '格式不正确'];
                    }
                    $minFunction = 'count';
                    $maxFunction = 'count';
                    $lengthFunction = 'count';
                    $betweenFunction = 'count';
                    $typeErrorMessagePrefix = '数量';
                    break;
            }

            if (array_key_exists('min', $fieldRule)) {
                if (($minFunction && $minFunction($value) < $fieldRule['min']) || $value < $fieldRule['min']) {
                    return [false, $fieldRule[$fieldType . 'Message'] ?: $messageFieldName . $typeErrorMessagePrefix . '不能小于' . $fieldRule['min']];
                }
            }

            if (array_key_exists('max', $fieldRule)) {
                if (($maxFunction && $maxFunction($value) > $fieldRule['max']) || $value > $fieldRule['max']) {
                    return [false, $fieldRule[$fieldType . 'Message'] ?: $messageFieldName . $typeErrorMessagePrefix . '不能大于' . $fieldRule['min']];
                }
            }

            if (array_key_exists('length', $fieldRule)) {
                if (($lengthFunction && $lengthFunction($value) !== $fieldRule['length']) || $value !== $fieldRule['length']) {
                    return [false, $fieldRule[$fieldType . 'Message'] ?: $messageFieldName . $typeErrorMessagePrefix . '不正确'];
                }
            }

            if (array_key_exists('list', $fieldRule)) {
                if (!$listFunction($value, $fieldRule['list'], true)) {
                    return [false, $fieldRule[$fieldType . 'Message'] ?: $messageFieldName . '范围不正确'];
                }
            }

            if (array_key_exists('between', $fieldRule)) {
                $valueArr = explode(':', $fieldRule['between']);
                $min = $valueArr[0] ?: null;
                $max = $valueArr[1] ?: null;

                if ($min !== null && (($betweenFunction && $betweenFunction($value) < $fieldRule['min']) || $value < $fieldRule['min'])) {
                    return [false, $fieldRule[$fieldType . 'Message'] ?: $messageFieldName . $typeErrorMessagePrefix . '不能小于' . $fieldRule['min']];
                }

                if ($max !== null && (($betweenFunction && $betweenFunction($value) > $fieldRule['max']) || $value > $fieldRule['max'])) {
                    return [false, $fieldRule[$fieldType . 'Message'] ?: $messageFieldName . $typeErrorMessagePrefix . '不能大于' . $fieldRule['min']];
                }
            }
        }

        return [true, ''];
    }
}
