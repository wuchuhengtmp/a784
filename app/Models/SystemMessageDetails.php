<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Images,
    Messages
};


class SystemMessageDetails extends Model
{
    protected $fillable = [
        'id',
        'title',
        'content',
        'image_id',
        'created_at',
        'updated_at',
    ];

    public function avatar()
    {
        return $this->hasOne(Images::class, 'id', 'image_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function($message){
            $Image = Images::create([
                'url' => $message->url,
            ]);
            unset($message->url);
            $message->image_id = $Image->id;
        });
        static::saved(function($message){
            // 添加系统消息
            Messages::addSystemMessageById($message->id);
        });
    } 
}
