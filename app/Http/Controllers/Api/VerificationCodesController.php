<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;
use App\Http\Requests\Api\VerificationCodeRequest;
use App\Http\Requests\Api\VerificationMemberPhoneRequest;

class VerificationCodesController extends Controller
{
    /**
     * 获取手机验证码
     * @http post
     * @return array
     */
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $code  = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
        $phone =  $request->phone;
        // 生成5位随机数，左侧补0
        $code = str_pad(random_int(1, 99999), 5, 0, STR_PAD_LEFT);

        try {
            $result = $easySms->send($phone, [
                'content' => "您的验证码是{$code}。如非本人操作，请忽略本短信"
            ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getException('diysms')->getMessage();
            Log::error($message);
            return $this->response->errorInternal($message ?: '短信发送异常');
        }
        $key = 'verificationCode_'.str_random(15);
        // 缓存验证码 10分钟过期。
        $expired_at = now()->addMinutes(10);
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expired_at);
        return $this->response->array([
            'key' => $key,
            'expired_at' => $expired_at->toDateTimeString(),
        ])->setStatusCode(201);
    }


    /**
    *  重置密码验证码
    *
    * @http post
    * @return array
    */   
    public function update(VerificationMemberPhoneRequest $request, EasySms $easySms)
    {
        $code  = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
        $phone =  $request->phone;
        // 生成5位随机数，左侧补0
        $code = str_pad(random_int(1, 99999), 5, 0, STR_PAD_LEFT);

        try {
            $result = $easySms->send($phone, [
                'content' => "您的验证码是{$code}。用于重置密码。如非本人操作，请忽略本短信"
            ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getException('diysms')->getMessage();
            Log::error($message);
            return $this->response->errorInternal($message ?: '短信发送异常');
        }
        $key = 'verificationCode_'.str_random(15);
        // 缓存验证码 10分钟过期。
        $expired_at = now()->addMinutes(10);
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expired_at);
        return $this->response->array([
            'key' => $key,
            'expired_at' => $expired_at->toDateTimeString(),
        ])->setStatusCode(201);
    }

}
