<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Comments,
    Messages,
    Posts,
    Members,
    CommentLikes
};

class CommentsController extends Controller
{

    /**
     *  添加新的评论
     *
     */
    public function store($post_id, Request $Request)
    {
        $Request->validate([
            'content' => 'required',
        ]); 
        $is_save = Comments::create([
            'member_id' => $this->user()->id,
            'content'   => $Request->content,
            'post_id'   => $Request->post_id,
        ]);
        if ($is_save) {
            Messages::insertByCommentId($is_save->id); 
            return $this->responseSuccess();
        } else  {
            return $this->responseError();
        }
    }


    /**
     *  添加回复
     *
     */
    public function replyStore($comment_id, Request $Request)
    {
        $Request->validate([
            'content' => 'required',
        ]); 
        if (!$parent_comment = Comments::where('id', $comment_id)->first())
            return $this->responseError('你选择的那个评论不存在, 请检查');
        $is_save = Comments::create([
            'pid'       => $parent_comment->id,
            'content'   => $Request->content,
            'post_id'   => $parent_comment->post_id,
            'path'      => $parent_comment->path . '-' . $parent_comment -> id,
            'member_id' => $this->user()->id,
        ]);
        if(!$is_save) {
            return $this->responseError('服务器内部错误');
        } else  {
            Messages::postCommentReply($is_save->id);
            return $this->responseSuccess();
        }
    }

    /**
     *  资源评论
     *
     */
    public function postShow(Request $Request)
    {
        $Request->validate([
            'comment_limit' => 'filled|numeric',
            'reply_limit'   => 'filled|numeric',
        ]);
        $result = [
            'data'  => [],
            'count' => 0,
            'total' => 0
        ];
        $comment_limit = $Request->comment_limit ??  3;
        $reply_limit   = $Request->reply_limit ??  3;
        $hasPost = Posts::find($Request->post_id);
        if (!$hasPost) return $this->responseError('没有这个资源');
        $Comments = Comments::where('comments.post_id', $Request->post_id) 
            ->select(DB::raw(
                "(SELECT COUNT(*) FROM comment_likes WHERE comment_likes.comment_id = comments.id ) AS likes_count, comments.*"
            ))
            ->where('comments.pid', 0)
            ->orderBy('id', 'desc')
            ->paginate($comment_limit);
        $result['count'] = $Comments->total();
        if(!$Comments->isEmpty()) {
            foreach($Comments as $el) {
                $tmp                = [];
                $tmp['id']          = $el->id;
                $tmp['content']     = $el->content;
                $tmp['created_at']  = $el->created_at->toDateTimeString();
                $tmp['member_id']   = $el->member_id;
                $tmp['nickname']    = $el->member->nickname;
                $tmp['avatar']      = $el->member->avatar->url;
                $tmp['level']       = Members::getLevelNameByMemberId($el->member_id);
                $tmp['likes_count'] = $el->likes_count;
                $tmp['is_author']   = $el->post_id == $Request->post_id ? true : false;
                $tmp['is_like']     = CommentLikes::isLike($el->id, $this->user()->id);
                $tmp['children']    = [];
                // 子评论 
                $subComments = Comments::where('path', "like", "0-" . $el->id . "%")
                    ->where('post_id', $el->post_id)
                    ->select(DB::raw(
                        "CONCAT(comments.path, '-',  comments.id) AS order_weight, comments.*, 
                        (SELECT COUNT(*) FROM comment_likes WHERE comment_likes.comment_id = comments.id ) AS likes_count"
                    ))
                    ->leftJoin('comment_likes', 'comment_likes.comment_id', '=' , 'comments.id')
                    ->orderBy('order_weight')
                    ->limit($reply_limit)
                    ->get();
                if(!$subComments->isEmpty()) {
                    $subCommentsCount = Comments::where('path', "like", "0-" . $el->id . "%")
                        ->where('post_id', $el->post_id)
                        ->count();
                    $tmp['children']['count'] = $subCommentsCount;
                    foreach($subComments as $suK=>$subEl) {
                        $sub_tmp['id']          = $subEl->id;
                        $sub_tmp['content']     = $subEl->content;
                        $sub_tmp['is_author']       = $subEl->post_id == $Request->post_id ? true : false;
                        $sub_tmp['created_at']  = $subEl->created_at->toDateTimeString();
                        $sub_tmp['member_id']   = $subEl->member_id;
                        $sub_tmp['avatar']      = $subEl->member->avatar->url;
                        $sub_tmp['level']       = Members::getLevelNameByMemberId($subEl->member_id);
                        $sub_tmp['likes_count'] = $subEl->likes_count;
                        $sub_tmp['is_like']     = CommentLikes::isLike($subEl->id, $this->user()->id);
                        $sub_tmp['nickname']    = $subEl->member->nickname;
                        $sub_tmp['PTOC'] = $suK === 0 ? $sub_tmp['nickname'] . '回复' . $tmp['nickname'] : $sub_tmp['nickname'] . '回复' . $subComments[--$suK]->member->nickname;
                        $sub_tmp['path']        = $subEl->path;
                        $tmp['children']['data'][]      = $sub_tmp;
                    }
                }
                $result['data'][] = $tmp;
            }
        }
        return $this->responseData($result);
    }


    /**
     * 获取资源评论下的回复
     *
     */
    public function postReplyShow(Request $Request)
    {
        $result = [
            'data' => [],
            'count' => 0
        ];
        $limit = $Request->limit ?? 3;
        $hasComment = Comments::find($Request->comment_id);
        if (!$hasComment) return $this->responseError('没有这个评论');
        $Replies = Comments::where('path', 'like', "0-" . $Request->comment_id . "%")
            ->select(DB::raw( "CONCAT(comments.path, '-',  comments.id) AS order_weight, comments.*"))
            ->where('post_id', $hasComment->post_id) 
            ->paginate($limit);
        if(!$Replies->isEmpty()) {
            foreach($Replies as $k => $v) {
                $tmp['id']          = $v->id;
                $tmp['content']     = $v->content;
                $tmp['is_author']       = $v->post_id == $Request->post_id ? true : false;
                $tmp['created_at']  = $v->created_at->toDateTimeString();
                $tmp['member_id']   = $v->member_id;
                $tmp['avatar']      = $v->member->avatar->url;
                $tmp['level']       = Members::getLevelNameByMemberId($v->member_id);
                $tmp['likes_count'] = $v->likes_count;
                $tmp['is_like']     = CommentLikes::isLike($v->id, $this->user()->id);
                $tmp['nickname']    = $v->member->nickname;
                $tmp['PTOC'] = $k === 0 ? $v->member->nickname. ' 回复 ' . $hasComment->member->nickname: $v->member->nickname. ' 回复 ' . $Replies[--$k]->member->nickname;
                $result['data'][]      = $tmp;
            }
        } 
        $result['count'] = $Replies->total();
        return $this->responseData($result);
    }
}
