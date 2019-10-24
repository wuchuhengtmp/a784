<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Transformers\VideosTransformer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\PostVideoRequest;
use App\Models\{
    Posts,
    Comments,
    AccountLogs,
    MemberFollow,
    Levels,
    Images,
    PostImages,
    CommentLikes,
    Favorites,
    Members,
    PostLikes
};


class VideosController extends Controller
{
    /**
     *  首页视频分页
     *
     */
    public function index()
    {
        $Posts =  DB::table('posts')
            ->where('posts.content_type', 1)
            ->select(
                DB::raw("CONCAT(posts.sponsor_at, '-', posts.all_likes, '-', posts.created_at) AS order_weight,
                    posts.id, 
                    images.url,
                    (SELECT COUNT(*) FROM favorites WHERE favorites.post_id = posts.id ) AS favories_count,
                    posts.clicks,
                    posts.title,
                    posts.duration,
                    posts.video_url
                ")
                ) 
            ->whereNull('posts.deleted_at')
            ->leftJoin('post_image', 'post_image.post_id', '=', 'posts.id')
            ->leftJoin('images', 'post_image.image_id', '=', 'images.id')
            ->orderBy('order_weight', 'desc') 
            ->paginate(18);
        if ($Posts) {
            foreach($Posts as $el) {
                unset($el->order_weight);
                $el->url = $el->url && !isset(parse_url($el->url)['host']) ? env("APP_URL") .'/'. $el->url : $el->url;
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

    /**
    * 视频详情 
    *
    */
    public function show(Posts $Posts, $id)
    {
        $Post = $Posts->withCount(['comments'])
            ->where('content_type', 1)
            ->whereNull('deleted_at')
            ->with(['member'])
            ->find($id);
        if (!$Post) 
            return $this->response->errorNotFound();
        // :xxx bug 可能有url
         if (isset($Post->member->avatar->url))  {
            $url = $Post->member->avatar->url;
            if(!isset(parse_url($url)['host'])) {
               $Post->member->avatar->url = env('APP_URL')  . '/'  . $url;
            }
        } else {
               $Post->member->avatar->url = '';
        }
        $like_count = PostLikes::where('post_id', $id)->count();
        $myLikePosts = PostLikes::where('member_id', $this->user()->id)->get('post_id');
        $my_like_post_ids = $myLikePosts ? array_column($myLikePosts->toArray(), 'post_id') : [];
        $data =  [ 
            'id'              => $Post->id,
            'title'           => $Post->title,
            'member_id'       => $Post->member_id,
            'shares'          => $Post->shares,
            'video_url'       => $Post->video_url,
            'likes'           => $like_count,
            'total_commtents' => $Post->comments_count,
            'nickname'        => $Post->member->nickname,
            'avatar'          => $Post->member->avatar->url,
            'is_favorite'     => Favorites::isFavorite($this->user()->id, $Post->id),
            'is_like'         => in_array($Post->id, $my_like_post_ids),
            'is_follow'       => in_array($Post->member_id, MemberFollow::getFollowMemberIdsByMmberId($this->user()->id)),
        ];
        if ($url = $Post->images) {
            if($url = $url->toArray()[0]['url']) {
                if(!isset(parse_url($url)['host'])) {
                    $data['url'] = env('APP_URL')  . '/'  . $url;
                } else {
                    $data['url'] = $url;
                }
            }
        }
        DB::table('posts')->where('id', $id)->increment('clicks');
        return $this->responseData($data);
    }


    /**
    *  视频评论
    *
    *  @http    GET
    */
    public function comments(Request $Request)
    {
        $Comments = Comments::select(Db::raw(
            "CONCAT(comments.path, '-',  comments.id) AS order_weight, comments.*,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_likes.comment_id = comments.id ) AS likes_count
            "))
            ->where('post_id', $Request->id)
            ->orderBy('order_weight')
            ->with(['member'])
            ->get();
        $data = [];
        if ($Comments)  {
            foreach($Comments as $el){
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
                $tmp['likes']      = $el->likes_count;
                $tmp['is_like']    = CommentLikes::isLike($el->id, $this->user()->id);
                $tmp['is_author']  = $el->post->member_id  == $el->member_id ? true : false;
                $tmp['full_path']  = $el->order_weight;
                $data[]            = $tmp;
            }
        }
        $data = $this->_arrToTree($data); 
        $result['list'] = $data;
        $result['total'] = count($data);
        return $this->responseData($result);
    }
    
    /**
    *  视频上传
    *
    *  @http    POST
    */
    public function store(PostVideoRequest $Request, Posts $Post)
    {
       $data['video_url']    = $Request->video;
       $data['title']        = $Request->title;
       $data['tag_id']       = $Request->tag_id;
       $data['content_type'] = 1;
       $data['member_id']    = $this->user()->id;
       //获取时长
       $videoInfo = json_decode(file_get_contents($Request->video . '?avinfo'), true);
       $day_timestamp = strtotime(date('Y-m-d', time()));
       $data['duration'] = date('i:s', $day_timestamp + intval($videoInfo['streams'][0]['duration'])); 
       DB::beginTransaction();
       try{
           $Post = $Post::create($data); 
           $image = isset($Request->image) ? $Request->image : env('DEFAULT_IMG');
           $Image = Images::create([
               'url'=> $image,
               'from'=> 2
           ]);
           $PostsImage = Db::table('post_image')->insert([
               'post_id' => $Post->id,
               'image_id' =>  $Image->id
           ]);
           DB::commit();
       }catch(\Exception $e) {
           DB::rollBack();
           return $this->responseError('服务器内部错误');
       } 
       return $this->responseSuccess(); 
    }

    /**
     * 更新post表数据
     *
     * @http    patch
     * @post_id  资源id
     */
    public function update($post_id)
    {
        if (!$Post = Posts::find($post_id)) return $this->responseError('没有这个资源');
        DB::table('posts')->where('id', $post_id)->increment('shares');
        return $this->responseSuccess();
    }

    /**
     * 我的视频
     *
     * @http    GET 
     */ 
    public function me()
    {
        $data = [];
        $Posts = Posts::where('content_type', 1) 
                ->where('member_id', $this->user()->id)
                ->paginate(18);
        if ($Posts) {
            $tmp_data = [];
            foreach($Posts as $el) {
                $tmp['title'] = $el->title;
                $tmp['video_url'] = $el->video_url;
                $tmp['id'] = $el->id;
                $tmp['member_id'] = $el->member_id;
                $tmp['image'] = $this->transferUrl($el->images[0]->url);
                $tmp['clicks_count'] = $el->clicks;
                $tmp_data[] = $tmp;
            } 
            $data['data'] = $tmp_data;
            $data['count'] =  $Posts->total();
        }
        return $this->responseData($data);
    }

    /**
     * 用户的个人视频
     *
     * @http GET
     */
    public function showByMemberId(Request $Request)
    {
        if (!Members::find($Request->member_id))
            return $this->responseError('没有这个用户');
            $data = [];
            $Posts = Posts::where('content_type', 1) 
                    ->where('member_id', $Request->member_id)
                    ->paginate(18);
            if ($Posts) {
                $tmp_data = [];
                foreach($Posts as $el) {
                    $tmp['title']        = $el->title;
                    $tmp['video_url']    = $el->video_url;
                    $tmp['id']           = $el->id;
                    $tmp['member_id']    = $el->member_id;
                    $tmp['image']        = $this->transferUrl($el->images[0]->url);
                    $tmp['clicks_count'] = $el->clicks;
                    $tmp_data[]          = $tmp;
                } 
                $data['data'] = $tmp_data;
                $data['count'] =  $Posts->total();
            }
            return $this->responseData($data);
    }

    /**
     * 视频截图
     *
     */
    public function transferByUrl(Request $Request)
    {
        $Request->validate([
            'video_url' => 'required'
        ], [
            'video_url.required' => '视频链接参数video_url不能为空'
        ]);
        $ffmpeg = \FFMpeg\FFMpeg::create(array(
        'ffmpeg.binaries' => '/usr/bin/ffmpeg',
        'ffprobe.binaries' => '/usr/bin/ffprobe',
        'timeout' => 0, // The timeout for the underlying process
        'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
        ), @$logger);
        $video = $ffmpeg->open($Request->video_url);
        $microtime = (explode('.', microtime(true)))[1];
        $save_name = './uploads/frame_'. date("Y-m-d-H-i-s", time()) ."-{$microtime}".'.jpg';
        $result = $video
        ->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
        ->save($save_name);
        $file_handle = fopen($save_name, 'r');
        $file_content = fread($file_handle, filesize($save_name));
        // 上传到七牛
        $disk = Storage::disk('qiniu');
        $disk->put(substr($save_name, 1), $file_content);
        $CDNUrl = $disk->getUrl(substr($save_name, 1));
        return $this->responseData([
            'thumbnails'   => $CDNUrl
        ]);
    }
}
