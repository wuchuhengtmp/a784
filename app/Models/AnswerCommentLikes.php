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
}
