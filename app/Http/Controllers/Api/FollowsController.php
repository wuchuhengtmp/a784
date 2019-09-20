<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\CheckMemberIdRequest;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Members,
    MemberFollow,
    Posts,
    Messages
};

class FollowsController extends Controller
{
    /**
     * 关注首页
     *
     * @http GET
     */
    public function index()
    {
        $myFollowMembers = MemberFollow::where('member_id', $this->user()->id)
            ->get();
        if (!$myFollowMembers) return $this->responseError('还没有关注!');
        $my_follow_member_ids = array_column($myFollowMembers->toArray(), 'follow_member_id');
        $Posts = Posts::whereIn('content_type', [1,2])->
            whereIn('member_id',$my_follow_member_ids)
            ->with(['member', 'comments', 'images'])
            ->withCount(['comments', 'likes'])
            ->paginate(18);
        $data = []; 
        if ($Posts){ 
            foreach($Posts as $el) {
                $tmp['title']          = $el->title;
                $tmp['post_id']        = $el->id;
                $tmp['created_at']     = $el->created_at->toDateTimeString();
                $tmp['nickname']       = $el->member->nickname;
                $tmp['avatar']         = $this->transferUrl($el->member->avatar->url);
                $tmp['content_type']   = $el->content_type;
                $tmp['comments_count'] = $el->comments_count;
                $tmp['likes_count']    = $el->likes_count;
                $tmp['Images']  = [];
                foreach($el->Images as $images_el) {
                    $tmp['Images'][]         = $this->transferUrl($images_el->url);
                }
                if ($el->comments) {
                    $tmp['comments'] = [];
                    foreach($el->comments as $comment_el) {
                        $tmp_comment['nickname']   = $comment_el->member->nickname;
                        $tmp_comment['avatar']     = $this->transferUrl($comment_el->member->avatar->url);
                        $tmp_comment['created_at'] = $comment_el->created_at->toDateTimeString();
                        $tmp_comment['id']         = $comment_el->id;
                        $tmp_comment['pid']         = $comment_el->pid;
                        $hasLevel                  = Members::getlevelInfoByMemberId($el->member->id);
                        $tmp_comment['level']      = $hasLevel ? $hasLevel->name : null;
                        $tmp_comment['content']    = $comment_el->content;
                        $tmp['comments'][] = $tmp_comment;
                    }
                $tmp['comments']  = isset($tmp['comments']) ?  $this->_arrToTree($tmp['comments']) : [];
                }
                $data['data'][] = $tmp;
            }
            $data['count'] = $Posts->total();
        }
        return $this->responseData($data);
    }

    /**
     * 他（她）的关注
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
                $tmp['is_follow'] = $MyFollowMemerIds ? in_array($el->member->id, $MyFollowMemerIds) : false;
                $data[]           = $tmp;
            }
        return $this->responseData($data);
    }

    /**
     * 关注他（她）
     *
     */
    public function store(Request $Request)
    {
        if (!Members::isMember($Request->member_id)) return $this->responseError('没有这个用户');        
        if (
            MemberFollow::where('member_id', $this->user->id)
            ->where('follow_member_id', $Request->member_id)
            ->first('id')
        ) 
            return $this->responseError('你已经关注这个用户了');
        $memberFollow = MemberFollow::create(['member_id'=> $this->user()->id, 'follow_member_id'=>$Request->member_id]);
        if (!$memberFollow) {
            return $this->responseError('服务器内部错误');
        } else {
            Messages::insertByMemberFollowId($memberFollow->id);
            return $this->responseSuccess();
        }
    }

    /**
     *  我的关注
     *
     * @http    GET
     */
    public function showMe()
    {
        $data = [];
        $MyFollowMembers = MemberFollow::where('member_id', $this->user()->id)
            ->get();
        $MembersForFollowsMe = MemberFollow::where('follow_member_id', $this->user()->id)->get('member_id');
        $my_fans_ids = $MembersForFollowsMe ? array_column($MembersForFollowsMe->toArray(), 'member_id') : []; 
        if ($MyFollowMembers) {
            foreach($MyFollowMembers as $el) {
                $tmp['member_id']       = $el->member->id;
                $tmp['nickname']        = $el->member->nickname;
                $tmp['sign']            = $el->member->sign;
                $tmp['avatar']          = $this->transferUrl($el->member->avatar->url);
                $tmp['is_fan_together'] = in_array($el->member->id, $my_fans_ids);
                $hasLevel               = Members::getlevelInfoByMemberId($el->id);
                $tmp['level']           = $hasLevel ? $hasLevel->name : null;
                $data[]                 = $tmp;
            }
            
        }
        return $this->responseData($data);
    }

    /**
     * 取消关注
     *
     */
    public function destroy(Request $Request) 
    {
        MemberFollow::where('follow_member_id', $Request->member_id) 
            ->where('member_id', $this->user()->id)
            ->delete();
        return $this->responseSuccess();
    }

}
