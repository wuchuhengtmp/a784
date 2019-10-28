<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\{
    Posts,
    PostLikes,
    CommentLikes,
    Comments,
    Favorites,
    Messages
};

class Members extends Authenticatable implements JWTSubject
{
    public $table = 'members';
    protected $appends = ['job_name'];    /* use  Notifiable; */
    protected $fillable = [
        'phone',
        'name',
        'avatar_image_id',
        'location',
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
     * 关联他的粉丝
     *
     */
    public function fans()
    {
        return $this->belongsToMany(Members::class, 'member_follows', 'follow_member_id', 'member_id');
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

    /**
     *  job字段以数字1学生2老师读取转换
     *
     *
     */
    public function getJobNameAttribute()
    {
        if ($this->job == 1) 
            return '学生';
        else if ($this->job == 2) 
            return '老师';
    } 

    /*
     *  会员等级
     *
     * @membe_id    会员id
     * @return       mix 
     */ 
    public static function getlevelInfoByMemberId(int $member_id) 
    {
        $money = AccountLogs::getMaxBetweenTimeByUid($member_id,  time() - 60*60*24*365);
        $fans = MemberFollow::countFansBYUid($member_id);
        $has_level = Levels::getLevelByFansAndMony($fans, $money);
        return $has_level;
    }

    /**
     *  获取会员名
     *
     * @return mix
     */
    public static function getLevelNameByMemberId($member_id)
    {
        $hasData = self::getlevelInfoByMemberId($member_id);
        return $hasData ? $hasData->name : null;
    }

    /**
    * 关联用户资源点赞
    *
    */
    public function postLikes()
    {
        return $this->hasManyThrough(
            PostLikes::class, 
            Posts::class,
            'member_id', 
            'post_id', 
            'id', 
            'id' 
        );
    }

    /**
     * 关联用户评论点赞
     *
     */
    public function commentLikes()
    {
        return $this->hasManyThrough(
            CommentLikes::class,
            Comments::class,
            'member_id',
            'comment_id',
            'id',
            'id'
        );
    }

    /**
     * 是否有这个用户 
     * 
     * @return boolean
     */
    public static function isMember(int $member_id)
    {
        $has_data = self::where('id', $member_id)
            ->first('id');
        return $has_data ? true : false;
    }

    /**
     * 获取关注的用户id组
     *
     * @member_id   用户尖
     * @return      array 
     */
    public static function getFollowIds(int $member_id) //: array
    {
        $myFollowMembers = MemberFollow::where('member_id', $member_id)
            ->get('follow_member_id');
        return $myFollowMembers ? array_column($myFollowMembers->toArray(), 'follow_member_id') : [];
        
    }

    /**
     * 关联用户消息
     *
     */
    public function message()
    {
        return $this->hasMany(Messages::class, 'be_like_member_id', 'id');
    }
}

