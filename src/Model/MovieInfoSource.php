<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-14
 * Time: 16:12
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed local_id
 * @property mixed name
 * @property mixed show_name
 * @property mixed source_site_id
 * @property mixed source_id
 * @property mixed source_category_id
 * @property mixed source_last_update
 * @property mixed cover
 * @property mixed lang
 * @property mixed area
 * @property mixed year
 * @property mixed note
 * @property mixed actor
 * @property mixed director
 * @property mixed description
 */
class MovieInfoSource extends Model
{
    protected $table = 'movie_info_source';
}
