<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\ImportButton;
use App\Models\Pin;
use App\Models\SoldPin;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content; // Add
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request; // Add
use Encore\Admin\Widgets;
use Illuminate\Support\Facades\DB;

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

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

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
        })->ajax('/admin/productsresult');
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

            $req = new Pin();
            $req->product_id = \App\Models\Product::where('name', $productName)->first()->id;
            $req->pin = $pin;
            $req->serial = $serial;
            $req->expiry_date = date('Y-m-d h:i:s', strtotime($expiryDate));
            $req->save();

        }
        return redirect('admin/pins');
    }

    public function exportPin(Content $content): Content
    {
        $this->dumpRequest($content);

        $content->title('Export PINs');

        $form = new Widgets\Form();

        $form->method('post');
        $form->action('export');

        $form->select('product_id')->options(function ($id) {
            $product = Product::find($id);

            if ($product) {
                return [$product->id => $product->name];
            }
        })->rules('required')->ajax('/admin/productsresult');
        $form->html('<h4>Available Qty For Product: <span id="available_qty"></span></h4>');

        $form->text('qty', 'Select Qty')->rules('required');
        $form->textarea('reference', 'Reference')->rules('required');


        $content->body(new Widgets\Box('Export PINs', $form));

        return $content;
    }

    public function postExport(Request $request){
        //dump($request->all());
        $productId = $request->product_id;
        $qty = $request->qty;
        $pins = Pin::where('product_id', $productId)->limit($qty)->get();
        $pinsArray = [];
        try{
            DB::beginTransaction();
            foreach($pins as $pin){
                $pinsArray[] = [
                    'product_id' => $pin->product_id,
                    'pin'       => $pin->pin,
                    'serial' => $pin->serial,
                    'expiry_date' => $pin->expiry_date,
                    'sold_date' => now(),
                    'reference' => $request->reference,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ];
                $pin->delete();
            }
            SoldPin::insert($pinsArray);
            DB::commit();
        }
        catch(\Exception $exception){
            DB::rollBack();
        }

        $fileName = 'tasks.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Product', 'Pin', 'Serial No', 'Expiry Date', 'Reference');

        $callback = function() use($pinsArray, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($pinsArray as $task) {
                $row['product_id']  = $task['product_id'];
                $row['pin']    = $task['pin'];
                $row['serial']    = $task['serial'];
                $row['expiry_date']  = $task['expiry_date'];
                $row['reference']  = $task['reference'];

                fputcsv($file, array($row['product_id'], $row['pin'], $row['serial'], $row['expiry_date'], $row['reference']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

        //return redirect('admin/export');
    }

    public function importPin(Content $content): Content
    {
        $this->dumpRequest($content);

        $content->title('Import PINs');

        $form = new Widgets\Form();

        $form->method('post');
        $form->action('import');

        $form->select('product_id')->options(function ($id) {
            $product = Product::find($id);

            if ($product) {
                return [$product->id => $product->name];
            }
        })->rules('required')->ajax('/admin/productsresult');
        $form->file('csvfile', 'CSV File')->rules('mimes:csv|required');

        $content->body(new Widgets\Box('Import PINs', $form));

        return $content;
    }

    public function postImport(Request $request){
        //dump($request->all());
        $file = $request->file('csvfile');
        $productId = $request->product_id;
        $csv = array_map('str_getcsv', file($file));
        array_shift($csv);

        foreach ($csv as $row){
            $pin          = $row[0];
            $serial       = $row[1];
            $expiryDate   = $row[2];

            $req = new Pin();
            $req->product_id = $productId;
            $req->pin = $pin;
            $req->serial = $serial;
            $req->expiry_date = date('Y-m-d h:i:s', strtotime($expiryDate));
            $req->save();
        }
        return redirect('admin/import');
    }

    protected function dumpRequest(Content $content)
    {
        $parameters = request()->except(['_pjax', '_token']);

        if (!empty($parameters)) {

            ob_start();

            dump($parameters);

            $contents = ob_get_contents();

            ob_end_clean();

            $content->row(new Widgets\Box('Form parameters', $contents));
        }
    }

    public function productQty(Request $request){
        $product_id = $request->get('product_id');
        return Pin::where('product_id', $product_id)->count();
    }
}
