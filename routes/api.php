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
    $api->group(['middleware' => 'api.auth'], function($api) {
        // 首页视频分页
        $api->get('videos', 'VideosController@index')->name('api.video.index');
        //视频详情
        $api->get('videos/{id}', 'VideosController@show')->name('api.video.show');
        // 单个资源的评论
        $api->get('videos/comments/{id}', 'VideosController@Comments')->name('api.comments.show');
        // 当前登录用户信息
        $api->get('member', 'MembersController@me')->name('api.member.me');
        // 游客信息
        $api->get('members/{member_id}', 'MembersController@show')->name('api.member.show');
        //视频上传
        $api->post('videos', 'VideosController@store')->name('api.video.store');
        //文章上传
        $api->post('articles', 'ArticlesController@store')->name('api.article.store');
        //问答上传
        $api->post('questions', 'QuestionController@store')->name('api.question.store');
        // 分类标签
        $api->get('tags', 'TagsController@index')->name('api.tags.index');
        // 他（她）的关注
        $api->get('follows/{member_id}', 'FollowsController@show')->name('api.follows.show');
        //关注他（她）
        $api->post('follows/{member_id}', 'FollowsController@store')->name('api.follows.store');
        // 他（她）的粉丝
        $api->get('fans/{member_id}', 'FansController@show')->name('api.fans.show');
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
        //点赞回答
        $api->post('answers/{answer_id}/likes', 'AnswersController@likeAnswer')
            ->where(['answer_id' => '[0-9]+'])
            ->name('api.answers.likeAnswer');
        // 点赞回答评论
        $api->post();
        gitgnore
    }); 

});
