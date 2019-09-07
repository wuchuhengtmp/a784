<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Members;

class MemberFollow extends Model
{
    protected $table = 'member_follows';

    /**
     * 关联关注用户
     *
     */
    public function memberFollow()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
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
