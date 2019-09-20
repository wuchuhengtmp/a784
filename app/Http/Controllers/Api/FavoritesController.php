<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Favorites;

class FavoritesController extends Controller
{
    /**
     *  我的收藏-视频 
     *
     */
    public function myVideos()
    {
        $data = []; 
        $Favorites = Favorites::where('member_id', $this->user()->id)
            ->with(['post' => function($query) {
                $query->withCount(['favorites']);
            }])
            ->paginate(18);
        if ($Favorites) {
            foreach($Favorites as $el) {
                if ($el->post->content_type == 1) {
                    $tmp['id']              = $el->post->id;
                    $tmp['title']           = $el->post->title;
                    $tmp['video_url']       = $el->post->video_url;
                    $tmp['image']           = $this->transferUrl($el->post->images[0]->url);
                    $tmp['clicks_count']    = $el->post->clicks;
                    $tmp['favorites_count'] = $el->post->favorites_count;
                    $data['data'][]                 = $tmp;
                }
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
        if ($Favorites) {
            foreach($Favorites as $el) {
                if ($el->post->content_type == 2 && $el->post->deleted_at == null) {
                    $tmp['id']             = $el->id;
                    $tmp['title']          = $el->post->title;
                    $tmp['nickname']       = $el->post->member->nickname;
                    $tmp['created_at']     = $el->created_at->toDateTimeString();
                    $tmp['images']         = array_map(function($arr_el){
                                                    return $this->transferUrl($arr_el['url'] );
                                                }, $el->post->images->toArray());
                    $tmp['clicks_count']   = $el->post->clicks;
                    $data['data'][]        = $tmp;
                }
            }
            $data['count'] = $Favorites->total();
        }
        return $this->responseData($data);
    }
}

