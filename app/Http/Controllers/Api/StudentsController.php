<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Posts,
    Images,
    PostImages
};

class StudentsController extends Controller
{
    /**
     *  学生首页
     *
     */
    public function index(Request $Request)
    {
        $result= [];
        $Request->validate([
            'tag_id' => 'numeric'
        ]);
        if ($Request->tag_id) {
            $stage = (new Posts())->where('posts.tag_id', $Request->tag_id);
        } else {
            $stage = (new Posts());
        }
        $Posts= $stage->where('members.job', 1)
            ->select(
                DB::raw("CONCAT(posts.sponsor_at, '-', posts.all_likes, '-', posts.created_at) AS order_weight,
                posts.*
                ")
                ) 
            ->whereIn('posts.content_type', [1,2])
            ->whereNull('posts.deleted_at')
            ->with(['images'])
            ->withCount(['comments'])
            ->join('members', 'members.id', '=', 'posts.member_id')
            ->orderBy('order_weight', 'desc') 
            ->paginate(18);
        if ($Posts) {
            $data = [];
            foreach($Posts as $el) {
                $tmp = [];
                $tmp['id']              = $el->id;
                $tmp['title']           = $el->title;
                $tmp['title']           = $el->title;
                $tmp['nickname']        = $el->member->nickname ?? '';
                $tmp['content_type']    = $el->content_type;
                $tmp['created_at']      = $el->created_at->toDateTimeString();
                $tmp['tag_id']          = $el->tag_id;
                $tmp['avatar']          = $el->member->avatar->url ??  env('DEFAULT_AVATAR');
                $tmp['comments_count']  = $el->comments_count ;
                $tmp['images']          = array_map(function($image) {
                    return $this->transferUrl($image['url']);
                }, $el->images->toArray());
                if ($el->content_type  == 1) {
                    $tmp['video_url'] = $el->video_url;
                    $tmp['duration'] = $el->duration;
                }
                if ($el->content_type == 2) {
                    $tmp['content'] = $el->content;
                }
                $data[] = $tmp;
            }
            $result['data'] = $data;
            $result['count']  = $Posts->total();
        }
         return $this->responseData($result); 
    }

}
