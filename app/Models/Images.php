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

    protected static function boot()
    {
        parent::boot();
        static::saving(function($image) {
            if(!isset(parse_url($image->url)['host'])) {
                $image->url  =  env('QINIU_DOMAIN') .'/' . $image->url;
            } 
        });

        static::updating(function($image) {
            if(!isset(parse_url($image->url)['host'])) {
                $image->url  =  env('QINIU_DOMAIN') .'/' . $image->url;
            } 
        });
    }
}
