<?php

namespace App\Admin\Controllers;

use App\Models\Members;
use App\Models\Messages;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use App\Admin\Actions\Post\AddUserMessage;

class UserMessagesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户消息';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Members);
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
            $actions->add(new AddUserMessage);
        });
        $grid->column('id', __('ID'));
        $grid->column('nickname', __('昵称'));
        $grid->column('avatar.url', '头像')->display(function ($avatar) {
            $el = <<< EOT
            <a href="{$avatar}" class="grid-popup-link"> <img src="{$avatar}" style="max-width:50px;max-height:50px" class="img img-thumbnail"> </a>
EOT;
            return $el;
        });
        $grid->column('likeMessages', "点赞(未读/全部)")->display(function($message){
            $ready = Messages::where('be_like_member_id', $this->id)
                ->where('type', 1);
            $all_count = $ready->count ();
            $no_read_count =  $ready->where('is_readed', 0)->count();
            return $no_read_count .'/'. $all_count;
        })->expand(function($model){
            $has_messages = Messages::whereIn('type', [1])
                ->where('be_like_member_id', $this->id)
                ->get();
            $data = [];
            if (count($has_messages)  >  0) {
                foreach($has_messages as $el) {
                    $tmp['id'] = $el->id;
                    $tmp['title'] = $el->content;
                    $tmp['status'] = $el->is_readed ? '是' : '否';
                    $tmp['created_at'] = $el->created_at;
                    $data[] = $tmp;
                }
            }
            return new Table(['ID', '标题', '是否已读', '时间'], $data); 
        });
        $grid->column('followMessages', "关注(未读/全部)")->display(function($message){
            $ready = Messages::where('be_like_member_id', $this->id)
                ->where('type', 2);
            $no_read_count =  $ready->where('is_readed', 0)->count();
            $all_count = $ready->count ();
            return "$no_read_count/$all_count";
        })
        ->expand(function($model){
            $has_messages = Messages::whereIn('type', [2])
                ->where('be_like_member_id', $this->id)
                ->get();
            $data = [];
            if (count($has_messages)  >  0) {
                foreach($has_messages as $el) {
                    $tmp['id'] = $el->id;
                    $tmp['title'] = $el->content;
                    $tmp['status'] = $el->is_readed ? '是' : '否';
                    $tmp['created_at'] = $el->created_at;
                    $data[] = $tmp;
                }
            }
            return new Table(['ID', '标题', '是否已读', '时间'], $data); 
        });
        $grid->column('commentMessages', "点评(未读/全部)")->display(function($message){
            $ready = Messages::where('be_like_member_id', $this->id)
                ->where('type', 3);
            $no_read_count =  $ready->where('is_readed', 0)->count();
            $all_count = $ready->count ();
            return $no_read_count .'/'. $all_count;
        })
        ->expand(function($model){
            $has_messages = Messages::whereIn('type', [3])
                ->where('be_like_member_id', $this->id)
                ->get();
            $data = [];
            if (count($has_messages)  >  0) {
                foreach($has_messages as $el) {
                    $tmp['id'] = $el->id;
                    $tmp['title'] = $el->content;
                    $tmp['status'] = $el->is_readed ? '是' : '否';
                    $tmp['created_at'] = $el->created_at;
                    $data[] = $tmp;
                }
            }
            return new Table(['ID', '标题', '是否已读', '时间'], $data); 
        });
        $grid->column('replyMessages', "回复(未读/全部)")->display(function($message){
            $ready = Messages::where('be_like_member_id', $this->id)
                ->where('type', 4);
            $no_read_count =  $ready->where('is_readed', 0)->count();
            $all_count = $ready->count ();
            return $no_read_count .'/'. $all_count;
        })
        ->expand(function($model){
            $has_messages = Messages::whereIn('type', [4])
                ->where('be_like_member_id', $this->id)
                ->get();
            $data = [];
            if (count($has_messages)  >  0) {
                foreach($has_messages as $el) {
                    $tmp['id'] = $el->id;
                    $tmp['title'] = $el->content;
                    $tmp['status'] = $el->is_readed ? '是' : '否';
                    $tmp['created_at'] = $el->created_at;
                    $data[] = $tmp;
                }
            }
            return new Table(['ID', '标题', '是否已读', '时间'], $data); 
        });
        $grid->column('systemMessage', "系统消息(未读/全部)")->display(function($message){
            $ready = Messages::where('be_like_member_id', $this->id)
                ->whereIn('type', [5,6]);
            $all_count = $ready->count();
            $no_read_count =  $ready->where('is_readed', 0)->count();
            return $no_read_count . '/' . $all_count;
            })
            ->expand(function($model){
            $has_messages = Messages::whereIn('type', [5,6])
                ->where('be_like_member_id', $this->id)
                ->get();
                $data = [];
                if (count($has_messages)  >  0) {
                    foreach($has_messages as $el) {
                        $tmp['id'] = $el->id;
                        $tmp['title'] = $el->systemMessageDetail->title;
                        $tmp['status'] = $el->is_readed ? '是' : '否';
                        if ($el->type == 5) $tmp['type'] = '系统消息';
                        if ($el->type == 6) $tmp['type'] = '指定消息';
                        $tmp['created_at'] = $el->systemMessageDetail->created_at;
                        $data[] = $tmp;
                    }
                }
                return new Table(['ID', '标题', '是否已读', '类型', '时间'], $data); 
        });
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
        $show = new Show(Messages::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('answer_like_id', __('Answer like id'));
        $show->field('comment_like_id', __('Comment like id'));
        $show->field('answer_comment_like_id', __('Answer comment like id'));
        $show->field('post_like_id', __('Post like id'));
        $show->field('member_follow_id', __('Member follow id'));
        $show->field('post_comment_id', __('Post comment id'));
        $show->field('answer_comment_id', __('Answer comment id'));
        $show->field('be_like_member_id', __('Be like member id'));
        $show->field('is_readed', __('Is readed'));
        $show->field('content', __('Content'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));
        $show->field('member_id', __('Member id'));
        $show->field('type', __('Type'));
        $show->field('system_message_detail_id', __('System message detail id'));
        $show->field('content_type', __('Content type'));
        $show->field('post_id', __('Post id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Messages);

        $form->number('answer_like_id', __('Answer like id'));
        $form->number('comment_like_id', __('Comment like id'));
        $form->number('answer_comment_like_id', __('Answer comment like id'));
        $form->number('post_like_id', __('Post like id'));
        $form->number('member_follow_id', __('Member follow id'));
        $form->number('post_comment_id', __('Post comment id'));
        $form->number('answer_comment_id', __('Answer comment id'));
        $form->number('be_like_member_id', __('Be like member id'));
        $form->number('is_readed', __('Is readed'));
        $form->text('content', __('Content'));
        $form->number('member_id', __('Member id'));
        $form->number('type', __('Type'));
        $form->number('system_message_detail_id', __('System message detail id'));
        $form->number('content_type', __('Content type'));
        $form->number('post_id', __('Post id'));

        return $form;
    }
}
