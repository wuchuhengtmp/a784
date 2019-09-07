<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Images;
use App\Models\Region;
use App\Models\Educations;
use App\Models\Posts;
use App\Models\MemberFollow;
use App\Models\Favorites;

class Members extends Model
{
    public $timestamps = false;

    /**
     * 关联用户评论
     */
    public function comments()
    {
        return $this->hasMany(Comments::class);
    }


    /**
     * 关联头像
     *
     */ 
    public function avatar()
    {
        return $this->hasOne(Images::class, 'id', 'avatar_image_id');
    }


    /**
    *   关联地区
    *
    */
    public function region()
    {
        return $this->hasOne(Region::class, 'id', 'region_id');
    }

    /**
    * 关联学历
    *
    */
    public function education()
    {
       return $this->hasOne(Educations::class, 'id', 'education_id');
    }

    /**
     * 关联发布资源
     */
    public function posts()
    {
        return $this->hasMany(Posts::class, 'member_id', 'id');
    }

    /**
     * 关联他的关注
     *
     */
    public function follows()
    {
        return $this->belongsToMany(Members::class, 'member_follows', 'member_id', 'follow_member_id');
    }


    /**
     * 关联他的收藏
     *  
     */
    public function favorites() : object
    {
        return $this->belongsToMany(Posts::class, 'favorites', 'member_id', 'post_id');
    }

}

