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

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();

Route::group(['namespace' => 'Admin'], function(){

Route::get('/receiving', 'ReceivingController@index');

Route::get('/supplier', 'SupplierController@index')->name('supplier.index');
Route::get('/supplier/create', 'SupplierController@create')->name('supplier.create');

});