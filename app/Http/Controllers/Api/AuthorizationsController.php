<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\Members;
use App\Models\Images;
use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\VerificationMemberInfoRequest;

class AuthorizationsController extends Controller
{
    /**
     * 微信和手机验证码登录授权
     *
     */
    public function socialStore($type, SocialAuthorizationRequest $request)
    {  
        if (!in_array($type, ['weixin', 'code'])) {
            return $this->response->errorBadRequest();
        }
        if (in_array($type, ['weixin'])) {
            $driver = \Socialite::driver($type);
            try {
                if ($code = $request->code) {
                    $response = $driver->getAccessTokenResponse($code);
                    $token = array_get($response, 'access_token');
                } else {
                    $token = $request->access_token;

                    if ($type == 'weixin') {
                        $driver->setOpenId($request->openid);
                    }
                }
                $oauthUser = $driver->userFromToken($token);
            } catch (\Exception $e) {
                return $this->response->errorUnauthorized('参数错误，未获取用户信息');
            }
        }
        switch ($type) {
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
                if ($unionid) {
                    $member = Members::where('weixin_unionid', $unionid)->first();
                } else {
                    $member = Members::where('weixin_openid', $oauthUser->getId())->first();
                }
                // 没有用户，默认创建一个用户
                if (!$member) {
                    $member = new Members();
                    $member->nickname = $oauthUser->getNickname();
                    $member->weixin_openid = $oauthUser->getId();
                    $member->weixin_unionid = $unionid;
                    $member->weixin_access_token = $token;
                    $Images = Images::create([
                        'from' => 2,
                        'url'  => $oauthUser->getAvatar()
                    ]);
                    $member->avatar_image_id = $Images->id;
                    $member->save();
                }
                break;
            case 'code' :
                $verifyData = \Cache::get($request->verification_key);
                if (!$verifyData) {
                    return $this->response->error('验证码已失效', 422);
                }
                if (!hash_equals($verifyData['code'], $request->verification_code)) {
                    // 返回401
                    return $this->response->errorUnauthorized('验证码错误');
                }
                // 清除缓存
                env('APP_DEBUG') || \Cache::forget($request->verification_key);
                $member = Members::where('phone', $verifyData['phone'])->first();
                if (!$member) {
                    $member = Members::create(['phone'=> $verifyData['phone']]);  
                }
        }
        $token=\Auth::guard('api')->fromUser($member);
        return $this->respondWithToken($token);
    }


    /**
     * 手机登录授权
     *
     *
     */
    public function store(AuthorizationRequest $request)
    {
        $credentials['phone'] = $request->phone;
        $credentials['password'] = $request->password;

        if (!$token = auth('api')->attempt($credentials)) {
            return $this->response()->array([
                'message' => '密码或用户名错误',
                'status_code'  => 422,
                'data'  => []
            ], 200);
        }

        return $this->respondWithToken($token);
    }


    /**
     * 获取微信openid
     *
     */
    public function getAppid()
    {
        return $this->responseData(['appid' => env("WEIXIN_KEY")]);
    }


    /**
     * 生成token
     *
     */
    protected function respondWithToken($token)
    {
        return $this->responseData([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * 刷新token
     *
     */
    public function update()
    {
        $token = \Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    public function destroy()
    {
        \Auth::guard('api')->logout();
        return $this->responseSuccess();
    }
}
