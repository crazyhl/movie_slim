<?php


namespace App\Validator;


class AddMenuValidator extends AbstractValidator
{

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
    public function rules()
    {
        // TODO: Implement rules() method.
        return [
            'name' => [
                'name' => '名称',
                'require' => true,
                'requireMessage' => '名称不能为空',
            ],
            'slug' => [
                'name' => '别名',
                'require' => true,
                'requireMessage' => '别名不能为空',
            ],
            'url' => [
                'name' => 'url',
                'require' => true,
                'requireMessage' => 'url不能为空',
            ],
            'order' => [
                'name' => '排序',
                'require' => true,
                'requireMessage' => '排序不能为空',
                'type' => 'integer',
                'message' => '排序必须是数字',
            ],
            'parentId' => [
                'name' => '父菜单',
                'require' => true,
                'requireMessage' => '父菜单必须选择',
                'type' => 'integer',
                'message' => '父菜单必须是个数字',
            ],
            'is_open' => [
                'name' => '开启',
                'require' => true,
                'requireMessage' => '必须选择开启',
                'type' => 'integer',
                'message' => '开启必须是个数字',
                'list' => [0,1],
                'listMessage' => '开启范围不正确'
            ],
            'is_show' => [
                'name' => '显示',
                'require' => true,
                'requireMessage' => '必须选择显示',
                'type' => 'integer',
                'message' => '显示必须是个数字',
                'list' => [0,1],
                'listMessage' => '显示范围不正确'
            ],
            'position' => [
                'name' => '位置',
                'require' => true,
                'requireMessage' => '必须选择位置',
                'type' => 'integer',
                'message' => '位置必须是个数字',
                'list' => [0,1],
                'listMessage' => '位置范围不正确'
            ],
        ];
    }
}
