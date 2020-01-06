<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\CheckMemberIdRequest;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Members,
    MemberFollow,
    Posts,
    Messages,
    Comments,
    Favorites
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
            ->orderby('id', 'desc') 
            ->get();
        if (!$myFollowMembers) return $this->responseError('还没有关注!');
        $my_follow_member_ids = array_column($myFollowMembers->toArray(), 'follow_member_id');
        foreach($my_follow_member_ids as &$id) {
           $Member = Members::where('id', $id)->get();
           if ($Member->isEmpty()) {
               unset($id);
           }
        }
        $Posts = Posts::whereIn('content_type', [1,2])->
            whereIn('member_id',$my_follow_member_ids)
            ->with(['member', 'comments', 'images'])
            ->withCount(['comments', 'likes'])
            ->orderBy('posts.created_at', 'desc')
            ->paginate(18);
        $data = []; 
        if ($Posts){ 
            foreach($Posts as $el) {
                $tmp = [];
                $tmp['title']          = $el->title;
                $tmp['post_id']        = $el->id;
                $tmp['created_at']     = $el->created_at->toDateTimeString();
                $tmp['nickname']       = $el->member->nickname;
                $tmp['avatar']         = $this->transferUrl($el->member->avatar->url);
                $tmp['content_type']   = $el->content_type;
                $tmp['comments_count'] = $el->comments_count;
                $tmp['likes_count']    = $el->likes_count;
                $tmp['Images']  = []; 
                $tmp['is_like']      = Posts::isLike($this->user()->id, $el->id);
                foreach($el->Images as $images_el) {
                    $tmp['Images'][]         = $this->transferUrl($images_el->url);
                }
                if ($el->content_type == 1) 
                $tmp['video_url']     = $el->video_url;
                if (!$el->comments->isEmpty()) {
                    $comments = Comments::select(
                        DB::raw("CONCAT(path, '-', id) AS order_weight,comments.*")
                        ) 
                        ->orderBy('order_weight')
                        ->where('post_id', $el->id)
                        ->get();
                    $tmp['comments'] = [];
                    foreach($comments as $comment_el) {
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
                $tmp['comments']  = count($tmp['comments']) > 0 ?  $this->_arrToTree($tmp['comments']) : [];
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
                $tmp['avatar']    = $el->member->avatar->url ? $this->transferUrl($el->member->avatar->url) : env('DEFAULT_AVATAR');
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
        if ($this->user()->id == $Request->member_id) return $this->responseError('关注失败，您不能关注您自己');
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
                if ($el->member){
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


    /**
     * 关注首页
     *
     *
     */
    public function getAll(Request $Request)
    {
        $myFollowMembers = MemberFollow::where('member_id', $this->user()->id)
            ->get();
        if (!$myFollowMembers) return $this->responseError('还没有关注!');
        $my_follow_member_ids = array_column($myFollowMembers->toArray(), 'follow_member_id');
        $Posts = Posts::whereIn('content_type', [1,2])->
            whereIn('member_id',$my_follow_member_ids)
            ->with(['member', 'comments', 'images'])
            ->withCount(['comments', 'likes'])
            ->orderby('posts.id', 'desc')
            ->paginate(18);
        $data = []; 
        if ($Posts){ 
            foreach($Posts as $el) {
                if (isset($tmp)) unset($tmp);
                if (!$el->member)  {
                    continue;
                }
                $tmp['title']          = $el->title;
                $tmp['post_id']        = $el->id;
                $tmp['created_at']     = $el->created_at->toDateTimeString();
                $tmp['nickname']       = $el->member->nickname ?? '';
                $tmp['member_id']      = $el->member_id;
                $tmp['avatar']         = $el->member->avatar->url ? $this->transferUrl($el->member->avatar->url) : env('DEFAULT_AVATAR');
                $tmp['content_type']   = $el->content_type;
                $tmp['comments_count'] = $el->comments_count;
                $tmp['likes_count']    = $el->likes_count;
                $tmp['is_favorite']    = Favorites::isFavorite($el->member_id, $el->id);
                $tmp['Images']  = []; 
                $tmp['is_like']      = Posts::isLike($this->user()->id, $el->member_id);
                foreach($el->Images as $images_el) {
                    $tmp['Images'][]         = $images_el->url ? $this->transferUrl($images_el->url) : env('DEFAULT_AVATAR');
                }
                if ($el->content_type == 1) 
                $tmp['video_url']     = $el->video_url;
                $tmp['comments']      = [];
                $comment_count =  Comments::where('pid', 0)
                    ->where('post_id', $el->id)
                    ->count();
                $tmp['comments'] = [];
                if ($comment_count >  0) {
                    // 评论
                    $comments = Comments::select(
                        DB::raw("CONCAT(path, '-', id) AS order_weight,comments.*")
                        ) 
                        ->orderBy('order_weight')
                        ->where('post_id', $el->id)
                        ->where('pid', 0)
                        ->limit(3)
                        ->paginate(2);
                    $tmp['comments']['count'] = $comment_count;
                    /* dd($comments->toArray());exit; */
                    foreach($comments as $comment_el) {
                        if (isset($tmp_comment))  unset($tmp_comment);
                        if (!$comment_el->member) {
                            continue;
                        }
                        $tmp_comment['nickname']   = $comment_el->member->nickname ?? '';
                        $tmp_comment['member_id']   = $comment_el->member->id;
                        $tmp_comment['avatar']     = $this->transferUrl($comment_el->member->avatar->url);
                        $tmp_comment['created_at'] = $comment_el->created_at->toDateTimeString();
                        $tmp_comment['id']         = $comment_el->id;
                        $tmp_comment['pid']        = $comment_el->pid;
                        $hasLevel                  = Members::getlevelInfoByMemberId($el->member->id);
                        $tmp_comment['level']      = $hasLevel ? $hasLevel->name : null;
                        $tmp_comment['content']    = $comment_el->content;
                        $tmp_comment['replies']    = [];
                        // 回复
                        $reply_count = Comments::where('post_id', $el->id)
                            ->where('path', 'like', "0-" . $comment_el->id . "%")
                            ->count();
                        if ($reply_count > 0) {
                            $tmp_comment['replies']['count'] = $reply_count;
                            $Replies = Comments::where('post_id', $el->id)
                                ->where('path', 'like', "0-" . $el->comment_el . '%')
                                ->paginate(2);
                            if (isset($tmp_comment))  unset($tmp_reply);
                            foreach($Replies as $k=>$reply_el) {
                                $tmp_reply['nickname']   = $reply_el->member->nickname;
                                $tmp_reply['member_id']   = $reply_el->member_id;
                                $tmp_reply['avatar']     = $reply_el->member->avatar->url ? $this->transferUrl($reply_el->member->avatar->url) : env('DEFAULT_AVATAR');
                                $tmp_reply['created_at'] = $reply_el->created_at->toDateTimeString();
                                $tmp_reply['id']         = $reply_el->id;
                                $tmp_reply['pid']        = $reply_el->pid;
                                $tmp_reply['level']      = Members::getLevelNameByMemberId($reply_el->member_id);
                                $tmp_reply['content']    = $reply_el->content;
                                $tmp_reply['PTOC']       = $k === 0 ? $reply_el->member->nickname . ' 回复 ' . $comment_el->member->nickname : $reply_el->member->nickname . ' 回复 ' . $Replies[--$k]->member->nickname;
                                $tmp_comment['replies']['data'][] = $tmp_reply;
                            }
                        }
                        $tmp['comments']['data'][] = $tmp_comment;
                        break;
                    }
                    $tmp['comments']['count'] = $comments->total();
                }
                /* dump($tmp); */
                $data['data'][] = $tmp;
            }
            $data['count'] = $Posts->total();
        }
        return $this->responseData($data);
    }
}
