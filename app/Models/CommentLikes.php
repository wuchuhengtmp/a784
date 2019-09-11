<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentLikes extends Model
{
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
}
