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
    'namespace' => 'App\Http\Controllers\Api'
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
        // 当前登录用户信息
        $api->get('member', 'MembersController@me')
            ->name('api.member.show');
    });
});
