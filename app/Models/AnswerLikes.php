<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Answers,
    Posts,
    Members
};

class AnswerLikes extends Model
{
    protected $fillable = [
        'member_id',
        'answer_id'
    ];

    /**
     *  关联点赞的资源 
     *
     */
    public function post()
    {
        return $this->hasOneThrough(
            Posts::class,
            Answers::class, // 中间表
            'id',           // 中间表对应开始表的外键
            'id',           // 目标表外键对应中间表的外键
            'answer_id',    // 开始表对应中间表外键的键名
            'post_id'       // 中间表对应目标表的键名
        );
    }

    /**
     * 关联用户
     */ 
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }
}

