<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Members extends Authenticatable implements JWTSubject
{
    public $table = 'members';

    /* use  Notifiable; */
    protected $fillable = [
        'phone',
        'password',
        'name',
        'avatar_image_id',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'nickname',
        'region_id',
        'sign',
        'sex',
        'age',
        'born',
        'job',
        'weixin',
        'phone',
        'phone_verified_at',
        'phone_verified_code',
        'school',
        'department',
        'professional',
        'education_id',
        'start_school_at',
        'hobby',
        'next_plan',
        'status',
        'balance',
        'weixin_expires_in',
        'weixin_refresh_token',
        'weixin_access_token',
        'weixin_access_token_at',
        'weixin_openid',
        'weixin_unionid'
    ];
    public $timestamps = false;

    /**
     * 关联用户评论
     */
    public function comments()
    {
        return $this->hasMany(Comments::class);
    }


    /**
     * 关联头像
     *
     */
    public function avatar()
    {
        return $this->hasOne(Images::class, 'id', 'avatar_image_id');
    }


    /**
    *   关联地区
    *
    */
    public function region()
    {
        return $this->hasOne(Region::class, 'id', 'region_id');
    }

    /**
    * 关联学历
    *
    */
    public function education()
    {
       return $this->hasOne(Educations::class, 'id', 'education_id');
    }

    /**
     * 关联发布资源
     */
    public function posts()
    {
        return $this->hasMany(Posts::class, 'member_id', 'id');
    }

    /**
     * 关联他的关注
     *
     */
    public function follows()
    {
        return $this->belongsToMany(Members::class, 'member_follows', 'member_id', 'follow_member_id');
    }


    /**
     * 关联他的收藏
     *
     */
    public function favorites() : object
    {
        return $this->belongsToMany(Posts::class, 'favorites', 'member_id', 'post_id');
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function($Member) {
        });
    }
}

