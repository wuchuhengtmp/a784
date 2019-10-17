<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tokens;
use App\Models\Members;
use Illuminate\Http\Response;
class CheckToken
{
    /**
     * 单设备登录验证
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        $hasToken = Tokens::where('token', $token)
            ->where('status', 1)
            ->first();
        if (!$hasToken) {
            return response()->json([
                'message' => '您的账号已经在别的设备上登录了',
                'status_code' => 403,
            ]);
        } 
        $user = Members::find($hasToken->member_id);
        if (!$user) {
            return response()->json([
                'message' => '用户不存在',
                'status_code' => 403,
            ]);
        }
        if ($user['status'] == 0) {
            return response()->json([
                'message' => '您的账号已被禁用，如有疑问请联系管理员',
                'status_code' => 403,
            ]);

        }
        $response =  $next($request);
        if ($response->content()) {
            $json = preg_replace_callback(
                '|\d{4}-\d{1,2}-\d{1,2}\s\d{1,2}:\d{1,2}:\d{1,2}|',
                function ($time_str) {
                    $timestamp = strtotime($time_str[0]);
                    $result = '';
                    if (($timestamp + 60 ) > time()) {
                        $result = time() - $timestamp  . '秒';
                    } else if(($timestamp + 60 * 60) > time()){
                        $time_len = intval((time() - $timestamp) /60);
                        $result = $time_len . '分钟';
                    } else if (($timestamp + 60 * 60 * 24 ) > time()) {
                        $time_len = intval((time() - $timestamp) / (60 * 60));
                        $result = $time_len . '小时';
                    } else if(($timestamp + 60 * 60 * 24 * 31) > time()) {
                        $time_len = intval((time() - $timestamp) / (60 * 60 * 24));
                        $result = $time_len . '天';
                    } else if (($timestamp + 60 * 60 * 24 * 365) > time()) {
                        $time_len = (time() - $timestamp ) / (60 * 60 * 24 * 31);
                        $result = $time_len . '月';
                    }
                    return $result . '前';
                },
                    $response->content()
                );
            $response->original = json_decode($json, true);
        }
        return $response;
    }
}
