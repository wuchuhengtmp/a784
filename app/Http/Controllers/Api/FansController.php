<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\CheckMemberIdRequest;
use App\Models\{
    Members,
    MemberFollow
};
          
class FansController extends Controller
{
    /**
     * 他（她）的粉丝
     */
    public function show(Request $Request)
    {
        $data = [];
        if (!$Request->member_id) return $this->responseError('缺少member_id参数');
        if (!$Member = Members::where('id', $Request->member_id)->first()) return $this->responseError('没有这个资源');
        $FollowMembers = MemberFollow::where('follow_member_id', $Request->member_id)
            ->with(['memberFollow'])
            ->get();
        $MyFollowMembers = MemberFollow::where('member_id', $this->user()->id)->get();
            // 用户自己本身注的ids 
            $MyFollowMemerIds = $MyFollowMembers ? array_values(array_column($MyFollowMembers->toArray(), 'follow_member_id')) : [];
            foreach($FollowMembers as $el) {
                $tmp['member_id'] = $el->memberFollow->id;
                $tmp['avatar']    = $el->memberFollow->avatar->url ? $this->transferUrl($el->member->avatar->url) : null;
                $tmp['nickname']  = $el->memberFollow->nickname;
                $has_level        = Members::getlevelInfoByMemberId($el->memberFollow->id);
                $tmp['level']     = $has_level ? $has_level->name : null;
                $tmp['sign']      = $el->memberFollow->sign;
                // 计算用户的关注和游客粉丝的是否是同一人
                $tmp['is_follow'] = $MyFollowMemerIds ? in_array($el->memberFollow->id, $MyFollowMemerIds) : false;
                $data[]           = $tmp;
            }
        return $this->responseData($data);
    }

    /**
     * 我的粉丝  
     *
     * @http GET
     *
     */
    public function me()
    {
        $data = [];
        $MyFans = MemberFollow::where('follow_member_id', $this->user()->id)->get();
        $MeFollowMembers = MemberFollow::where('member_id', $this->user()->id)
            ->get('follow_member_id');
        $my_follow_member_ids = $MeFollowMembers ? array_column($MeFollowMembers->toArray(), 'follow_member_id') : [];
        if ($MyFans) {
            foreach($MyFans as $el) {
                $tmp['member_id']       = $el->member_id;
                $tmp['nickname']        = $el->memberFollow->nickname;
                $tmp['sign']            = $el->memberFollow->sign;
                $tmp['avatar']          = $this->transferUrl($el->member->avatar->url);
                $tmp['is_fan_together'] = in_array($el->memberFollow->id, $my_follow_member_ids);
                $hasLevel               = Members::getlevelInfoByMemberId($el->id);
                $tmp['level']           = $hasLevel ? $hasLevel->name : null;
                $data[]                 = $tmp;
            }
        }
        return $this->responseData($data);
    }

    

}
