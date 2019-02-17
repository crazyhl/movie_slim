<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-17
 * Time: 13:10
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class CategorySourceCategoryRelation extends Model
{
    protected $table = 'category_source_site_category_relation';
    public $timestamps = false;
}
