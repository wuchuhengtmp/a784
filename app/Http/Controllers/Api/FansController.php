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
        if ($MyFollowMembers) {
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
                $tmp['is_follow'] = in_array($el->memberFollow->id, $MyFollowMemerIds);
                $data[]           = $tmp;
            }
        }
        return $this->responseData($data);
    }

}
