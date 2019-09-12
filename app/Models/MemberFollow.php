<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Members;

class MemberFollow extends Model
{
    protected $table = 'member_follows';
    protected $fillable = [
        'member_id',
        'follow_member_id'
    ];

    /**
     * 关联关注用户(就是TA的粉丝用户信息)
     *
     */
    public function memberFollow()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }
    
    /**
     * 关联关注用(TA粉了的人的用户信息)
     *
     */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'follow_member_id');
    }

    /**
     *  获取粉丝量
     * @uid      init    用户id
     * @return   init    粉丝量
     */
    public  static  function  countFansByUid(int $uid)
    {
        $is_int = self::where('follow_member_id', $uid)
            ->count();
        return $is_int;
    }
}
