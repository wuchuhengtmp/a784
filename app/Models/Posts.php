<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{
    Images,
    Members,
    PostLikes,
    Comments,
    Tags,
    Answers,
    AnswerComments,
    PostImages,
};


class Posts extends Model
{
    use  SoftDeletes;    
    protected $fillable = [
        'video_url',
        'title',
        'tag_id',
        'content_type',
        'content',
        'member_id',
        'sponsor_at',
        'duration',
    ]; 
    protected $dates = ['deleted_at'];

    protected $hidden = [
        'sponsor_at',
        'deleted_at',
        'updated_at'
    ];

    /**
     * 关联图片
     *
     */
    public function images() : object
    {

        return $this->belongsToMany(Images::class, 'post_image', 'post_id', 'image_id');
    }

    /**
     * 图片
     *
     */
    /* public function */ 

    /**
    * 关联用户信息
    *
    */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }

    /**
     * 关联点赞用户
     *
     */
    public function likes() : object
    {
        return $this->belongsToMany(Members::class, 'post_likes', 'post_id', 'member_id');
    }

    /**
     * 关联评论用户
     *
     */
    public function comments() 
    {
        return $this->hasMany(Comments::class, 'post_id', 'id');  
    }

    /**
     * 关联收藏用户
     *
     */
    public function favorites()
    {
        return $this->belongsToMany(Members::class, 'favorites', 'post_id', 'member_id');
    }

    /**
     * 关联分类标签
     *
     */
    public function tag()
    {
        return $this->hasOne(Tags::class, 'id', 'tag_id');
    }

    /**
     * 回答关联
     *
     */
    public function answers()
    {
        return $this->hasMany(Answers::class, 'post_id', 'id');
    }


    /**
     * 关联问题评论
     *
     */
    public function answerComments()
    {
        return $this->hasManyThrough(
            AnswerComments::class,
            Answers::class,
            'post_id',   
            'answer_id',
            'id',
            'id'
        );
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function($post) {
             $post->images()->delete();
             $post->comments()->delete();
        });
    }

    /**
     * 是否点赞
     *
     * @return boolean 
     */
    public static function  isLike($member_id, $post_id)
    {
        $hasData = PostLikes::where('member_id', $member_id)->get();
        if (!$hasData) return false;
        $post_ids = array_column($hasData->toArray(), 'post_id');
        return in_array($post_id, array_column($hasData->toArray(), 'post_id'));
    }

    /**
     * 热搜期限
     * 
     * @post_id 资源id
     * @time_len 时长(s/秒)
     * return void
     */
    public static function sponsor($post_id, $time_len)
    {
        $Post = self::find($post_id);
        $base_time = strtotime($Post->sponsor_at) < time() ?  time() : strtotime($Post->sponsor_at);
        $time = $base_time +  $time_len;
        $Post->sponsor_at = date('Y-m-d H:i:s', $time);
        $Post->save();
    }
}

