<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\MembersRequest;
use App\Http\Requests\Api\VerificationMemberInfoRequest ;
use App\Models\Members;
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
        return $this->response->item($this->user(), new MemberTransformer());
    }

    /**
     *  游客信息
     *
     */
    public function show(Members $Member, Request $Request)
    {
        $Member            = $Member->where('id', $Request->member_id)
            ->withCount([
                'postLikes',
                'commentLikes',
                'follows',
                'fans'
            ])
            ->first();
        if (!$Member) return $this->responseError();
        $data['id'] = $Member->id;
        $data['avatar']        = $this->transferUrl($Member->avatar->url);
        $data['nickname']      = $Member->nickname;
        $data['level']         = Members::getlevelInfoByMemberId($Member->id)->name ?? null;
        $data['sign']          = $Member->sign;
        $data['sex']           = $Member->sex;
        $data['job']           = $Member->job_name;
        $data['school']        = $Member->school;
        $data['education']     = $Member->education->name;
        $data['professional']  = $Member->professional;
        $data['likes']         = $Member->post_likes_count + $Member->comment_likes_count;
        $data['follows_count'] = $Member->follows_count;
        $data['fans_count']    = $Member->fans_count;
        return $this->responseData($data);
    }
}

