<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-05
 * Time: 18:44
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';

    /**
     * 拥有这个权限的角色
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }

    /**
     * 直接拥有这个权限的用户
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'permission_id', 'user_id');
    }
}
