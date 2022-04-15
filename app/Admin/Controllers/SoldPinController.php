<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\SoldPin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SoldPinController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SoldPin';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SoldPin());

        $grid->column('id', __('Id'));
        //$grid->column('product_id', __('Product id'));
        $grid->column('product_id', __('Product Name'))->display(function($userId) {
            return Product::find($userId)->name;
        });
        $grid->column('pin', __('Pin'));
        $grid->column('serial', __('Serial'));
        $grid->column('expiry_date', __('Expiry date'));
        $grid->column('sold_date', __('Sold date'));
        $grid->column('reference', __('Reference'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->filter(function ($filter) {

            $filter->like('reference','Reference');

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
        $show = new Show(SoldPin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('product_id', __('Product id'));
        $show->field('pin', __('Pin'));
        $show->field('serial', __('Serial'));
        $show->field('expiry_date', __('Expiry date'));
        $show->field('sold_date', __('Sold date'));
        $show->field('reference', __('Reference'));
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
        $form = new Form(new SoldPin());

        $form->number('product_id', __('Product id'));
        $form->text('pin', __('Pin'));
        $form->text('serial', __('Serial'));
        $form->datetime('expiry_date', __('Expiry date'))->default(date('Y-m-d H:i:s'));
        $form->datetime('sold_date', __('Sold date'))->default(date('Y-m-d H:i:s'));
        $form->text('reference', __('Reference'));

        return $form;
    }
}
