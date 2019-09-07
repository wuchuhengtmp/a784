<?php

namespace App\Admin\Controllers;

use App\Models\Levels;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DemoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\Levels';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Levels);

        $grid->column(' id', __(' id'));
        $grid->column('name', __('Name'));
        $grid->column('annotation', __('Annotation'))->editable();
        $grid->column('eg_follows', __('Eg follows'));
        $grid->column('eg_money', __('Eg money'));
        $grid->column('order_num', __('Order num'));

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

        $show->field(' id', __(' id'));
        $show->field('name', __('Name'));
        $show->field('annotation', __('Annotation'))->editable();
        $show->field('eg_follows', __('Eg follows'));
        $show->field('eg_money', __('Eg money'));
        $show->field('order_num', __('Order num'));
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
        $form->text('name', __('Name'));
        $form->text('annotation', __('Annotation'));
        $form->number('eg_follows', __('Eg follows'));
        $form->number('eg_money', __('Eg money'));
        $form->number('order_num', __('Order num'));

        return $form;
    }
}
