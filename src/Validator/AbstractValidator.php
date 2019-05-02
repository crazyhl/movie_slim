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
     * 保存条件的数组
     * @var array
     * rules 的 key 是验证的变量名称
     * rules 的 value 是个数组
     * value 可包含 type type是声明变量的类型，可包含 string | number | float | double | array | regex | function 其他的需要用到的时候在添加
     *       不同的类型会依赖后续不同的参数
     * value 包含 require 这个类型是 true | false, 如果是 true 就是必填项，如果请求参数不包含这个就会返回 false，默认 false
     * value 可包含 message 出错的提示信息，如果出错没有 message 的时候就考大家在 validation 里面自己实现默认的消息了,
     *       如果包含 type 键，这个 message 就是 type 出错的 message，如果没有 type 这个就可以是 require 为 true 的错误消息
     * value 可包含 requireMessage 这个是 require 为 true 时候的出错消息，如果这个没有，就返回默认的信息 'xxx必须填写'
     */
    protected $rules = [];

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $extraParams;

    public function __construct(Request $request, array $extraParams = [])
    {
        $this->request = $request;
        $this->extraParams = $extraParams;
    }

    abstract public function validation();

    /**
     * 执行验证流程
     */
    protected function doValidation()
    {
        // 获取请求方法
        $requestMethod = $this->request->getMethod();
        // 获取参数，如果是get就直接获取get参数，如果是其他请求方法
        // 则优先获取post参数，如果不存在在获取get参数，如果依然不存在
        // 就获取 extraParams
        foreach ($this->rules as $paramName => $rule) {
            $falseReturn = [false, $rule['message'] ?: null];

            $value = $this->request->getParsedBodyParam($paramName);

            if (empty($value) && strtoupper($requestMethod) !== 'GET') {
                $value = $this->request->getQueryParam($paramName);
            }

            if ($this->extraParams[$paramName]) {
                $value = $this->extraParams[$paramName];
            }

            if ($rule['require'] == true) {
                return [
                    false,
                    $rule['type']
                        ? ($rule['requireMessage'] ?: $paramName . '必须填写')
                        : ($rule['message'] ?: $paramName . '必须填写')
                ];
            }

            // 如果什么都不存在就返回
            if (empty($value)) {
                continue;
            }

            switch ($rule['type']) {
                case 'string':
                    if (!is_string($value)) {
                        return $falseReturn;
                    }
                    break;
                case 'number':
                    if (!is_numeric($value)) {
                        return $falseReturn;
                    }
                    break;
                case 'float':
                case 'double':
                    if (!is_numeric($value)) {
                        return $falseReturn;
                    }
                    $value = floatval($value);
                    if (!is_float($value)) {
                        return $falseReturn;
                    }
                    break;
                case 'regex':
                    // 正则
                    if (!preg_match('~' . $rule['regex'] . '~iu', $value)) {
                        return $falseReturn;
                    }
                    break;
                case 'function':
                    if (is_callable($rule['function']) && !call_user_func_array($rule['function'], [$value])) {
                        return $falseReturn;
                    }
                    break;
            }
        }

        return true;
    }
}
