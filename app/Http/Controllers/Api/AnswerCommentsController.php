<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    AnswerComments,
    AnswerCommentLikes
};

class AnswerCommentsController extends Controller
{
    /**
     * 评论回答
     *
     * @http    POST
     * @return  json
     */
    public function store(Request $Request)
    {
        $Request->validate([
            'content' => 'required'
        ]);
        $hasSave = AnswerComments::create([
            'answer_id' => $Request->answer_id,
            'content'   => $Request->content,
            'member_id' => $this->user()->id,
            'pid'       => 0,
            'path'      => 0
        ]); 
        if ($hasSave)
            return $this->responseSuccess();
        else 
            return $this->responseError();
    }

    /**
     * 回复
     *
     * @http    POST
     * @return  json
     */
    public function replyStore(Request $Request)
    {
        $Request->validate([
            'content'    => 'required'
        ]);
        if (!$ParenComment = AnswerComments::find($Request->comment_id)) 
            return $this->responseError('您要回复的这个评论不存在，请检查comment_id参数是否有误');
        $hasSave = AnswerComments::create([
            'member_id' => $this->user()->id,
            'content'   => $Request->content,
            'pid'       => $ParenComment->id,
            'path'      => $ParenComment->path . '-' . $ParenComment->id,
            'answer_id' => $ParenComment->answer_id
        ]);
        if ($hasSave)
            return $this->responseSuccess();
        else 
            return $this->responseError();
            
    }

    /**
     *   点赞评论
     *   
     *  @http   POST
     */
    public function likeStore(Request $Request)
    {
        if (!AnswerComments::find($Request->comment_id))
            return $this->responseError('没有这个评论，请检查参数comment_id');
        $be_like = AnswerCommentLikes::where('member_id', $this->user()->id)
            ->where('answer_comment_id', $Request->comment_id)
            ->first();
        if ($be_like) 
            return $this->responseError('您已经点赞过了');
        $hasCreate = AnswerCommentLikes::create([
            'answer_comment_id'  => $Request->comment_id,
            'member_id'  => $this->user()->id
        ]);
        return $hasCreate ? $this->responseSuccess() : $this->responseError('服务器内部错误');
    }
}
