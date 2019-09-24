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
     * 关联关注用户(就是自己的信息)
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

    /**
     * 获取当前用户关注的用户id组
     *
     *  @return Array
     */
    public static function getFollowMemberIdsByMmberId($member_id)
    {
        $myFollowMembers = self::where('member_id', $member_id)->get('follow_member_id');
        $my_follow_member_ids = $myFollowMembers ? array_column($myFollowMembers->toArray(), 'follow_member_id') : [];
        return $my_follow_member_ids;
    }

}
