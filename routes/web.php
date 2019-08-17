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
Route::get('/supplier/get_supplier_id', 'SupplierController@get_supplier_id')->name('supplier.get_supplier_id');
Route::get('/supplier/create', 'SupplierController@create')->name('supplier.create');
Route::post('/supplier/store', 'SupplierController@store')->name('supplier.store');

Route::get('/company', 'CompanyController@index')->name('company.index');
Route::get('/company/create', 'CompanyController@create')->name('company.create');
Route::get('/company/get_company_id', 'CompanyController@get_company_id')->name('company.get_company_id');
Route::get('/company/edit/{id}', 'CompanyController@edit');
Route::get('/company/{id}', 'CompanyController@show')->name('company.view');
Route::get('/company/api/show/{id}', 'CompanyController@api_show_info')->name('company.api_view');
Route::post('/company/api/upload/photo/{id}', 'CompanyController@api_upload_photo')->name('company.api_upload_photo');
Route::post('/company/store', 'CompanyController@store')->name('company.store');
Route::post('/company/update/{id}', 'CompanyController@update')->name('company.update');
Route::post('/company/apiGetAllCompany', 'CompanyController@apiGetAllCompany')->name('company.apiGetAllCompany');
});