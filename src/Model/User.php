<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-05
 * Time: 18:36
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    // hidden 的字段在返回的时候会被过滤
    protected $hidden = ['password'];

    /**
     * 设置用户密码的时候进行加密
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * 用户角色
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }
}
