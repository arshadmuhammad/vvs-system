<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('products', ProductController::class);
    $router->resource('availible_pins', PinController::class);

    $router->post('pins/csv/import', 'PinController@import');
    $router->get('/import', 'PinController@importPin');
    $router->post('/import', 'PinController@postImport');
    $router->get('productsresult', 'PinController@products');

});
