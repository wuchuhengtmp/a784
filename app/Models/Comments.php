<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Posts;
use App\Models\Members;

class Comments extends Model
{
    use  SoftDeletes;
    /**
     *  关联用户信息
     *
     */
    public function member()
    {
       return $this->hasOne(Members::class, 'id', 'member_id'); 
    }

    /**
     *  关联到发布资源
     *
     */
    public function post()
    {
        return $this->hasOne(Posts::class, 'id', 'post_id'); 
    }


    /**
     * 获取用户被评论量
     * 
     *
     * @id         用户id
     * @return     评论量   
     */
    public static function countCommentByMemberid(int $id)  : int
    {
        $Posts = Posts::where('member_id', $id)->pluck('id');
        if ($Posts->isEmpty()) return 0;
        $count_comments = Comments::whereIn('post_id',$Posts->toArray())
            ->where('pid', 0)
            ->count();
        return $count_comments;
    }

    /**
     * 获取用户被评论
     * 
     *
     * @id         用户id
     * @return     评论 
     */
    public static function getCommentsByMemberid(int $id)  : array
    {
        $Posts = Posts::where('member_id', $id)->pluck('id');
        if ($Posts->isEmpty()) return [];
        $Comments = self::whereIn('post_id',$Posts->toArray())
            ->where('pid', 0)
            ->with([
                    'member' => function($query) {
                        $query->select('nickname', 'id'); 
                    },
                    'post' => function($query) {
                        $query->select('title', 'id'); 
                    }
                ])

            ->orderby('id', 'desc')
            ->get();
        if ($Comments->isEmpty()) return [];
        foreach ($Comments  as $pro) {
            $pro->name  = $pro->member['nickname'];
            $pro->title = $pro->post['title'];
        }
        return $Comments->toArray();
    }
}
