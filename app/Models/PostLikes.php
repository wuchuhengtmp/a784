<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Posts,
    LikeMessages,
    Members
};

class PostLikes extends Model
{
    protected $fillable = [
        'post_id',
        'member_id'
    ];

    /**
     * 关联资源表
     *
     */
    public function post()
    {
        return $this->hasOne(Posts::class, 'id', 'post_id');
    }

    /**
     * 关联点赞消息表
     *
     *
     */
    public function likeMessage()
    {
        return $this->hasOne(LikeMessages::class, 'post_like_id', 'id');
    }

    /**
     * 关联用户
     *
     */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }

    protected static function boot()
    {
        parent::boot();
    }

    
}
