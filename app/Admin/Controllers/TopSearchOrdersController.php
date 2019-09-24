<?php

namespace App\Admin\Controllers;

use App\Models\TopSearchOrders;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TopSearchOrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '置顶订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TopSearchOrders);

        $grid->column('id', "ID");
        $grid->column('title', '标题')->editable();
        $grid->column('nickname', __('用户名'));
        $grid->column('price', __('价格'));
        $grid->column('expense', __('费用'));
        $grid->column('top_time_limit', __('置顶时长'));
        $grid->column('top_end_time', __('置顶时限'));
        $grid->column('transfer_type', __('付款方式'))
            ->display(function($type_id){
            if ($type_id == 1) 
                return '龙币';
            if ($type_id == 3 )
                return ' 支付宝';
                /* return $this->transferType->name; */ 
            })
            ->label([
            1 => 'default',
            2 => 'warning',
            3 => 'success',
            ]);
        $grid->column('created_at', __('创建时间'));

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
        $show = new Show(TopSearchOrders::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('post_id', __('Post id'));
        $show->field('nickname', __('Nickname'));
        $show->field('member_id', __('Member id'));
        $show->field('price', __('Price'));
        $show->field('expense', __('Expense'));
        $show->field('top_time_limit', __('Top time limit'));
        $show->field('top_end_time', __('Top end time'));
        $show->field('transfer_type', __('Transfer type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TopSearchOrders);

        $form->text('title', __('Title'));
        $form->number('post_id', __('Post id'));
        $form->text('nickname', __('Nickname'));
        $form->number('member_id', __('Member id'));
        $form->text('price', __('Price'));
        $form->number('expense', __('Expense'));
        $form->time('top_time_limit', __('Top time limit'))->default(date('H:i:s'));
        $form->datetime('top_end_time', __('Top end time'))->default(date('Y-m-d H:i:s'));
        $form->text('transfer_type', __('Transfer type'));

        return $form;
    }
}
