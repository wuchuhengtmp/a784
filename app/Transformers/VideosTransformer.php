<?php

namespace App\Transformers;

use App\Models\Posts;
use League\Fractal\TransformerAbstract;

class VideosTransformer extends TransformerAbstract
{
    public function transform(Posts $videos)
    {
        if ($url = $videos->member->avatar->url)  {
            if(!isset(parse_url($url)['host'])) {
               $videos->member->avatar->url = env('APP_URL')  . '/'  . $url;
            }
        }
        /* if ($url = )  { */
        
        /* } */
        /* dd($Post->images[0]->url); */
        return [
            'id' => $videos->id,
            'title'   => $videos->title,
            'shares'  => $videos->shares,
            'likes'  => $videos->all_likes,
            'total_commtents' => $videos->comments_count,
            'nickname' => $videos->member->nickname,
            'avatar'   => $videos->member->avatar->url,
            /* 'url'   => $videos->images->url, */
        ];
    }
}
