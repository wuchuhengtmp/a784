<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    Comments,
    Members,
    MemberFollow
};

class SearchController extends Controller
{
    /**
     *  搜索用户 
     *
     */
    public function searchByUser(Request $Request)
    {
        $result_data = [];
        $Request->validate([
            'nickname' => 'required',
        ]); 
        $keyword = $Request->nickname;
        $search_result = Members::where('nickname', 'like', "%{$keyword}%")
            ->where('status', 1)
            ->get();
        if (!$search_result) return $this->responseData($result_data);

        $MyFollowMembers = MemberFollow::where('member_id', $this->user()->id)->get();
            // 用户自己本身关注的ids 
            $MyFollowMemerIds = $MyFollowMembers ? array_values(array_column($MyFollowMembers->toArray(), 'follow_member_id')) : [];
            foreach($search_result as $el) {
                $tmp['member_id'] = $el->id;
                $tmp['avatar']    = $el->avatar->url ? $this->transferUrl($el->avatar->url) : null;
                $tmp['nickname']  = $el->nickname;
                $has_level        = Members::getlevelInfoByMemberId($el->id);
                $tmp['level']     = $has_level ? $has_level->name : null;
                $tmp['sign']      = $el->sign;
                // 计算用户是否用户本身和游客关注的是否是同一人
                $tmp['is_follow'] = $MyFollowMemerIds ? in_array($el->id, $MyFollowMemerIds) : false;
                $data[]           = $tmp;
            }
        return $this->responseData($data);
    }
}

