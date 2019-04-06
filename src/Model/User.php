<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-05
 * Time: 18:36
 */

namespace App\Model;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

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
        return $this
            ->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->where('is_open' , '=', 1)
            ->where(function ($query) {
                $query->where('expire', '=', '0')
                    ->orWhere('expire', '<=', Carbon::now()->timestamp);
            });
    }

    /**
     * 用户角色
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')
            ->where('is_open' , '=', 1)
            ->where(function ($query) {
                $query->where('expire', '=', '0')
                    ->orWhere('expire', '<=', Carbon::now()->timestamp);
            });
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * 根据用户角色关联的角色获取用户所有的权限，并且返回单独绑定的权限
     * @return mixed
     */
    public function getRolePermissions()
    {
        $roleIds = $this->roles->pluck('id');
        $rolePermissions = Permission::whereIn('id', $roleIds)->get();
        $permissions = [];

        foreach ($rolePermissions as $permission) {
            $permissions[$permission->id] = $permission;
        }

        foreach ($this->permissions as $permission) {
            $permissions[$permission->id] = $permission;
        }

        return array_values($permissions);
    }
}
