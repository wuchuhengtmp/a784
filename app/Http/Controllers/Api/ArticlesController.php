<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\PostArticleRequest;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Posts,
    PostImages,
    Images
};

class ArticlesController extends Controller
{
    /**
     *
     *  文章上传 
     */
    public function store(PostArticleRequest $Request)
    {
       DB::beginTransaction();
        try{
        $Post = Posts::create([
            'title'        => $Request->title,
            'tag_id'       => $Request->tag_id,
            'content'      => $Request->content,
            'content_type' => 2,
            'member_id'    => $this->user()->id,
        ]);
        // 单图保存
        if(count($Request->file('image')) > 0 && count($Request->file('image')) <=  2 )  {
            $Image = Images::create([
                'url'  => $this->DNSupload($Request->file('image.0')->store('public')),
                'from' => 2
            ]);
           $PostsImage = Db::table('post_image')->insert([
               'post_id'  => $Post->id,
               'image_id' => $Image->id
           ]);
        }
        //3图保存
        if(count($Request->file('image')) === 3)  {
            for($i=0; $i<=2; $i++) {
                $Image = Images::create([
                        'url'  => $this->DNSupload($Request->file('image.' . $i)->store('public')),
                        'from' => 2
                    ]);
                $PostsImage = Db::table('post_image')->insert([
                    'post_id'  => $Post->id,
                    'image_id' => $Image->id
                ]);
            }
        }
           DB::commit();
        }catch(\Exception $e) {
           DB::rollBack();
           return $this->responseError('服务器内部错误');
        }
       return $this->responseSuccess(); 
    }
}
