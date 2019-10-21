<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\PostQuestionRequest;
use App\Models\Posts;

class QuestionController extends Controller
{

    /**
     *  问答上传
     *
     */
    public function store(PostQuestionRequest $Request)
    {
       $data['title']        = $Request->title;
       $data['tag_id']        = $Request->tag_id;
       $data['member_id']    = $this->user()->id;
       $data['content_type'] = 3;
       $Post = Posts::create($data); 
       if (!$Post)
           return $this->responseError('服务器内部错误');
        else 
            return $this->responseSuccess(); 
    }
}
