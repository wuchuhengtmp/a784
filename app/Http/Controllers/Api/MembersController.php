<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\MembersRequest;
use App\Http\Requests\Api\VerificationMemberInfoRequest ;
use App\Models\{
    Members,
    Posts,
    MemberFollow
};
use App\Transformers\MemberTransformer;

class MembersController extends Controller
{
    /**
     * 用户注册
     *
     * @http post
     */
    public function store(VerificationMemberInfoRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);
        if (!$verifyData) {
            return $this->response->error('验证码已失效', 422);
        }
        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回401
            return $this->response->errorUnauthorized('验证码错误');
        }
        $user = Members::create([
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);

        // 清除验证码缓存
        \Cache::forget($request->verification_key);
        return $this->responseSuccess();
    }


    /**
     * 更新用户信息
     *
     */
    public function update(VerificationMemberInfoRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);
        if (!$verifyData) {
            return $this->response->error('验证码已失效', 422);
        }
        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回401
            return $this->response->errorUnauthorized('验证码错误');
        }
        $Member = Members::where('phone', $verifyData['phone'])->first();
        $Member->password  = bcrypt($request->password);
        $Member->save();
        // 清除验证码缓存
        \Cache::forget($request->verification_key);
        return $this->responseSuccess();
    }

    /**
     *  用户个人信息
     *
     */
    public function me()
    {
        $Member = Members::where('id', $this->user()->id)
            ->with(['education'])
            ->withCount(['fans','commentLikes', 'follows' ])
            ->first();
        $Member->avatar_url = $this->transferUrl($Member->avatar->url);
        $hasLevel = Members::getlevelInfoByMemberId($this->user()->id);
        $Member->level = $hasLevel ? $hasLevel->name : null;
        $Member->education_level  = $Member->education->name ?? null;
        $data = $Member->makeHidden([
            'password',
            'email_verified_at',
            'remember_token',
            'region_id',
            'job',
            'phone_verified_at',
            'phone_verified_codea',
            'education_id',
            'status',
            'weixin_expires_in',
            'weixin_refresh_token',
            'weixin_access_token',
            'weixin_access_token_at',
            'weixin_openid',
            'weixin_unionid',
            'updated_at',
            'shares',
            'name',
            'avatar_image_id',
            'avatar',
            'education'
        ])->toArray();
        return $this->responseData($data);
    }

    /**
     *  游客信息
     *
     */
    public function show(Members $Member, Request $Request)
    {
        if (!Members::find($Request->member_id))
            return $this->responseError('没有这个用户');
        $MyFollowMembers = MemberFollow::where('member_id', $this->user()->id)->get('follow_member_id');
        $my_follow_member_ids = $MyFollowMembers ? array_column($MyFollowMembers->toArray(), 'follow_member_id') : [];
        $Member = Members::where('id', $Request->member_id)
            ->with(['education'])
            ->withCount(['fans','commentLikes', 'follows' ])
            ->first();
        $Member->is_follow = in_array($Member->id, $my_follow_member_ids);
        $Member->avatar_url = $this->transferUrl($Member->avatar->url);
        $hasLevel = Members::getlevelInfoByMemberId($this->user()->id);
        $Member->level = $hasLevel ? $hasLevel->name : null;
        $Member->education_level  = $Member->education->name ?? null;
        $data = $Member->makeHidden([
            'password',
            'email_verified_at',
            'remember_token',
            'region_id',
            'job',
            'phone_verified_at',
            'phone_verified_codea',
            'education_id',
            'status',
            'weixin_expires_in',
            'weixin_refresh_token',
            'weixin_access_token',
            'weixin_access_token_at',
            'weixin_openid',
            'weixin_unionid',
            'updated_at',
            'shares',
            'name',
            'avatar_image_id',
            'avatar',
            'education'
        ])->toArray();
        return $this->responseData($data);
    }
}

