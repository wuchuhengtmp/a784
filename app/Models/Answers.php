<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Members,
    AnswerComments,
    AnswerLikes    ,
    Posts
};

class Answers extends Model
{
    protected $fillable = [
        'content',
        'member_id',
        'post_id',
        'updated_at',
    ];

    protected $hidden = [
        'deleted_at', 
        'updated_at'
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
     *  关联用户评论
     *
     */
    public function answerComments()
    {
        return $this->hasMany(AnswerComments::class, 'answer_id', 'id');
    }

    /**
     *  关联点赞
      *
     */
    public function answerLikes()
    {
        return $this->hasMany(AnswerLikes::class, 'answer_id', 'id');
    }

    /**
     * 关联资源
     *
     */
    public function post()
    {
        return $this->hasOne(
            Posts::class,
            'id',
            'post_id'
        );
    }
}
