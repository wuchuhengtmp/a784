<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\MembersRequest;
use App\Http\Requests\Api\VerificationMemberInfoRequest ;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\UpdateMeRequest;
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
            'nickname' => '用户_' . rand(1, 999),
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
            ->withCount(['fans','commentLikes', 'follows'])
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

    /**
     * 编辑我的资料
     *
     *  @http PATCH
     */
    public function updateMe(UpdateMeRequest $Request)
    {
        $Request->nickname        && $input['nickname']        = $Request->nickname;
        $Request->sign            && $input['sign']            = $Request->sign;
        $Request->sex             && $input['sex']             = $Request->sex;
        $Request->age             && $input['age']             = $Request->age;
        $Request->job             && $input['job']             = $Request->job;
        $Request->born            && $input['born']            = $Request->born;
        $Request->weixin          && $input['weixin']          = $Request->weixin;
        $Request->school          && $input['school']          = $Request->school;
        $Request->department      && $input['department']      = $Request->department;
        $Request->professional    && $input['professional']    = $Request->professional;
        $Request->education_id    && $input['education_id']    = $Request->education_id;
        $Request->email           && $input['email']           = $Request->email;
        $Request->start_school_at && $input['start_school_at'] = $Request->start_school_at;
        $Request->hobby           && $input['hobby']           = $Request->hobby;
        $Request->password        && $input['password']        = bcrypt($Request->password);
        if (!$input) return  $this->responseError('请输入参数');

        $is_save = DB::table('members')
            ->where('id', $this->user()->id)
            ->update($input);
        if ($is_save ) 
            return $this->responseSuccess();
        else 
            return $this->responseError('更新失败，您提交的内容没有进行任何变动');
            
            
    }

    /**
     * 修改头像
     *
     * @http POST
     */
    public function avatarUpdate(Request $Request)
    {
        $Member = Members::with(['avatar'])->where('id', $this->user->id)->first();
        $Avatar = $Member->avatar;
        $Avatar->url = $this->DNSupload($Request->file('avatar')->store('public'));
        $is_save = $Avatar->save();
        return  $is_save ? $this->responseSuccess() : $this->responseError();
            
    }
}

