<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Posts;
use App\Transformers\VideosTransformer;
use Illuminate\Support\Facades\DB;
use App\Models\Comments;
use App\Models\AccountLogs;
use App\Models\MemberFollow;
use App\Models\Levels;
use App\Models\Images;
use App\Models\PostImages;
use App\Models\CommentLikes;
use App\Http\Requests\Api\PostVideoRequest;

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
                    posts.title
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
        
        if ($url = $Post->member->avatar->url)  {
            if(!isset(parse_url($url)['host'])) {
               $Post->member->avatar->url = env('APP_URL')  . '/'  . $url;
            }
        }
        $data =  [
            'id'              => $Post->id,
            'title'           => $Post->title,
            'member_id'       => $Post->member_id,
            'shares'          => $Post->shares,
            'video_url'       => $Post->video_url,
            'likes'           => $Post->all_likes,
            'total_commtents' => $Post->comments_count,
            'nickname'        => $Post->member->nickname,
            'avatar'          => $Post->member->avatar->url,
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
            ->orderBy('order_weight')
            ->with(['member'])
            ->get();
        $data = [];
        if ($Comments)  {
            foreach($Comments as $el){
                $tmp['nickname'] = $el->member->nickname;
                $tmp['avatar']   = $this->transferUrl($el->member->avatar->url);
                $money = AccountLogs::getMaxBetweenTimeByUid($el->member->id,  time() - 60*60*24*365);
                $fans = MemberFollow::countFansBYUid($el->member->id);
                $has_level = Levels::getLevelByFansAndMony($fans, $money);
                $tmp['id'] = $el->id;
                $tmp['pid'] = $el->pid;
                $tmp['level']  = $has_level ? $has_level->name : null;
                $tmp['content'] = $el->content;
                $tmp['created_at'] = $el->created_at->toArray()['formatted'];
                $tmp['likes'] = $el->likes_count;
                $tmp['is_like'] = CommentLikes::isLike($el->id, $this->user()->id);
                $tmp['full_path'] = $el->order_weight;
                $data[] = $tmp;
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
       $path = $Request->file('video')->store('public');
       $data['video_url']    = $this->DNSupload($path);
       $data['title']        = $Request->title;
       $data['tag_id']       = $Request->tag_id;
       $data['content_type'] = 1;
       $data['member_id']    = $this->user()->id;
       DB::beginTransaction();
       try{
           $Post = $Post::create($data); 
           $Image = Images::create([
               'url'=> $this->DNSupload($Request->file('image')->store('public')),
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
     * 将数组遍历为数组树
     * @arr     有子节点的目录树
     * @tree    遍历赋值的树
     * @return  array
     *
     */
    protected function _arrToTree($items, $pid = 'pid')
    {
         $map  = [];
         $tree = [];
         foreach ($items as &$it){
           $el = &$it;
           $map[$it['id']] = &$it;
         }  //数据的ID名生成新的引用索引树
         foreach ($items as &$it){
           $parent = &$map[$it[$pid]];
           if($parent) {
             $parent['children'][] = &$it;
           }else{
             $tree[] = &$it;
           }
         }
         return $tree;
    }
}
