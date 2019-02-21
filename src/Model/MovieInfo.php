<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-17
 * Time: 10:08
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed name
 * @property mixed show_name
 * @property mixed category_id
 * @property mixed cover
 * @property mixed lang
 * @property mixed area
 * @property mixed year
 * @property mixed note
 * @property mixed actor
 * @property mixed director
 * @property mixed description
 * @property mixed id
 */
class MovieInfo extends Model
{
    protected $table = 'movie_info';
}
