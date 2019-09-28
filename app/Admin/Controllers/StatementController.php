<?php

namespace App\Admin\Controllers;

use App\Models\Configs;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StatementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '协议声明';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Configs);
        $grid->model()->whereIn('name', ['disclaimer', 'agreement']);
        $grid->disableCreateButton();
        $grid->disableFilter();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });
        $grid->column('title', __('标题'));
        $grid->column('value', __('内容'))->limit(50);

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
        $show = new Show(Configs::findOrFail($id));
        $show->field('title', __('标题'));
        $show->field('value', __('内容'))->unescape()->as(function ($avatar) {
            return $avatar;
        });
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Configs);

        $form->text('title', __('标题'));
        $form->summernote('value', '内容');
        return $form;
    }
}
