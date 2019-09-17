<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Posts
};

class StudentsController extends Controller
{
    /**
     *  首页老师分页
     *
     */
    public function index(Request $Request)
    {
        $Request->validate([
            'tag_id' => 'numeric'
        ]);
        $stage =  (new Posts())
            ->where('members.job', 1)
            ->select(
                DB::raw("CONCAT(posts.sponsor_at, '-', posts.all_likes, '-', posts.created_at) AS order_weight,
                    posts.id, 
                    posts.title,
                    members.nickname,
                    video_url,
                    content_type,
                    posts.created_at,
                    tag_id,
                    images.url as avatar
                ")
                ) 
            ->whereNull('posts.deleted_at');
            $Request->tag_id && $stage->where('tag_id', $Request->tag_id);
        $Posts = $stage->with(['images'=>function($query){
                    $query->select(['url']);
                }
            ])
            ->withCount(['comments'])
            ->join('members', 'members.id', '=', 'posts.member_id')
            ->join('post_image', 'post_image.post_id', '=', 'posts.id')
            ->join('images', 'images.id', '=', 'post_image.image_id')
            ->orderBy('order_weight', 'desc') 
            ->paginate(18);
        if ($Posts) {
            foreach($Posts as $el) {
                unset($el->order_weight);
                $el->avatar = $this->transferUrl($el->avatar);
                if($el->content_type != 1 ) unset($el->video_url);
                if($el->content_type == 3 ) unset($el->images);
            }
            $Posts = $Posts->toArray(); 
            $Posts['count'] = $Posts['total'];
            unset(
            $Posts['first_page_url'],
            $Posts['from'],
            $Posts['last_page'],
            $Posts['path'],
            $Posts['per_page'],
            $Posts['prev_page_url'],
            $Posts['to'],
            $Posts['next_page_url'],
            $Posts['total'],
            $Posts['current_page'],
            $Posts['last_page_url']
            );
        }
         return $this->responseData($Posts); 
    }

}
