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
}
