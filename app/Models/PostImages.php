<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImages extends Model
{
    protected $table = 'post_image';
    protected $fillable = [
        'member_id',
        'image_id'
    ];
}
