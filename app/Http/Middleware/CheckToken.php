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
        return $next($request);
    }
}
