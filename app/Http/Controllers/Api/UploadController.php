<?php

namespace App\Http\Controllers\Api;
use Qiniu\Auth;

class UploadController extends Controller
{
    /**
     * 获取七牛token
     * 
     * @http  GET
     */
    public function qiniuToken()
    {
        $bucket = env('QINIU_BUCKET');
        $accessKey = env('QINIU_ACCESS_KEY');
        $secretKey = env('QINIU_ACCESS_KEY');
        $auth = new Auth($accessKey, $secretKey);
        $upToken = $auth->uploadToken($bucket);
        return $this->responseData(['toke'=>$upToken]);

    }
}
