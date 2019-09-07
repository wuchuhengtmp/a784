<?php

namespace App\Admin\Controllers;

use App\Models\Levels;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LevelsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员等级';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Levels);
        $grid->column('id', 'ID')->sortable();
        $grid->name('爵位');
        $grid->annotation('规则说明')->editable();
        $grid->eg_follows('粉丝量')->editable();
        $grid->eg_money('额度')->editable();
        $grid->order_num('自定义排序')->editable();
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
        $Show = new Show(Levels::findOrFail($id));

        $show->field(' id', __(' id'));
        $show->field('name', '爵位');
        $show->field('annotation', '规则说明');
        $show->field('eg_follows', '粉丝量');
        $show->field('eg_money', '额度');
        $show->field('order_num', '自定义排序');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Levels);

        $form->number(' id', __(' id'));
        $form->text('name', '爵位');
        $form->text('annotation', '规则说明');
        $form->number('eg_follows', '粉丝量')->rules('required|numeric');
        $form->number('eg_money', '额度')->rules('required|numeric');
        $form->number('order_num', '自定义排序')->rules('required|numeric');
        return $form;
    }
}
