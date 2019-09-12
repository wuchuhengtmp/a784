<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\CheckMemberIdRequest;
use App\Models\{
    Members,
    MemberFollow
};
          

class FollowsController extends Controller
{
    /**
     * 他（她）的关注
     *
     */
    public function show(Request $Request)
    {
        $data = [];
        if (!$Request->member_id) return $this->responseError('缺少member_id参数');
        if (!$Member = Members::where('id', $Request->member_id)->first()) return $this->responseError('没有这个资源');
        $FollowMembers = MemberFollow::where('member_id', $Request->member_id)
            ->with(['member'])
            ->get();
        $MyFollowMembers = MemberFollow::where('member_id', $this->user()->id)->get();
        if ($MyFollowMembers) {
            // 用户自己本身关注的ids 
            $MyFollowMemerIds = $MyFollowMembers ? array_values(array_column($MyFollowMembers->toArray(), 'follow_member_id')) : [];
            foreach($FollowMembers as $el) {
                $tmp['member_id'] = $el->member->id;
                $tmp['avatar']    = $el->member->avatar->url ? $this->transferUrl($el->member->avatar->url) : null;
                $tmp['nickname']  = $el->member->nickname;
                $has_level        = Members::getlevelInfoByMemberId($el->member->id);
                $tmp['level']     = $has_level ? $has_level->name : null;
                $tmp['sign']      = $el->member->sign;
                // 计算用户是否用户本身和游客关注的是否是同一人
                $tmp['is_follow'] = in_array($el->member->id, $MyFollowMemerIds);
                $data[]           = $tmp;
            }
        }
        return $this->responseData($data);
    }
}
