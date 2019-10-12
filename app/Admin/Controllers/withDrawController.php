<?php

namespace App\Admin\Controllers;

use App\Models\AccountLogs;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class withDrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\AccountLogs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AccountLogs);
         $grid->model()
            ->where('status', 0)
            ->where('type', 3)
            ->orderBy('id', 'desc');
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->column('id', 'ID');
        $grid->column('type', '类型')->display(function($type){
            switch($type) {
            case 1 : 
                return '支出';break;
            case 2 : 
                return '收入';break;
            case 3 : 
                return '提现';break;
            case 4 : 
                return '充值';break;

            }
        })->label([
            1 => 'default',
            2 => 'warning',
            3 => 'success',
            4 => 'info',
        ]);
        $grid->column('member', '会员')->display(function() {
            return $this->member->nickname ?? '';
        });
        $grid->column('notice', '备注');
        $grid->column('money', '金额')->display(function(){
            if (in_array($this->type, [1,3])) 
                return '-'  . number_format($this->money, 2, '.', '');
            else 
                return '+'  . number_format($this->money, 2, '.', '');
        });
        $grid->column('withdraw_account', '提现账号');
        $grid->column('account_name', '提现账号名');
        $grid->column('transfer_type_id','提现方式')->display(function(){
            return $this->transferType->name; 
        })
            ->label([
                1 => 'default',
                2 => 'warning',
                3 => 'success',
            ]);



        // 设置text、color、和存储值
        $states = [
            'on'  => ['value' => 1, 'text' => '完成', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '未完成', 'color' => 'default'],
        ];
        $grid->column('status', '状态')->switch($states);
        $grid->column('created_at', '时间');

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
        $show = new Show(AccountLogs::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('notice', __('Notice'));
        $show->field('member_id', __('Member id'));
        $show->field('money', __('Money'));
        $show->field('transfer_type_id', __('Transfer type id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('status', __('Status'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('type', __('Type'));
        $show->field('out_trade_no', __('Out trade no'));
        $show->field('trade_no', __('Trade no'));
        $show->field('withdraw_account', __('Withdraw account'));
        $show->field('account_name', __('Account name'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AccountLogs);

        $form->text('notice', __('Notice'));
        $form->number('member_id', __('Member id'));
        $form->decimal('money', __('Money'))->default(0.00);
        $form->number('transfer_type_id', __('Transfer type id'));
        $form->number('status', __('Status'));
        $form->number('type', __('Type'));
        $form->text('out_trade_no', __('Out trade no'));
        $form->text('trade_no', __('Trade no'));

        $states = [
            'on'  => ['value' => 1, 'text' => '完成', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '未完成', 'color' => 'default'],
        ];
        $form->switch('status');
        $form->text('withdraw_account', __('Withdraw account'));
        $form->text('account_name', __('Account name'));

        return $form;
    }
}
