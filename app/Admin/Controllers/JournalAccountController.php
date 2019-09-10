<?php

namespace App\Admin\Controllers;

use App\Models\AccountLogs;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class JournalAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '流水记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AccountLogs);
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->column('id', 'ID');
        $grid->column('is_transfer_out', '类型')->display(function(){
            if ($this->is_transfer_out == 1 && $this->is_out_transaction == 0) {
                return '支出';
            } else if($this->is_transfer_out == 0  && $this->is_out_transaction == 0) {
                return '收入';
            } elseif($this->is_transfer_out == 0 && $this->is_out_transaction == 1) {
                return '充值';
            } elseif($this->is_transfer_out == 1  && $this->is_out_transaction == 1) {
                return '提现';
            }
        })->label([
            0 => 'default',
            1 => 'warning',
        ]);
        $grid->column('member', '会员')->display(function() {
            return $this->member->nickname;
        });
        $grid->column('notice', '备注');
        $grid->column('money', '金额')->display(function(){
            if ($this->is_transfer_out) 
                return '-'  . number_format($this->money, 2, '.', '');
            else 
                return '+'  . number_format($this->money, 2, '.', '');
        });
        $grid->column('transfer_type_id','支付方式')->display(function(){
                return $this->transferType->name; 
            })
            ->label([
            1 => 'default',
            2 => 'warning',
            3 => 'success',
            ]);
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
        $show->field('uuid', __('Uuid'));
        $show->field('is_transfer_out', __('Is transfer out'));
        $show->field('notice', __('Notice'));
        $show->field('member_id', __('Member id'));
        $show->field('money', __('Money'));
        $show->field('is_third_party_ transfer', __('Is third party  transfer'));
        $show->field('transfer_type_id', __('Transfer type id'));
        $show->field('created_at', __('Created at'));

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

        $form->text('uuid', __('Uuid'));
        $form->number('is_transfer_out', __('Is transfer out'));
        $form->text('notice', __('Notice'));
        $form->number('member_id', __('Member id'));
        $form->decimal('money', __('Money'))->default(0.00);
        $form->number('is_third_party_ transfer', __('Is third party  transfer'));
        $form->number('transfer_type_id', __('Transfer type id'));

        return $form;
    }
}
