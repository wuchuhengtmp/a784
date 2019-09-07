<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Images extends Model
{
    use  SoftDeletes;    
    protected $dates = ['deleted_at'];

    /**
     * 修改图片读取时是路径
     * 
     */
    public function getUrlAttribute($value)
    {
           return $value; 
    }
    
}
