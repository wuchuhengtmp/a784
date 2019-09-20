<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    Comments,
    Messages
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
}
