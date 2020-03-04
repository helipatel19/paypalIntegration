<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/payment', function () {
    return view('view-cart');
});

Route::get('/checkout', 'PaypalController@paypalPayment')->name('checkout');

Route::get('status', 'PaypalController@getStatus');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
