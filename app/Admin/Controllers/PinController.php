<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\ImportButton;
use App\Admin\Forms\ImportPin;
use App\Models\Pin;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content; // Add
use Illuminate\Http\Request; // Add

class PinController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Pin';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Pin());

        $grid->column('id', __('Id'));
        //$grid->column('product_id', __('Product id'));
        $grid->column('product_id', __('Product Name'))->display(function($userId) {
            return Product::find($userId)->name;
        });
        $grid->column('pin', __('Pin'));
        $grid->column('serial', __('Serial'));
        $grid->column('expiry_date', __('Expiry date'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->tools(function ($tools) {
            $tools->append(new ImportButton());
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
        $show = new Show(Pin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('product_id', __('Product id'));
        $show->field('pin', __('Pin'));
        $show->field('serial', __('Serial'));
        $show->field('expiry_date', __('Expiry date'));
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
        $form = new Form(new Pin());

        //$form->number('product_id', __('Product id'));
        $form->select('product_id')->options(function ($id) {
            $product = Product::find($id);

            if ($product) {
                return [$product->id => $product->name];
            }
        })->ajax('/admin/pins/products');
        $form->text('pin', __('Pin'));
        $form->text('serial', __('Serial'));
        $form->datetime('expiry_date', __('Expiry date'))->default(date('Y-m-d H:i:s'));

        return $form;
    }

    public function products(Request $request)
    {
        $q = $request->get('q');

        return Product::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    /**
     * Import interface.
     */
    protected function import(Content $content, Request $request)
    {
        $file = $request->file('file');
        $csv = array_map('str_getcsv', file($file));
        array_shift($csv);
        //print_r($csv);exit;

        foreach ($csv as $row){
            $productName  = $row[0];
            $pin          = $row[1];
            $serial       = $row[2];
            $expiryDate   = $row[3];

//            $question = \App\Models\Pin::where('id', $id)->first();
//            if(!$question){
                $req = new Pin();
                $req->product_id = \App\Models\Product::where('name', $productName)->first()->id;
                $req->pin = $pin;
                $req->serial = $serial;
                $req->expiry_date = date('Y-m-d h:i:s', strtotime($expiryDate));
                $req->save();
//            }else{
//                $question->conetnt = $content;
//                $question->save();
//            }
        }
        return redirect('admin/pins');
    }

    public function importPin(Content $content){
        return $content
            ->title('Import PINs')
            ->body(new ImportPin());
    }
}
