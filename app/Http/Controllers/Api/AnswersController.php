<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Posts,
    Members,
    MemberFollow,
    AnswerLikes,
    Answers,
    AnswerCommentLikes,
    AnswerComments,
    Comments
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
            ->orderBy('created_at', 'desc')
           ->paginate(18);
        if ($Posts) {
            $tmp_data = [];
            foreach($Posts as $el) {
                $tmp['title'] = $el->title;
                $tmp['nickname'] = $el->member->nickname;
                $tmp['member_id'] = $el->member->id;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['id'] = $el->id;
                $tmp['avatar'] = isset($el->member->avatar->url) ? $this->transferUrl($el->member->avatar->url) : '';
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
        $data['avatar']                = isset($hasData->member->avatar->url) ? $this->transferUrl($hasData->member->avatar->url) : '';
        $data['answer_comments_count'] = $hasData->answer_comments_count;
        $data['member_id']             = $hasData->member->id;
        $data['is_follow']             = in_array($hasData->member->id, $my_follow_ids);
        $myFollowMember =  MemberFollow::where('member_id', $this->user()->id)->get('follow_member_id');
        $my_follow_ids = $myFollowMember ? array_column($myFollowMember->toArray(), 'follow_member_id') : [];
        $myLikeAnswers       = AnswerLikes::where('member_id', $this->user()->id)->get(['answer_id']);
        $my_like_answer_ids  = $myLikeAnswers ? array_column($myLikeAnswers->toArray(), 'answer_id') : [];
        $myCommentLikes      = AnswerCommentLikes::where('member_id', $this->user()->id)->get(['answer_comment_id']);
        $my_comment_like_ids = $myCommentLikes ? array_column($myCommentLikes->toArray(), 'answer_comment_id') : [];
        $data['answers'] = [];
        if($hasData->answers) {
            // 计算回答
            foreach($hasData->answers as $el) {
                $tmp['member_id'] = $el->member_id;
                $tmp['id']         = $el->id;
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
                $tmp['answerComments'] = [];
                if (!$el->answerComments->isEmpty()) {
                    $comments = AnswerComments::select(
                        DB::raw("CONCAT(path, '-', id) AS order_weight,answer_comments.*")
                        ) 
                        ->orderBy('order_weight')
                        ->where('answer_id', $el->id)
                        ->get();
                    foreach ($comments as $answerComments)  {
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
                    $tmp['answerComments'] = isset($tmp['answerComments']) ? $this->_arrToTree($tmp['answerComments']) : [];
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
                $tmp['nickname'] = $el->member->nickname;
                $tmp['avatar']  = $el->member->avatar->url;
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
                $tmp['id']                    = $el->id;
                $tmp['content']               = $el->content;
                $tmp['answer_comments_count'] = $el->answer_comments_count;
                $tmp['nickname']              = $el->member->nickname;
                $tmp['avatar']                = $this->transferUrl($el->member->avatar->url);
                $tmp['created_at']            = $el->created_at->toDateTimeString();
                $tmp['post_id']               = $el->post_id;
                $tmp_data[]                   = $tmp;
            }
            $data['data'] = $tmp_data;
            $data['count'] = $Answers->total();
        }
        return $this->responseData($data);
    }

    /**
     * 问答详情
     *
     */
    public function version2show(Request $Request)
    {
        $hasData = $Post = Posts::where('id', $Request->post_id)
            ->where('content_type', 3)
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
        $myFollowMember =  MemberFollow::where('member_id', $this->user()->id)->get('follow_member_id');
        $my_follow_ids = $myFollowMember ? array_column($myFollowMember->toArray(), 'follow_member_id') : [];
        $data['id']                    = $hasData->id;
        $data['title']                 = $hasData->title;
        $data['nickname']              = $hasData->member->nickname;
        $data['avatar']                = $this->transferUrl($hasData->member->avatar->url);
        $data['answer_comments_count'] = $hasData->answer_comments_count;
        $data['member_id']             = $hasData->member->id;
        $data['is_follow']             = in_array($hasData->member->id, $my_follow_ids);
        return $this->responseData($data);
    }

   /**
    *  答案 
    *
    * @http get 
    */
    public function answersShow(Request $Request)
    {
        $result = [
            'data'  => [],
            'count' => 0,
        ];
        $comment_limit = $Request->comment_limit ?? 2;
        $Posts = Posts::find($Request->question_id);
        $hasAnswers= Answers::where('post_id', $Request->question_id)
            ->paginate(18);
        if ($hasAnswers->isEmpty()) return $this->responseError('没有这个问题');
        $result['count']  = $hasAnswers->total();
        $result['data'] = $this->_getAnswers($hasAnswers);
        return $this->responseData($result);
    }

    /**
     * 获取答案的详情
     * 
     * @return 答案  
     */
    protected function _getAnswers(object $Answers) 
    {
        $my_follow_ids       = Members::getFollowIds($this->user()->id);
        $my_like_answer_ids  = AnswerLikes::getLikesByMemberId($this->user()->id);
        $my_comment_like_ids = AnswerCommentLikes::getLikesByMemberId($this->user()->id);
        foreach($Answers as $el) {
                $tmp['member_id'] = $el->member_id;
                $tmp['id']         = $el->id;
                $tmp['nickname']  = $el->member->nickname;
                $tmp['content']   = $el->content;
                $tmp['avatar']    = $this->transferUrl($el->member->avatar->url);
                $hasLevel         = Members::getlevelInfoByMemberId($el->member_id);
                $tmp['level']     = $hasLevel ?  $hasLevel->name : null;
                $tmp['is_follow'] = in_array($el->member_id, $my_follow_ids);
                $tmp['is_like']   = in_array($el->id, $my_like_answer_ids);
                // 获取评论
                $tmp['comments'] = $this->_getComments($el->id);
                $result[] = $tmp;
        }
        return $result;
    }

    /**
     * 获取评论
     *
     * @return 评论
     */
    public function _getComments(int $answer_id)
    {
        $Comments = AnswerComments::where('answer_id', $answer_id)
            ->where('pid', 0)
            ->paginate(2);
        if($Comments->isEmpty()) return [];
        $el = Answers::find($answer_id);
        $Post = Posts::find($el->post_id);
        $result['count'] = $Comments->total();
        foreach($Comments as $Comm) {
            $tmp_answer_comments['nickname']      = $Comm->member->nickname;
            $tmp_answer_comments['id']            = $Comm->id;
            $tmp_answer_comments['avatar']        = $this->transferUrl($Comm->member->avatar->url);
            $tmp_answer_comments['pid']           = $Comm->pid;
            $tmp_answer_comments['created_at']    = $Comm->created_at->toDateTimeString();
            $hasLevel                             = Members::getlevelInfoByMemberId($Comm->member->id);
            $tmp_answer_comments['level']         = $hasLevel ? $hasLevel->name : null;
            $tmp_answer_comments['answer_comment_likes_count'] = AnswerCommentLikes::where('answer_comment_id', $Comm->id)->count();
            $tmp_answer_comments['is_like']       = AnswerCommentLikes::where('answer_comment_id', $Comm->id)
                ->where('member_id', $this->user()->id)
                ->first() ?  true : false;
            $tmp_answer_comments['is_questionee'] = $el->member_id == $Comm->member_id ? true : false;
            $tmp_answer_comments['is_questioner'] = $Post->member_id == $Comm->member_id ? true : false;
            $tmp_answer_comments['content']       = $Comm->content;
            // 回复
            $hasReplies = AnswerComments::where('path', 'like', '0-' . $Comm->id. '%')
                ->select(
                    DB::raw("CONCAT(path, '-', id) AS order_weight,answer_comments.*")
                ) 
                ->orderBy('order_weight')
                ->paginate(1);
            if (!$hasReplies->isEmpty()) {
                unset($tmp_answer_comments['replies']);
                $tmp_answer_comments['replies']['count'] = $hasReplies->total();
                foreach($hasReplies as $k=>$reply){
                    $tmp_answer_reply['nickname']      = $reply->member->nickname;
                    $tmp_answer_reply['id']            = $reply->id;
                    $tmp_answer_reply['avatar']        = $this->transferUrl($reply->member->avatar->url);
                    $tmp_answer_reply['pid']           = $reply->pid;
                    $tmp_answer_reply['created_at']    = $reply->created_at->toDateTimeString();
                    $hasLevel                     = Members::getlevelInfoByMemberId($reply->member->id); 
                    $tmp_answer_reply['level']         = $hasLevel ? $hasLevel->name : null;
                    $tmp_answer_reply['answer_comment_likes_count'] = AnswerCommentLikes::where('answer_comment_id', $reply->id)->count();
                    $tmp_answer_reply['is_like']       = AnswerCommentLikes::where('answer_comment_id', $reply->id)
                        ->where('member_id', $this->user()->id)
                        ->first() ?  true : false;
                    $tmp_answer_reply['is_questionee'] = $el->member_id == $reply->member_id ? true : false;
                    $tmp_answer_reply['is_questioner'] = $Post->member_id == $reply->member_id ? true : false;
                    $tmp_answer_reply['content']       = $reply->content;
                    $tmp_answer_reply['PTOC']          = $reply->member->nickname . ' 回复 ' . $tmp_answer_comments['nickname'];
                    $tmp_answer_comments['replies']['data'][] = $tmp_answer_reply;
                    break;
                }
            }
            $result['data'][] = $tmp_answer_comments;
        }
        return $result;
    }


    /**
    *  评论回复
    *
    */
    public function repliesshow(Request $Request)
    {
        $tmp_answer_comments = [];
        $limit = $Request->comment_limit ?? 1; 
        $Comm =  AnswerComments::find($Request->answer_id);
        if (!$Comm) return $this->responseError('没有这个评论, 请检查参数是否正确');
            $hasReplies = AnswerComments::where('path', 'like', '0-' . $Comm->id. '%')
                ->select(
                    DB::raw("CONCAT(path, '-', id) AS order_weight,answer_comments.*")
                ) 
                ->orderBy('order_weight')
                ->paginate($limit);
            if (!$hasReplies->isEmpty()) {
                $el = Answers::find($Comm->answer_id);
                $Post = Posts::find($el->post_id);
                $tmp_answer_comments['count'] = $hasReplies->total();
                foreach($hasReplies as $k=>$reply){
                    $tmp_answer_reply['nickname']      = $reply->member->nickname;
                    $tmp_answer_reply['member_id']      = $reply->member->id;
                    $tmp_answer_reply['id']            = $reply->id;
                    $tmp_answer_reply['avatar']        = $this->transferUrl($reply->member->avatar->url);
                    $tmp_answer_reply['pid']           = $reply->pid;
                    $tmp_answer_reply['created_at']    = $reply->created_at->toDateTimeString();
                    $hasLevel                     = Members::getlevelInfoByMemberId($reply->member->id); 
                    $tmp_answer_reply['level']         = $hasLevel ? $hasLevel->name : null;
                    $tmp_answer_reply['answer_comment_likes_count'] = AnswerCommentLikes::where('answer_comment_id', $reply->id)->count();
                    $tmp_answer_reply['is_like']       = AnswerCommentLikes::where('answer_comment_id', $reply->id)
                        ->where('member_id', $this->user()->id)
                        ->first() ?  true : false;
                    $tmp_answer_reply['is_questionee'] = $el->member_id == $reply->member_id ? true : false;
                    $tmp_answer_reply['is_questioner'] = $Post->member_id == $reply->member_id ? true : false;
                    $tmp_answer_reply['content']       = $reply->content;
                    $tmp_answer_reply['PTOC']          = $k === 0 ? $reply->member->nickname . ' 回复 ' . $Comm->member->nickname : $reply->member->nickname . ' 回复 ' . $hasReplies[--$k]->member->nickname;
                    $tmp_answer_comments['data'][] = $tmp_answer_reply;
                }
            }
        return $this->responseData($tmp_answer_comments);
    }
}

