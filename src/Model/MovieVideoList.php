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
 * @property mixed movie_info_id
 * @property mixed video_info
 * @property mixed source_site_id
 */
class MovieVideoList extends Model
{
    protected $table = 'movie_video_list';
}
