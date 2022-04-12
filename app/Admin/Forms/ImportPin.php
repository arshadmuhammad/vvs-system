<?php

namespace App\Admin\Forms;

use App\Models\Product;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class ImportPin extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Import PINs';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        dump($request->all());

        admin_success('Processed successfully.');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {

        $this->select('product_id')->options(function ($id) {
            $product = Product::find($id);

            if ($product) {
                return [$product->id => $product->name];
            }
        })->rules('required')->ajax('/admin/productsresult');
        $this->file('csvfile', 'CSV File')->rules('mimes:csv|required');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
//        return [
//            'name'       => 'John Doe',
//            'email'      => 'John.Doe@gmail.com',
//            'created_at' => now(),
//        ];
        return [

        ];
    }
}
