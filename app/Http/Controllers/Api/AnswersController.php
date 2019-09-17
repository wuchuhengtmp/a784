<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    Posts,
    Members,
    MemberFollow,
    AnswerLikes,
    Answers,
    AnswerCommentLikes
};

class AnswersController extends Controller
{

    /**
     *  答案首页
     *
     *
     */
    public function index()
    {
        $data = [];
        $Posts = Posts::where('content_type', 3) 
           ->with(['member'])
           ->withCount(['AnswerComments'])
           ->paginate(18);
        if ($Posts) {
            $tmp_data = [];
            foreach($Posts as $el) {
                $tmp['title'] = $el->title;
                $tmp['nickname'] = $el->member->nickname;
                $tmp['member_id'] = $el->member->id;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['id'] = $el->id;
                $tmp['avatar'] = $this->transferUrl($el->member->avatar->url);
                $tmp['answer_comments_count'] =  $el->answer_comments_count;
                $tmp_data[] = $tmp;
            }
        $data['data']  = $tmp_data;
        $data['count']  =  $Posts->total(); 
        }
        return $this->responseData($data); 
    } 

    /**
     * 写回答
     *
     * @http    POST
     */
    public function store(Request $Request)
    {
        $Request->validate([
            'content' => 'required'
        ]);
        $hasData = Answers::create([
            'member_id'   => $this->user()->id,
            'content' => $Request->content,
            'post_id' => $Request->post_id
        ]); 
        if ($hasData) 
            return $this->responseSuccess();
        else
            return $this->responseError();
    }
    
    /**
     * 问答详情
     *
     * @http    GET
     */
    public function show(Request $Request)
    {
        $hasData = $Post = Posts::where('id', $Request->post_id)
            ->whereNull('deleted_at')
            ->with([
                'member',
                'answers' => function($query) {
                    $query->withCount(['answerComments'])->with(['answerComments']);
                }
            ])
            ->withCount(['answerComments'])
            ->first();
        if (!$hasData)
            return $this->responseError('没有这个问答, 请检查post_id是否存在');
        $myFollowMember =  Members::with(['follows'])->find($this->user()->id);
        $my_follow_ids = $myFollowMember->follows ? array_column($myFollowMember->follows->toArray(), 'id') : [];
        $data['id']                    = $hasData->id;
        $data['title']                 = $hasData->title;
        $data['nickname']              = $hasData->member->nickname;
        $data['avatar']                = $this->transferUrl($hasData->member->avatar->url);
        $data['answer_comments_count'] = $hasData->answer_comments_count;
        $data['member_id']             = $hasData->member->id;
        $data['is_follow']             = in_array($hasData->member->id, $my_follow_ids);
        $myFollowMembers     = MemberFollow::where('member_id', $this->user()->id)->get('follow_member_id');
        $my_follow_ids       = $myFollowMembers ? array_column($myFollowMembers->toArray(), 'follow_member_id') : null;
        $myLikeAnswers       = AnswerLikes::where('member_id', $this->user()->id)->get(['answer_id']);
        $my_like_answer_ids  = $myLikeAnswers ? array_column($myLikeAnswers->toArray(), 'answer_id') : [];
        $myCommentLikes      = AnswerCommentLikes::where('member_id', $this->user()->id)->get(['answer_comment_id']);
        $my_comment_like_ids = $myCommentLikes ? array_column($myCommentLikes->toArray(), 'answer_comment_id') : [];
        $data['answers'] = [];
        if($hasData->answers) {
            // 计算回答
            foreach($hasData->answers as $el) {
                $tmp['member_id'] = $el->member_id;
                $tmp['nickname']  = $el->member->nickname;
                $tmp['content']   = $el->content;
                $tmp['avatar']    = $this->transferUrl($el->member->avatar->url);
                $hasLevel         = Members::getlevelInfoByMemberId($el->member_id);
                $tmp['level']     = $hasLevel ?  $hasLevel->name : null;
                $tmp['is_follow'] = in_array($el->member_id, $my_follow_ids);
                $tmp['is_like']   = in_array($el->id, $my_like_answer_ids);
                $tmp['answer_like_count'] =  AnswerLikes::where('answer_id', $el->id)->count();
                $tmp['answer_comments_count'] = $el->answer_comments_count;
                // 计算回答下的评论
                $tmp_answer_comments = [];
                if ($el->answerComments) {
                    foreach ($el->answerComments as $answerComments)  {
                        $tmp_answer_comments['nickname']      = $answerComments->member->nickname;
                        $tmp_answer_comments['id']            = $answerComments->id;
                        $tmp_answer_comments['avatar']        = $this->transferUrl($answerComments->member->avatar->url);
                        $tmp_answer_comments['pid']           = $answerComments->pid;
                        $tmp_answer_comments['created_at']    = $answerComments->created_at->toDateTimeString();
                        $hasLevel                             = Members::getlevelInfoByMemberId($answerComments->member->id);
                        $tmp_answer_comments['level']         = $hasLevel ? $hasLevel->name : null;
                        $tmp_answer_comments['answer_comment_likes_count'] = AnswerCommentLikes::where('answer_comment_id', $answerComments->id)->count();
                        $tmp_answer_comments['is_like']       = AnswerCommentLikes::where('answer_comment_id', $answerComments->id)
                            ->where('member_id', $this->user()->id)
                            ->first() ?  true : false;
                        $tmp_answer_comments['is_questionee'] = $el->member_id == $answerComments->member_id ? true : false;
                        $tmp_answer_comments['is_questioner'] = $hasData->member_id == $answerComments->member_id ? true : false;
                        $tmp_answer_comments['content']       = $answerComments->content;
                        $tmp['answerComments'][]              = $tmp_answer_comments;
                     }
                    $tmp['answerComments'] = $this->_arrToTree($tmp['answerComments']);
                }
                $data['answers'][] = $tmp;
            }
        } 
        return $this->responseData($data);
    }

