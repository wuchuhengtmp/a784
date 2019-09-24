<?php

namespace App\Admin\Controllers;

use App\Models\Configs;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConfigsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '置顶价格设置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Configs);
        $grid->model()->where('name','top_search_price')->limit(1);
        $grid->disablePagination();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->column('value', __('单位(n秒/元)'))->editable();
        $grid->column('notes', __('说明'));

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

        $show->field('id', __('Id'));
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
        $form = new Form(new Configs);
        $form->text('value')->rules('required|regex:/^\d+$/|min:1|gt:0', [
            'regex' => '必须全部为数字',
            'min'   => '不能少于1个字符',
            'gt'    => '时间必须大于0'
        ]);
        return $form;
    }
}
