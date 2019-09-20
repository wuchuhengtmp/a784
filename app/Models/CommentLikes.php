<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Comments,
    Posts,
    Members
};

class CommentLikes extends Model
{
    protected $fillable = [
        'member_id',
        'comment_id'
    ];

    /**
    *  是否点赞过
    * 
    *  @comment_id 评论id
    *  @member_id   用户id
    *  @return  boolean 
    */
    public static function isLike(int $comment_id, int $member_id)
    {
        $hasData = self::where('comment_id', $comment_id)
            ->where('member_id', $member_id)
            ->first();
        return $hasData ? true : false;

    }

    /**
     * 被点赞的资源
     *
     */
    public function post()
    {
        return $this->hasOneThrough(
            Posts::class,
            Comments::class,
            'id',
            'id',
            'comment_id',
            'post_id'
        );
    }

    /**
     * 关联用户
     *
     */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }
}
