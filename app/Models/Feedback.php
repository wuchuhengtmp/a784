<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Members;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $fillable = [
        'content',
        'contact',
        'member_id'
    ];

    /**
     * 关联用户
     *
     */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }
}
