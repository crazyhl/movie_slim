<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-17
 * Time: 13:09
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    public function permissions() {
        return $this->morphToMany(Permission::class, 'model', 'permission_model_relation');
    }

    /**
     * 获取关联到的父级菜单
     */
    public function parentMenu()
    {
        return $this->hasOne(self::class, 'id', 'parent');
    }

    /**
     * 获取关联到的子菜单
     */
    public function childrenMenu()
    {
        return $this->hasMany(self::class, 'parent', 'id');
    }
}
