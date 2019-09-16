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
    MemberFollow,
    Levels,
    CommentLikes
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

    /**
     *  文章详情
     *
     */
    public function show(Request $Request)
    {
        $Posts = Posts::where('content_type', 2)
            ->where('id', $Request->post_id)
            ->with(['member','comments', 'images' => function($query) {
                $query->select('url');
            }])
            ->first();
        if (!$Posts) 
            return $this->responseError('您请求的是文章详情，而没有这个参数id的文章，请检查');
        $data['id']         = $Posts->id;
        $data['title']      = $Posts->title;
        $data['content']    = $Posts -> content;
        $data['created_at'] = $Posts->created_at->toDateTimeString();
        $data['nickname']   = $Posts->member->nickname;
        $data['avatar']     = $this->transferUrl($Posts->member->avatar->url);
        $data['images']     = $Posts->images->toArray();
        $data['comments']   = null;
        if ($Posts->comments){
            foreach($Posts->comments as $el){
                $tmp['nickname']   = $el->member->nickname;
                $tmp['avatar']     = $this->transferUrl($el->member->avatar->url);
                $money             = AccountLogs::getMaxBetweenTimeByUid($el->member->id,  time() - 60*60*24*365);
                $fans              = MemberFollow::countFansBYUid($el->member->id);
                $has_level         = Levels::getLevelByFansAndMony($fans, $money);
                $tmp['id']         = $el->id;
                $tmp['pid']        = $el->pid;
                $tmp['level']      = $has_level ? $has_level->name : null;
                $tmp['content']    = $el->content;
                $tmp['created_at'] = $el->created_at->toArray()['formatted'];
                $tmp['likes']      = CommentLikes::where('id', $el->id)->count();
                $tmp['is_like']    = CommentLikes::isLike($el->id, $this->user()->id);
                $tmp['full_path']  = $el->order_weight;
                $tmp['is_author']  = $el->post_id == $Request->post_id ? true : false;
                $data['comments'][]  = $tmp;
            }
            $data['comments'] = $this->_arrToTree($data['comments']);
        }
        return $this->responseData($data);
    }

}
