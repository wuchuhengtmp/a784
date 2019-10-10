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
    Levels,
    CommentLikes,
    Members,
    Favorites,
    PostLikes,
    Comments,
    MemberFollow 
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
        if(!$Request->file('image3') )  {
            $Image = Images::create([
                'url'  => $Request->image1,
                'from' => 2
            ]);
           $PostsImage = Db::table('post_image')->insert([
               'post_id'  => $Post->id,
               'image_id' => $Image->id
           ]);
        }
        //3图保存
        if($Request->file('image1') && $Request->file('image3'))  {
            for($i=1; $i<=3; $i++) {
                $Image = Images::create([
                    'url'  => $Request->image . $i,
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
        $myFollowMembers     = MemberFollow::where('member_id', $this->user()->id)->get('follow_member_id');
        $my_follow_ids       = $myFollowMembers ?  array_column($myFollowMembers->toArray(), 'follow_member_id') : [];
        $myLikePosts         = PostLikes::where('member_id', $this->user()->id)->get('post_id');
        $my_like_post_ids    = $myLikePosts ? array_column($myLikePosts->toArray(), 'post_id') : [];
        $myFavories          = Favorites::where('member_id', $this->user()->id)->get('post_id');
        $my_favorie_ids      = $myFavories ? array_column($myFavories->toArray(), 'post_id') : [];
        $data['id']          = $Posts->id;
        $data['member_id']   = $Posts->member_id;
        $data['title']       = $Posts->title;
        $data['content']     = $Posts -> content;
        $data['comment_count'] = Comments::where('pid', 0)->where('post_id', $Request->post_id)->count();
        $data['created_at']  = $Posts->created_at->toDateTimeString();
        $data['nickname']    = $Posts->member->nickname;
        $data['avatar']      = isset($Posts->member->avatar->url) ? $this->transferUrl($Posts->member->avatar->url) : '';
        $data['is_follow']   = in_array($Posts->member_id, $my_follow_ids);
        $data['is_like']     = in_array($Posts->id, $my_like_post_ids);
        $data['is_favorite'] = in_array($Posts->id, $my_favorie_ids);
        $data['images']      = array_map(function($el){
            return $el['url'];
        } ,$Posts->images->toArray());
        $data['comments']    = null;
        if ($Posts->comments){
            $comments  = Comments::where('post_id', $Request->post_id)
                ->select(DB::raw(
                    "CONCAT(comments.path, '-',  comments.id) AS order_weight, comments.*"
                ))
                ->orderBy('order_weight')
                ->get();
            foreach($comments as $el){
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
                $tmp['likes']      = CommentLikes::where('comment_id', $el->id)->count();
                $tmp['is_like']    = CommentLikes::isLike($el->id, $this->user()->id);
                $tmp['full_path']  = $el->order_weight;
                $tmp['is_author']  = $el->post_id == $Request->post_id ? true : false;
                $data['comments'][]  = $tmp;
            }
            $data['comments'] = $data['comments'] ? $this->_arrToTree($data['comments']) : [];
        }
        return $this->responseData($data);
    }

    /** 
     * 我的文章
     *
     * @http  GET
     *
     */
    public function me()
    {
        $data = [];
        $Posts = Posts::where('content_type', 2) 
                ->where('member_id', $this->user()->id)
                ->withCount(['comments'])
                ->orderBy('created_at', 'desc')
                ->paginate(18);
        if ($Posts) {
            $tmp_data = [];
            foreach($Posts as $el) {
                $tmp['id'] = $el->id;
                $tmp['title'] = $el->title;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['images'] = array_map(function($el){
                    return $this->transferUrl($el['url']);
                }, $el->images->toArray());
                $tmp['comments_count'] = $el->comments_count;
                $tmp_data[] = $tmp;
            } 
            $data['data'] = $tmp_data;
            $data['count'] =  $Posts->total();
        }
        return $this->responseData($data);
    }

    /**
     * 游客信息-我的文章
     *
     * @http GET
     *
     */
    public function showByMemberId(Request $Request)
    {
        if (!Members::find($Request->member_id))
            return $this->responseError('没有这个用户');
        $data = [];
        $Posts = Posts::where('content_type', 2) 
                ->where('member_id', $Request->member_id)
                ->withCount(['comments'])
                ->orderBy('created_at', 'desc')
                ->paginate(18);
        if ($Posts) {
            $tmp_data = [];
            foreach($Posts as $el) {
                $tmp['id'] = $el->id;
                $tmp['title'] = $el->title;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['images'] = array_map(function($el){
                    return $this->transferUrl($el['url']);
                }, $el->images->toArray());
                $tmp['comments_count'] = $el->comments_count;
                $tmp_data[] = $tmp;
            } 
            $data['data'] = $tmp_data;
            $data['count'] =  $Posts->total();
        }
        return $this->responseData($data);
    }


    /**
     * 文章详情V2
     *
     */
    public function version2show(Request $Request)
    {
        $Posts = Posts::where('content_type', 2)
            ->where('id', $Request->post_id)
            ->with(['member','comments', 'images' => function($query) {
                $query->select('url');
            }])
            ->first();
        if (!$Posts) 
            return $this->responseError('您请求的是文章详情，而没有这个参数id的文章，请检查');
        $myFollowMembers     = MemberFollow::where('member_id', $this->user()->id)->get('follow_member_id');
        $my_follow_ids       = $myFollowMembers ?  array_column($myFollowMembers->toArray(), 'follow_member_id') : [];
        $myLikePosts         = PostLikes::where('member_id', $this->user()->id)->get('post_id');
        $my_like_post_ids    = $myLikePosts ? array_column($myLikePosts->toArray(), 'post_id') : [];
        $myFavories          = Favorites::where('member_id', $this->user()->id)->get('post_id');
        $my_favorie_ids      = $myFavories ? array_column($myFavories->toArray(), 'post_id') : [];
        $data['id']          = $Posts->id;
        $data['member_id']   = $Posts->member_id;
        $data['title']       = $Posts->title;
        $data['content']     = $Posts -> content;
        $data['created_at']  = $Posts->created_at->toDateTimeString();
        $data['nickname']    = $Posts->member->nickname;
        $data['avatar']      = $this->transferUrl($Posts->member->avatar->url);
        $data['is_follow']   = in_array($Posts->member_id, $my_follow_ids);
        $data['is_like']     = in_array($Posts->id, $my_like_post_ids);
        $data['is_favorite'] = in_array($Posts->id, $my_favorie_ids);
        $data['images']      = $Posts->images->toArray();
        return $this->responseData($data);
    }
}
