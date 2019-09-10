<?php

namespace App\Admin\Controllers;

use App\Models\Posts;
use App\Models\Members;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class AnswersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '问答管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Posts);
        $grid->model()->where('content_type', 3);

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->column('id', 'Id');
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
        $grid->column('title', '问题')->width(200)->limit(25);
        $grid->column('answers', '回答')->display(function(){
                return count($this->answers);
            })
            ->expand(function($model){
                $data = [];
                if ($this->answers) {
                    foreach($this->answers as $el) {
                        $tmp['id']  =  $el->id;
                        $tmp['name'] = Members::find($el->member_id)->nickname;
                        $tmp['content'] = $el->content;
                        $tmp['created_at'] = $el->created_at;
                        $data[] = $tmp;
                    }
                } 
                return new  Table(['ID', '昵称', '内容', '时间'], $data);
            }); 
        $grid->column('member', '发布人')
            ->display(function($model) {
               return $model['nickname']; 
            });
        $grid->column('status', '审核状态')->switch( [ 
            'off'  => ['value' => 0, 'text' => '禁止', 'color' => 'primary'],
            'on' => ['value' => 1, 'text' => '通过', 'color' => 'default']
        ]);
        $grid->column('commants','评论数')->display(function(){
                return $this->answerComments ? count($this->answerComments) : 0;
            })
            ->expand(function($model){
                $comments  = $this->answerComments->take(10);
                $data = [];
                if (!$comments->isEmpty()) {
                    foreach($comments as $el) {
                        $tmp['id'] = $el->id; 
                        $tmp['nickname'] = $el->member->nickname;
                        $tmp['content'] = $el->content;
                        $tmp['created_at'] = $el->created_at->toArray()['formatted'];
                        $data[] = $tmp;
                    }
                }
                return new  Table(['ID', '昵称', '内容', '时间'], $data);
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
        $show->field('status', 'Status');
        $show->field('created_at', 'Created at');
        $show->field('updated_at', 'Updated at');
        $show->field('deleted_at', 'Deleted at');

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
        $form->switch('status', '审核状态')->states( [ 
            'off'  => ['value' => 0, 'text' => '禁止', 'color' => 'primary'],
            'on' => ['value' => 1, 'text' => '通过', 'color' => 'default']
        ]);
        return $form;
    }
}
