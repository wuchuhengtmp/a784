<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\{
    Posts
};

class PostController extends Controller
{
    /**
     *  删除资源
     *
     */
    public function desctroy(Request $Request, $post_id)
    {
        $Post = Posts::where('id', $post_id)
            ->where('member_id', $this->user()->id)
            ->first();
        if (!$Post) {
            return $this->responseFailed('没有这个资源');
        }
        if ($Post->delete()) {
            return $this->responseSuccess();
        } else {
            return $this->responseFailed('删除失败');
        }
    }
}
