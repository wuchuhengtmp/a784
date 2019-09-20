<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Members,
    AnswerCommentLikes,
    Answer,
    Posts
};

class AnswerComments extends Model
{
    protected $hidden = [
        'deleted_at', 
        'updated_at'
    ];

    protected $fillable = [
        'answer_id',
        'member_id',
        'content',
        'pid',
        'path'
    ];

    /**
     * 关联用户
     *
     */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }

    /**
     *  关联点赞用户 
     *
     */
    public function likeMembers()
    {
        return $this->hasMany(AnswerComments::class, 'answer_comment_id', 'id');
    }

    /**
     * 关联post 
     *
     */
    public function post()
    {
        return $this->hasOneThrough(
            Posts::class, 
            Answers::class, 
            'id',
            'id',
            'answer_id',
            'post_id'
        );
    }
}
