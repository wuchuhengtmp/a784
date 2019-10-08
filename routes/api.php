<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Route::middleware('auth:api')->get('/user', function (Request $request) { */
/*     return $request->user(); */
/* }); */




$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',
    /* 'middleware' => 'serializer:array' */
], function($api) {
    // 短信验证码
    $api->post('verificationCodes', 'VerificationCodesController@store')
        ->name('api.verificationCodes.store');
    // 重置用户密码短信验证码
    $api->post('verificationMemberCodes', 'VerificationCodesController@update')
        ->name('api.verificationCodes.ResetPassword');
    // 用户注册
    $api->post('members', 'MembersController@store')
        ->name('api.members.store');
    // 重置密码
    $api->patch('members', 'MembersController@update')
        ->name('api.members.update');
    // 微信获取openid
    $api->get('socials/{social_type}/appid', 'AuthorizationsController@getAppid')
        ->name('api.socials.authorizations.openid');
    // 第三方登录
    $api->post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
        ->name('api.socials.authorizations.store');
    // 登录
    $api->post('authorizations', 'AuthorizationsController@store')
        ->name('api.authorizations.store');
    // 刷新token
    $api->put('authorizations/current', 'AuthorizationsController@update')
        ->name('api.authorizations.update');
     // 删除token
    $api->delete('authorizations/current', 'AuthorizationsController@destroy')
        ->name('api.authorizations.destroy');
     // 需要 token 验证的接口

    $api->group(['middleware' => ['checktoken', 'api.auth']], function($api) {
        // 首页视频分页
        $api->get('videos', 'VideosController@index')->name('api.video.index');
        //视频详情
        $api->get('videos/{id}', 'VideosController@show')
            ->where(['id' => '[0-9]+'])
            ->name('api.video.show');
        // 单个资源的评论
        $api->get('videos/comments/{id}', 'VideosController@Comments')->name('api.comments.show');
        // 当前登录用户信息
        $api->get('members/me', 'MembersController@me')->name('api.member.me');
        //上传文件
        $api->post('resource', 'ResourceController@store');
        //视频上传
        $api->post('videos', 'VideosController@store')->name('api.video.store');
        //文章上传
        $api->post('articles', 'ArticlesController@store')->name('api.article.store');
        //问答上传
        $api->post('questions', 'QuestionController@store')->name('api.question.store');
        // 分类标签
        $api->get('tags', 'TagsController@index')->name('api.tags.index');
        // 他（她）的关注
        $api->get('follows/{member_id}', 'FollowsController@show')
            ->where(['member_id' => '[0-9]+']);
        //关注他（她）
        $api->post('follows/{member_id}', 'FollowsController@store')
            ->where(['member_id' => '[0-9]+']);
        //取消关注他（她）
        $api->delete('follows/{member_id}', 'FollowsController@destroy')
            ->where(['member_id' => '[0-9]+']);
        // 他（她）的粉丝
        $api->get('fans/{member_id}', 'FansController@show')
            ->where(['member_id' => '[0-9]+']);
        //分享 视频  文章
        $api->post('videos/{post_id}/shares', 'VideosController@update')->name('api.video.update');
        $api->post('articles/{post_id}/shares', 'VideosController@update')->name('api.video.update');
        //评论视频
        $api->post('videos/{post_id}/comments', 'CommentsController@store')
            ->where(['post_id'=>'[0-9]+'])
            ->name('api.videos.commentsUpdate');
        // 评论文章
        $api->post('articles/{post_id}/comments', 'CommentsController@store')
            ->where(['post_id'=>'[0-9]+'])
            ->name('api.articles.commentsUpdate');
        //添加回复
        $api->post('comments/{comment_id}', 'CommentsController@replyStore')
            ->where(['comment_id' => '[0-9]+'])
            ->name('api.comments.replystore');
        //搜索用户
        $api->get('search/user', 'SearchController@searchByUser')->name('api.search.user');
        // 老师首页
        $api->get('teachers', 'TeachersController@index')->name('api.teachers.user');
        //文章详情
        $api->get('articles/{post_id}', 'ArticlesController@show')
            ->where(['post_id' => '[0-9]+'])
            ->name('api.articles.show');
        //学生首页
        $api->get('students', 'StudentsController@index')->name('api.students.user');
        // 答案首页
        $api->get('answers', 'AnswersController@index')->name('api.answers.index');
        // 写回答 
        $api->post('answers/{post_id}', 'AnswersController@store')
            ->where(['post_id' => '[0-9]+'])
            ->name('api.answers.store');
        // 问答详情
        $api->get('answers/{post_id}', 'AnswersController@show')
            ->where(['post_id' => '[0-9]+'])
            ->name('api.answers.show');
        // 提交问答评论
        $api->post('answercomments/{answer_id}', 'AnswerCommentsController@store')
            ->where(['post_id' => '[0-9]+'])
            ->name('api.answercomments.store');
        //提交问答评论的回复 
        $api->post('replyanswercomments/{comment_id}', 'AnswerCommentsController@replyStore')
            ->where(['post_id' => '[0-9]+'])
            ->name('api.answercomments.replyStore');
        // 关注首页
        $api->get('follows','FollowsController@index')->name('api.follows.index');
        // 我的视频
        $api->get('members/me/videos', 'VideosController@me')->name('api.videos.me');
        //我的文章
        $api->get('members/me/articles', 'ArticlesController@me')->name('api.articles.me'); 
        // 我的问题
        $api->get('members/me/questions', 'AnswersController@meQuestions')->name('api.answers.meQuestions'); 
        // 我的回答
        $api->get('members/me/answers', 'AnswersController@meAnswers')->name('api.answers.meAnswers'); 
        //游客信息-基本信息 
        $api->get('members/{member_id}', 'MembersController@show')
            ->name('api.member.show');
        //游客信息-我的视频 
        $api->get('members/{member_id}/videos', 'VideosController@showByMemberId')
            ->where(['member_id' => '[0-9]+'])
            ->name('api.videos.showByMemberId');
        //游客信息-我的文章
        $api->get('members/{member_id}/articles', 'ArticlesController@showByMemberId')
            ->where(['member_id' => '[0-9]+'])
            ->name('api.articles.showByMemberId');
        // 游客信息-我的问题
        $api->get('members/{member_id}/questions', 'AnswersController@showQuestionsByMemberId')
            ->where(['member_id' => '[0-9]+']);
        // 游客信息-我的回答
        $api->get('members/{member_id}/answers', 'AnswersController@showAnswersByMemberId')
            ->where(['member_id' => '[0-9]+']);
        // 我的关注
        $api->get('follows/me', 'FollowsController@showMe');
        // 我的粉丝 
        $api->get('fans/me', 'FansController@me');
        // 爵位信息
        $api->get('levels', 'LevelsController@index');
        // 我的收藏-视频
        $api->get('members/me/favorites/videos', 'FavoritesController@myVideos');
        // 我的收藏-文章
        $api->get('members/me/favorites/articles', 'FavoritesController@myArticles');
        // 编辑资料
        $api->patch('members/me', 'MembersController@updateMe');
        // 学历列表
        $api->get('educations', 'EducationsController@index');
        // 编辑头像
        $api->post('members/me/avatar', 'MembersController@avatarUpdate');
        // 意见反馈 
        $api->post('feedbacks', 'FeedbacksController@store');
        // 点赞消息
        $api->get('messages/likes', 'LikesController@index');
        // 点赞视频和文章
        $api->post('posts/likes/{post_id}', 'LikesController@store')
            ->where(['post_id'=> '[0-9]+']);
        $api->delete('posts/likes/{post_id}', 'LikesController@destroy')
            ->where(['post_id'=> '[0-9]+']);
        // 点赞文章和视频评论
        $api->post('likes/posts/comments/{comment_id}', 'LikesController@commentStore')
            ->where(['comment_id' => '[0-9]+']); 
        //取消点赞文章和视频评论
        $api->delete('likes/posts/comments/{comment_id}', 'LikesController@commentDestroy')
            ->where(['comment_id' => '[0-9]+']); 
        //答案点赞
        $api->post('likes/answers/{answer_id}', 'LikesController@AnswerStore')
            ->where(['answer_id' => '[0-9]+']); 
        //取消答案点赞
        $api->delete('likes/answers/{answer_id}', 'LikesController@AnswerDestroy')
            ->where(['answer_id' => '[0-9]+']); 
        // 答案评论点赞
        $api->post('likes/answercomments/{comment_id}', 'LikesController@answerCommentStore');
        // 取消答案评论点赞
        $api->delete('likes/answercomments/{comment_id}', 'LikesController@answerCommentDestroy');
        // 推送消息过来
        $api->get('messages', 'MessagesController@send');
        // 消息标记为已读取
        $api->patch('messages', 'MessagesController@update');
        // 测试
        $api->get('test', 'TestController@index');
        // 收藏
        $api->post('favorites/posts/{post_id}', 'FavoritesController@postStore'); 
        // 取消收藏
        $api->delete('favorites/posts/{post_id}', 'FavoritesController@postDestroy'); 
        // 消息详情列表
        $api->get('messages/list', 'MessagesController@index'); 
        // 支付宝签名订单
        $api->get('pays/alipay', 'PayController@index');
        // 置顶
        $api->post('topsearch/{post_id}', 'PayController@topsearchStore')
            ->where(['post_id' => '[0-9]+']);
        // 绑定手机号
        $api->put('members/me/phone', 'MembersController@updatePhone');
        //**************** 版本2接口*********//
        //获取资源评论
        $api->get('v2/comments/posts/{post_id}', 'CommentsController@postShow')
            ->where(['post_id' => '[0-9]+']);
        //获取资源评论下的回复
        $api->get('v2/comments/posts/{comment_id}/replies', 'CommentsController@postReplyShow')
            ->where(['post_id' => '[0-9]+']);
        //关注首页
        $api->get('v2/follows','FollowsController@getAll');
        //问题详情
        $api->get('v2/questions/{post_id}', 'AnswersController@version2show')
            ->where(['post_id' => '[0-9]+']);
        //答案列表
        $api->get('v2/questions/{question_id}/answers', 'AnswersController@answersShow')
            ->where(['question_id' => '[0-9]+']);
        //评论
        $api->get('v2/answercomments/{answer_id}', 'AnswersController@_getComments')
            ->where(['question_id' => '[0-9]+']);
        // 回复
        $api->get('v2/answercomments/{answer_id}/replies', 'AnswersController@repliesshow')
            ->where(['question_id' => '[0-9]+']) ;
    }); 
    // 支付宝回调请求 
    $api->post('pays/alipay/natify', 'PayController@notify');
    $api->get('pays/alipay/natify', 'PayController@notify');
    $api->post('natify', 'PayController@notify');
    $api->get('natify', 'PayController@notify');
    // 提交支付结果 
    $api->get('pays/alipay/return', 'PayController@return');
    //用户协议
    $api->get('agreement', 'ConfigsController@agreementShow');
    $api->get('disclaimer', 'ConfigsController@disclaimersShow');
});
Route::get('natify', 'PayController@notify');
