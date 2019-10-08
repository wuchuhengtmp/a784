<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Posts
};

class TeachersController extends Controller
{
    /**
     *  首页老师分页
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
        $Posts= $stage->where('members.job', 2)
            ->whereIn('posts.content_type', [1,2])
            ->whereNull('posts.deleted_at')
            ->with(['images'])
            ->withCount(['comments'])
            ->join('members', 'members.id', '=', 'posts.member_id')
            ->orderBy('posts.created_at', 'desc') 
            ->paginate(18);
        if ($Posts) {
            $data = [];
            foreach($Posts as $el) {
                $tmp['id'] = $el->id;
                $tmp['title'] = $el->title;
                $tmp['nickname'] = $el->nickname;
                $tmp['content_type'] = $el->content_type;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['tag_id']  = $el->tag_id;
                $tmp['avatar']  = $el->member->avatar->url ??  '';
                $tmp['comments_count'] = $el->comments_count ;
                $tmp['images']         = array_map(function($image) {
                    return $this->transferUrl($image['url']);
                }, $el->images->toArray());
                if ($el->content_type == 1) $tmp['video_url'] = $el->video_url;
                $data[]  = $tmp;
            }
            $result['data'] = $data;
            $result['count']  = $Posts->total();
        }
         return $this->responseData($result); 
    }

}
