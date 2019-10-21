<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\PostArticleRequest;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Posts,
    PostImages,
    Images,
    AccountLogs,
    Levels,
    CommentLikes,
    Members,
    Favorites,
    PostLikes,
    Comments,
    MemberFollow 
};

class ResourceController extends Controller
{
    /**
     *
     *  图片视频上传 
     */
    public function store(Request $Request)
    {
        $Request->validate([
            'resource' => 'required|file'
        ]);
        $url = $this->DNSupload($Request->file('resource')->store('public'));
       return $this->responseData(['url'=> $url]); 
    }
}
