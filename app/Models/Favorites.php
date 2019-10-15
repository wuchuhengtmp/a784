<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Posts
};

class Favorites extends Model
{
    protected $fillable = [
        'member_id',
        'post_id'
    ];
    
    /**
     *  是否已经收藏
     *  
     *  @member_id  用户id 
     *  @post_id    资源id
     *  @return boolean
     */
    public static function isFavorite(int $member_id, int $post_id)
    {
        $has_data = self::where('post_id', $post_id)
            ->where('member_id', $member_id)
            ->first('id');
        return !$has_data ? true : false; 
    }


    /**
     * 关联收藏的资源
     *
     */
    public function post()
    {
        return $this->hasOne(Posts::class, 'id', 'post_id');
    }
}
