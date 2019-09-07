<?php

namespace App\Admin\Controllers;

use App\Models\Posts;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

class videosController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '视频';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Posts);
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->model()->where('content_type', 1);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', 'ID');
        $grid->column('images','封面图片')->display(function($model){
            $html = '';
            $data = Posts::where('id', $this->id)->with('images')->first();
            if ($data->images) {
                foreach($this->images as $el) {
                    $html .=  " <img src='{$el["url"]}'  style='max-width:60px;max-height:60px' class='img img-thumbnail'>";
                } 
            }
            return $html;
        });
        $grid->column('title', '标题')->editable();
        $grid->column('video_url', '视频')->display(function($model){
            $html =  "<video width='180' height='120' controls='controls'>
                <source src=".$this->video_url." type='video/mp4' />
                <source src=".$this->video_url." type='video/ogg'>
                <source src=".$this->video_url." type='video/webm' />
                <object data=".$this->video_url." width='180'  height='120'>
                <embed src=".$this->video_url." width='180'  height='120' />
                </object>
                </video>";
            return $html;
        });
        $grid->column('member', '发布用户')
            ->display(function($model){
               return $model['nickname']; 
            });
        $grid->column('status', '审核状态')->switch( [ 
            'on'  => ['value' => 0, 'text' => '禁止', 'color' => 'primary'],
            'off' => ['value' => 1, 'text' => '通过', 'color' => 'default']
        ]);
        $grid->column('likes', '点赞')
            ->display(function($model){
                return count($this->likes);
            })
            ->expand(function($model){
                $hasLikes = $model->likes->take(10)->map(function($query){
                    return $query->only(['id', 'nickname']);
                });
                $data = $hasLikes->isEmpty() ? [] : $hasLikes->toArray();
                return new Table(['ID', '昵称'], $data);
            });
        $grid->column('commants','评论数')->display(function(){
                return count($this->comments);
            })
            ->expand(function($model){
                $comments  = $this->comments->take(10)->where('pid', 0);
                $data = [];
                if (!$comments->isEmpty()) {
                    foreach($comments as $el) {
                        $tmp['id'] = $el->id; 
                        $tmp['nickname'] = $el->member->name;
                        $tmp['content'] = $el->content;
                        $tmp['created_at'] = $el->created_at->toArray()['formatted'];
                        $data[] = $tmp;
                    }
                }
                return new  Table(['ID', '昵称', '内容', '时间'], $data);
            });
        $grid->column('favorites', '收藏')->display(function($model){
            return count($this->favorites) ;
            })
            ->expand(function($model){
                $data = [];
                if ($this->favorites) {
                    foreach($this->favorites as $el) {
                        $tmp['id'] = $el->id;
                        $tmp['nickname'] = $el->nickname;
                        $data[] = $tmp;
                    }
            }
            return new Table(['id', '昵称'], $data);
        });
        $grid->column('tag_id', '分类')->display(function(){
             return $this->tag->name;
        })->label([
            1 => 'default',
            2 => 'warning',
            3 => 'success',
            4 => 'info',
        ]);
        $grid->column('created_at', '发布时间');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Posts::findOrFail($id));

        $show->field('id', 'Id');
        $show->field('title', 'Title');
        $show->field('content', 'Content');
        $show->field('video_url', 'Video url');
        $show->field('content_type', 'Content type');
        $show->field('member_id', 'Member id');
        $show->field('created_at', 'Created at');
        $show->field('updated_at', 'Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Posts);

        $form->text('title', 'Title');
        $form->text('content', 'Content');
        $form->text('video_url', 'Video url');
        $form->switch('status', '审核状态')->states( [ 
            'on'  => ['value' => 0, 'text' => '禁止', 'color' => 'primary'],
            'off' => ['value' => 1, 'text' => '通过', 'color' => 'default']
        ]);
        $form->number('content_type', 'Content type');
        $form->number('member_id', 'Member id');
        return $form;
    }


    
}
