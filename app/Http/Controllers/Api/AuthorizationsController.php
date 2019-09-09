<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\Members;
use App\Models\Images;
use App\Http\Requests\Api\AuthorizationRequest;

class AuthorizationsController extends Controller
{
    /**
     * 微信登录授权
     *
     */
    public function socialStore($type, SocialAuthorizationRequest $request)
    {
        if (!in_array($type, ['weixin'])) {
            return $this->response->errorBadRequest();
        }

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
                    $Member = new Members();
                    $Member->nickname = $oauthUser->getNickname();
                    $Member->weixin_openid = $oauthUser->getId();
                    $Member->weixin_unionid = $unionid;
                    $Member->weixin_access_token = $token;
                    $Images = Images::create([
                        'from' => 2,
                        'url'  => $oauthUser->getAvatar()
                    ]);
                    $Member->avatar_image_id = $Images->id;
                    $Member->save();
                }
                break;
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

            return $this->response->errorUnauthorized('用户名或密码错误');
        }

        return $this->respondWithToken($token);
    }


    /**
     * 生成token
     *
     */
    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
