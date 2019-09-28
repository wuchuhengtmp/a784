<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tokens;
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
        return $next($request);
    }
}
