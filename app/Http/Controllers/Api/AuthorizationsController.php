<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\Members;
use App\Models\Images;

class AuthorizationsController extends Controller
{
    /**
     * 用户授权
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
                    $Images = new Images();
                    $Images->url = $oauthUser->getAvatar();
                    $Images->from = 2;
                    $Images->create();
                    $Member->avatar_image_id = $Images->id;
                    $Member->save();
                    
                }
                break;
        }
        return $this->response->array(['token' => $member->id]);
    } 
}
