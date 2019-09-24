<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    PostLikes,
    CommentLikes,
    MemberFollow,
    Comments,
    AnswerComments,
    Members,
    SystemMessageDetails
};
    
class Messages extends Model
{
    public static $_redis;
    protected $fillable = [
        'answer_like_id',
        'comment_like_id',
        'answer_comment_like_id',
        'post_like_id',
        'be_like_member_id',
        'system_message_detail_id',
        'is_readed',
        'type',
        'content',
        'member_id',
        'member_follow_id',
        'post_comment_id',
        'answer_comment_id'
    ];
    
    /**
     * 用户表
     *
     */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }
    
    /**
     * 关联资源点赞表
     *
     *
     */
    public function postLike()
    {
        return $this->hasOne(PostLikes::class, 'id', 'post_like_id');
    }

    /**
     * 关联系统消息
     */
    public function systemMessageDetail()
    {
        return $this->hasOne(SystemMessageDetails::class, 'id', 'system_message_detail_id');
    }

    /**
     * 通过点赞资源id入库消息记录 
     *
     * @return void
     */
    public static function insertByPostLikeId($post_like_id)
    {
        $postLike = PostLikes::find($post_like_id);
        $data['member_id']         = $postLike->member_id;
        $data['post_like_id']      = $postLike->id;
        $data['be_like_member_id'] = $postLike->post->member_id;
        $data['type']              = 1;
        switch($postLike->post->content_type) {
            case 1 : 
                $data['content'] = $postLike->member->nickname . "点赞你的视频" ;
                break;
            case 2 : 
                $data['content'] = $postLike->member->nickname . "点赞你的文章" ;
        }
        $isCreate = self::create($data);
    }

    /**
     * 通过点赞资源id删除消息记录 
     *
     * @return void
     */
    public static function deleteByPostLikeId($post_like_id)
    {
        self::where('post_like_id', $post_like_id)
        ->delete();
    }

    /**
     * 登记评论点赞消息
     *
     * @return void
     */
    public static function insertByCommentLikeId($comment_like_id)
    {
        $commentLike = CommentLikes::find($comment_like_id);
        $data['member_id']         = $commentLike->member_id;
        $data['comment_like_id']   = $commentLike->id;
        $data['be_like_member_id'] = $commentLike->comment->member->id;
        $data['type']              = 1;
        switch($commentLike->post->content_type) {
            case 1 : 
                $data['content'] = $commentLike->member->nickname . "点赞你的视频评论" ;
                break;
            case 2 : 
                $data['content'] = $commentLike->member->nickname . "点赞你的文章评论" ;
        }
        $isCreate = self::create($data);
    }

    /**
     * 取消登记评论点赞消息
     *
     * @return void
     */
    public static function deleteByCommentLikeId($comment_like_id)
    {
        self::where('comment_like_id', $comment_like_id)
        ->delete();
    }

    /**
     * 登记点赞答案消息
     *
     * @return void
     */
    public static function insertByAnswerLikeId($answer_like_id)
    {
        $answerLike= AnswerLikes::find($answer_like_id);
        $data['member_id']         = $answerLike->member_id;
        $data['answer_like_id']    = $answerLike->id;
        $data['be_like_member_id'] = Answers::find($answerLike->answer_id)->member_id;
        $data['content']           = $answerLike->member->nickname . "点赞你的答案" ;
        $data['type']              = 1;
        $isCreate = self::create($data);
    }

    /**
     *  取消点赞答案消息
     *
     * @return void
     */
    public static function deleteByAnswerLikeId($answer_like_id)
    {
        self::where('answer_like_id', $answer_like_id)
        ->delete();
    }

    /**
     * 登记答案评论点赞消息
     *
     * @return void
     */
    public static function insertByAnswerCommentLikeId(int $answer_comment_like_id)
    {
        $answerCommentLike = AnswerCommentLikes::find($answer_comment_like_id);
        $Post = $answerCommentLike->answer->post;
        $data['member_id']              = $answerCommentLike->member_id;
        $data['answer_comment_like_id'] = $answerCommentLike->id;
        $data['be_like_member_id']      = $Post->member->id;
        $data['content']                = $answerCommentLike->member->nickname . "点赞你的答案评论" ;
        $data['type']                   = 1;
        $isCreate = self::create($data);
    }

    /**
     * 取消答案评论点赞的消息
     *
     */
    public static function deleteByAnswerCommentLikeId($answer_comment_like_id)
    {
        self::where('answer_comment_like_id', $answer_comment_like_id)->delete();
    }
    
    /**
    * 登记关注消息
    *
    */
    public static function insertByMemberFollowId(int $member_follow_id)
    {
        $memberFollow              = MemberFollow::find($member_follow_id);
        $data['member_id']         = $memberFollow->member_id;
        $data['be_like_member_id'] = $memberFollow->follow_member_id;
        $data['content']           = "关注了你";
        $data['type']              = 2;
        $data['member_follow_id']  = $memberFollow->id;
        self::create($data);
    }
    
    /**
     * 点评作品消息
     * 
     * @return void
     */
    public static function insertByCommentId($comment_id)
    {
        $Comment = Comments::find($comment_id);
        $data['post_comment_id']   = $Comment->id;
        $data['member_id']         = $Comment->member_id;
        $data['be_like_member_id'] = $Comment->post->member->id;
        switch($Comment->post->content_type) {  
            case 1 :
                $data['content']  = $Comment->member->nickname . "点评你的视频";
                break;
            case 2 :
                $data['content']  = $Comment->member->nickname . "点评你的文章";
                break;
            case 3 :
                $data['content']  = $Comment->member->nickname . "点评你的问题";
                break;
            default:
        $data['content']  = $Comment->member->nickname . "点评你的作品";
        }
        $data['type'] = 3;
        self::create($data);
    }


    /**
    * 资源回复消息
    *
    * @return void
    */
    public static function postCommentReply($comment_id)
    {
        $Comment = Comments::find($comment_id);
        $data['post_comment_id']   = $Comment->id;
        $data['member_id']         = $Comment->member_id;
        $data['be_like_member_id'] = Comments::find($Comment->pid)->id;
        $data['content']           = $Comment->content;
        $data['type']              = 4;
        self::create($data);
    }

    /**
    * 问答评论回复消息
    *
    * @return void
    */
    public static function answerCommentReply(int $comment_id)
    {
        $answerComment             = AnswerComments::find($comment_id);
        $data['answer_comment_id'] = $answerComment->id;
        $data['member_id']         = $answerComment->member_id;
        $data['be_like_member_id'] = AnswerComments::find($answerComment->pid)->member_id;
        $data['content']           = $answerComment->content;
        $data['type']              = 4;
        self::create($data);
    }

    /**
    * 添加新的系统消息
    *
    * @return void
    */
    public static function addSystemMessageById($message_detail_id)
    {
        $Members = Members::where('status', 1)->get();
        if ($Members) {
            foreach($Members as $el) {
                $data['be_like_member_id'] = $el->id;
                $data['system_message_detail_id'] = $message_detail_id;
                $data['type']              = 5;
                self::create($data);
            }
        }
    }
    
    /**
     * 消息推送
     * 
     * @http POST
     */
    public static function sendMessage($post_data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('APP_URL') . ":7777");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $response = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response_code !== 200) {
            // 发送失败 处理
        }
    }

    protected static function getData($be_like_member_id, $type)
    {
        $result = [
            'content'    => '',
            'type'       => '',
            'created_at' => '',
            'count'      => ''
        ];
        $hasData = self::where('be_like_member_id', $be_like_member_id)
            ->where('type', $type)
            ->where('is_readed', 0)
            ->count();
        if ($hasData) {
            $result['count'] = $hasData;
           $news  = self::where('be_like_member_id', $be_like_member_id)
                ->where('type', $type)
                ->where('is_readed', 0)
                ->orderBy('created_at', 'desc')
                ->first(['content', 'created_at', 'type']);
            $result['content'] = $news->content;
            $result['type'] =  $type;
            $result['created_at'] = $news->created_at->toDateTimeString();
        }
        return count($result) > 0 ? $result : '';
    }

    /**
     *  系统消息
     *
     */
    public static function getSystemMessage($be_like_member_id)
    {
        $result = [
            'title'      => '',
            'type'       => '',
            'created_at' => '',
            'count'      => ''
        ];
        $hasData = self::where('be_like_member_id', $be_like_member_id)
            ->where('type', 5)
            ->where('is_readed', 0)
            ->count();
        if ($hasData) {
            $result['count'] = $hasData;
            $news = self::where('be_like_member_id', $be_like_member_id)
                ->where('type', 5)
                ->where('is_readed', 0)
                ->orderBy('created_at', 'desc')
                ->first(['system_message_detail_id', 'created_at']);
            $detail = SystemMessageDetails::where('id', $news->system_message_detail_id)
                ->first('title');
            $result['title'] = $detail->title;
            $result['type']  = 5;
            $result['created_at'] = $news->created_at->toDateTimeString();
        }
        return $result;
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function($message) {
            $Redis = Messages::getRedisInstance(); 
            if ($Redis->exists(env('REDIS_PREFIX') . $message->be_like_member_id)) {
                $data_format['likes']           = Messages::getData($message->be_like_member_id, 1);
                $data_format['follows']         = Messages::getData($message->be_like_member_id, 2);
                $data_format['comments']        = Messages::getData($message->be_like_member_id, 3);
                $data_format['replies']         = Messages::getData($message->be_like_member_id, 4);
                $data_format['system_messages'] = Messages::getSystemMessage($message->be_like_member_id);
                Messages::sendMessage([
                    'member_id'  => $message->be_like_member_id,
                    'data'       => json_encode($data_format)
                ]);
            }
        });
    }

    /**
     * redis实例
     *
     */
    public static function getRedisInstance()
    {
        if(!isset(self::$_redis)) {
            $redis = new \Redis();
            $redis->connect(
                getenv('REDIS_HOST'),
                getenv('REDIS_PORT')
            );
            $redis->select(env('REDIS_DB'));
            self::$_redis = $redis;
        }
        return self::$_redis;
    }
}
