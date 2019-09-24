<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    AnswerComments,
    Answers,
    Members
};

class AnswerCommentLikes extends Model
{
    protected $fillable = [
        'answer_comment_id',
        'member_id'
    ];

    /**
     * 关联post
     *
     */
    public function answer()
    {
        return $this->hasOneThrough(
            Answers::class,
            AnswerComments::class,
            'id',
            'id',
            'answer_comment_id',
            'answer_id'
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

    /**
     * 获取点赞的评论ids
     *
     * @return Array
     */
    public static function getLikesByMemberId($member_id)
    {
        $myCommentLikes      = self::where('member_id', $member_id)->get(['answer_comment_id']);
        $my_comment_like_ids = $myCommentLikes ? array_column($myCommentLikes->toArray(), 'answer_comment_id') : [];
        return $my_comment_like_ids;
    }

}
