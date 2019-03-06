<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-05
 * Time: 18:44
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';

    /**
     * 角色拥有的权限
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    /**
     * 拥有这个角色的用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }
}
