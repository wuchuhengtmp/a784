<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerCommentLikes extends Model
{
    protected $fillable = [
        'answer_comment_id',
        'member_id'
    ];
}
