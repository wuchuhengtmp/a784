<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    Answers,
    AnswerLikes,
    AnswerComments,
    Messages,
    Comments,
    CommentLikes,
    Posts,
    PostLikes,
    AnswerCommentLikes
};

class LikesController extends Controller
{
    /**
     * 点赞消息列表
     *
     */
    public function index()
    {

    } 

    /**
     * 点赞视频和文章 
     * 
     * @http    POST
     */
    public function store(Request $Request)
    {
        $hasPost = Posts::where('id', $Request->post_id)->first();
        if (!$hasPost) return $this->responseError('没有这个资源，请检查参数');
        $hasLike = PostLikes::where('post_id', $hasPost->id)
            ->where('member_id', $this->user()->id)
            ->first();
            if ($hasLike) return $this->responseError('您已经点赞过了');
        $isCreate = PostLikes::create([
            'post_id' => $hasPost->id,
            'member_id' =>$this->user()->id
        ]);
        $isCreate && Messages::insertByPostLikeId($isCreate->id); 
        return $isCreate ? $this->responseSuccess() : $this->responseError('服务器内部错误');
    }

    /**
     * 取消点赞的视频或文章
     *
     * @http DELETE
     */
    public function destroy(Request $Request)
    {
        $hasData = PostLikes::where('member_id', $this->user()->id)
            ->where('post_id', $Request->post_id) 
            ->first();
        if (!$hasData) return $this->responseError('没有这个资源，请检查参数');
        $is_delete = $hasData->delete();
        /* Messages::deleteByPostLikeId($hasData->id); */
        return $is_delete ? $this->responseSuccess() : $this->responseError('服务器内部错误');
    }


    /**
     * 点赞资源评论
     *
     * @http POST
     */
    public function commentStore(Request $Request)
    {
        $hasData = Comments::where('id', $Request->comment_id) 
            ->first();
        if (!$hasData) return $this->responseError('没有这个评论，请检查下参数 ');

        $hasLike = CommentLikes::where('comment_id', $hasData->id)
            ->where('member_id', $this->user()->id)
            ->first();
        if($hasLike)  return $this->responseError('你已经点赞过了');
        $commentLike = CommentLikes::create([
            'member_id' => $this->user()->id,
            'comment_id' => $hasData->id,
        ]);

        if ($commentLike) {
            Messages::insertByCommentLikeId($commentLike->id);
            return $this->responseSuccess();
        } else {
            return $this->responseError();
        }
    }

    /**
     * 取消资源评论的点赞
     *
     * @http DELETE
     */
    public function commentDestroy(Request $Request)
    {
        $hasData = CommentLikes::where('comment_id', $Request->comment_id)
            ->where('member_id', $this->user()->id)
            ->first();
        if (!$hasData) return $this->responseError('没有这个资源，请检查参数');
        if ($hasData->delete()) {
            /* Messages::deleteByCommentLikeId($hasData->id); */
            return $this->responseSuccess();
        } else {
            return $this->responseError();
        }
    }

    /**
     *  点赞答案
     *
     * @http POST
     */
    public function AnswerStore(Request $Request)
    {
        $Answer = Answers::find($Request->answer_id);
        if(!$Answer) return $this->responseError('没有这个回答');
        $answerLike = AnswerLikes::where('answer_id', $Answer->id)
            ->where('member_id', $this->user()->id)
            ->first();
        if ($answerLike) return $this->responseError('你已经点赞过了');
        $hasCreate = AnswerLikes::create([
            'answer_id' => $Answer->id,
            'member_id' => $this->user()->id
        ]); 
        Messages::insertByAnswerLikeId($hasCreate->id);
        return $this->responseSuccess();
    }

    /**
     * 取消点赞答案
     *
     * @http DELETE
     */
    public function AnswerDestroy(Request $Request)
    {
        $Answer = Answers::find($Request->answer_id);
        if(!$Answer) return $this->responseError('没有这个回答');
        $hasData = AnswerLikes::where('answer_id', $Request->answer_id) 
            ->where('member_id', $this->user()->id)
            ->first();
        if (!$hasData) return $this->responseSuccess();
        $hasData->delete();
        /* Messages::deleteByAnswerLikeId($hasData->id); */
        return $this->responseSuccess();
    }

    /**
     * 点赞答案评论
     *
     * @http POST
     */ 
    public function answerCommentStore(Request $Request)
    {
        if (!AnswerComments::find($Request->comment_id))
            return $this->responseError('没有这个评论，请检查参数comment_id');
        $be_like = AnswerCommentLikes::where('member_id', $this->user()->id)
            ->where('answer_comment_id', $Request->comment_id)
            ->first();
        if ($be_like) return $this->responseError('您已经点赞过了');
        $hasCreate = AnswerCommentLikes::create([
            'answer_comment_id'  => $Request->comment_id,
            'member_id'  => $this->user()->id
        ]);
        Messages::insertByAnswerCommentLikeId($hasCreate->id);
        return $hasCreate ? $this->responseSuccess() : $this->responseError('服务器内部错误');
    }

    /**
     * 取消答案评论的点赞
     *
     * @http DELETE
     */
    public function answerCommentDestroy(Request $Request)
    {
        $answerComment = AnswerComments::find($Request->comment_id);
        if (!$answerComment) return $this->responseError('没有这条评论');
        $hasData = AnswerCommentLikes::where('answer_comment_id', $answerComment->id) 
            ->where('member_id', $this->user()->id)
            ->first();
        if ($hasData){
            $hasData->delete();
        }
        return $this->responseSuccess();
    }

}
