<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
        'pivot'
    ];
    protected $fillable = [
        'url',
        'from'
    ];
}
