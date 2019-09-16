<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerLikes extends Model
{
    protected $fillable = [
        'member_id',
        'answer_id'
    ];
}
