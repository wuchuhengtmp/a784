<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    Favorites,
    Posts
};

class FavoritesController extends Controller
{
    /**
     *  我的收藏-视频 
     *
     */
    public function myVideos()
    {
        $data = []; 
        $Favorites = Favorites::where('favorites.member_id', $this->user()->id)
            ->where('posts.content_type', 1)
            ->join('posts', 'posts.id', '=', 'favorites.post_id')  
            ->with(['post' => function($query) {
                $query->withCount(['favorites']);
            }])
            ->paginate(18);
        if (!$Favorites->isEmpty()) {
            foreach($Favorites as $el) {
                    $tmp['id']              = $el->post->id;
                    $tmp['title']           = $el->post->title;
                    $tmp['video_url']       = $el->post->video_url;
                    $tmp['image']           = $el->post->images[0]->url ? $this->transferUrl($el->post->images[0]->url) : env('DEFAULT_AVATAR');
                    $tmp['clicks_count']    = $el->post->clicks;
                    $tmp['favorites_count'] = $el->post->favorites_count;
                    $data['data'][]                 = $tmp;
            }
            $data['count'] = $Favorites->total();
        }
        return $this->responseData($data);
    }


    /**
     * 我的收藏-文章
     *
     * @http    GET
     */
    public function myArticles()
    {
        $data = []; 
        $Favorites = Favorites::where('member_id', $this->user()->id)
            ->with(['post' => function($query) {
                $query->withCount(['comments']);
            }])
            ->paginate(18);
        if (!$Favorites->isEmpty()) {
            foreach($Favorites as $el) {
                if ($el->post->content_type == 2 && $el->post->deleted_at == null) {
                    $tmp['id']             = $el->post->id;
                    $tmp['title']          = $el->post->title;
                    $tmp['nickname']       = $el->post->member->nickname;
                    $tmp['created_at']     = $el->created_at->toDateTimeString();
                    $tmp['images']         = array_map(function($arr_el){
                                                    return $this->transferUrl($arr_el['url'] );
                                                }, $el->post->images->toArray());
                    $tmp['clicks_count']   = $el->post->clicks;
                    $tmp['comments_count'] = $el->post->comments_count;
                    $data['data'][]        = $tmp;
                }
            }
            $data['count'] = $Favorites->total();
        }
        return $this->responseData($data);
    }

    /**
     * 收藏资源
     * 
     * @http    POST
     */
    public function postStore(Request $Request)
    {
        $hasPost = Posts::where('id', $Request->post_id)->first();
        if (!is_object($hasPost)) {
            return $this->responseError('没有这个资源');
        }
        $hasFavorite = Favorites::where('post_id', $Request->post_id)
            ->where('member_id', $this->user()->id)
            ->first();
        if($hasFavorite) return $this->responseError('已经收藏这个资源了');
        $result = Favorites::create([
            'member_id' => $this->user()->id,
            'post_id' => $Request->post_id
        ]);
        if ($result) {
            return $this->responseSuccess();
        } else {
            return $this->responseError();
        }
    }

    /**
     *  取消收藏
     *
     */
    public function postDestroy(Request $Request)
    {
        $hasFavorite = Favorites::where('post_id', $Request->post_id)
            ->first();
        if($hasFavorite) {
            $hasFavorite->delete();
            return $this->responseSuccess();
        } else {
            return $this->responseError('并没有收藏');
        }
    }
}