    /**
     * 点赞回答
     *
     */
    public function likeAnswer(Request $Request)
    {
        if (!Answers::find($Request->answer_id)) 
            return $this->responseError('没有这个回答，请检查下answer_id参数');
        if (AnswerLikes::where('answer_id', $Request->answer_id)->where('member_id', $this->user()->id)->first())
            return $this->responseError('你已经点赞过这个回答了');
        $hasCreate = AnswerLikes::create([
            'member_id' => $this->user()->id,
            'answer_id' => $Request->answer_id
        ]);
        return $hasCreate ? $this->responseSuccess() : $this->responseError('服务器内部错误');
    }

    /**
     * 我的提问
     *
     * @http    GET
     */
    public function meQuestions()
    {
        $data = [];
        $Posts = Posts::where('content_type', 3) 
                ->where('member_id', $this->user()->id)
                ->withCount(['answerComments'])
                ->paginate(18);
        if ($Posts) {
            $tmp_data = [];
            foreach($Posts as $el) {
                $tmp['id']                    = $el->id;
                $tmp['title']                 = $el->title;
                $tmp['avatar']                = $this->transferUrl($el->member->avatar->url);
                $tmp['nickname']              = $el->member->nickname;
                $tmp['created_at']            = $el->created_at->toDateTimeString();
                $tmp['answer_comments_count'] = $el->answer_comments_count;
                $tmp_data[]                   = $tmp;
            } 
            $data['data'] = $tmp_data;
            $data['count'] =  $Posts->total();
        }
        return $this->responseData($data);
    }

    /**
     * 我的回答
     *
     * @http GET
     */
    public function meAnswers()
    {
        $data = [];
        $Answers = Answers::where('member_id', $this->user()->id)
            ->withCount(['answerComments'])
            ->paginate(18);
        if ($Answers)  {
            $tmp_data = [];
            foreach($Answers as $el) {
                $tmp['id'] = $el->id;
                $tmp['content'] = $el->content;
                $tmp['answer_comments_count']  = $el->answer_comments_count;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['post_id'] = $el->post_id;
                $tmp_data[] = $tmp;
            }
            $data['data'] = $tmp_data;
            $data['count'] = $Answers->total();
        }
        return $this->responseData($data);
    }

    /**
     * 游客信息-我的提问
     *
     * @http    GET
     */
    public function showQuestionsByMemberId(Request $Request)
    {
        
        if (!Members::find($Request->member_id))
            return $this->responseError('没有这个用户');
        $data = [];
        $Posts = Posts::where('content_type', 3) 
                ->where('member_id', $Request->member_id)
                ->withCount(['answerComments'])
                ->paginate(18);
        if ($Posts) {
            $tmp_data = [];
            foreach($Posts as $el) {
                $tmp['id']                    = $el->id;
                $tmp['title']                 = $el->title;
                $tmp['avatar']                = $this->transferUrl($el->member->avatar->url);
                $tmp['nickname']              = $el->member->nickname;
                $tmp['created_at']            = $el->created_at->toDateTimeString();
                $tmp['answer_comments_count'] = $el->answer_comments_count;
                $tmp_data[]                   = $tmp;
            } 
            $data['data'] = $tmp_data;
            $data['count'] =  $Posts->total();
        }
        return $this->responseData($data);
    }
    
    /**
     * 游客信息-我的回答
     *
     * @http    GET
     */
    public function showAnswersByMemberId(Request $Request)
    {
        if (!Members::find($Request->member_id))
            return $this->responseError('没有这个用户');
        $data = [];
        $Answers = Answers::where('member_id', $Request->member_id)
            ->withCount(['answerComments'])
            ->paginate(18);
        if ($Answers)  {
            $tmp_data = [];
            foreach($Answers as $el) {
                $tmp['id'] = $el->id;
                $tmp['content'] = $el->content;
                $tmp['answer_comments_count']  = $el->answer_comments_count;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['post_id'] = $el->post_id;
                $tmp_data[] = $tmp;
            }
            $data['data'] = $tmp_data;
            $data['count'] = $Answers->total();
        }
        return $this->responseData($data);
    }
}
