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


Route::get('/item', 'ItemController@index')->name('item.index');
Route::get('/item/create', 'ItemController@create')->name('item.create');
Route::get('/item/get_item_id', 'ItemController@get_item_id')->name('item.get_item_id');
// Route::get('/supplier/edit/{id}', 'SupplierController@edit');
// Route::get('/supplier/{id}', 'SupplierController@show')->name('supplier.view');
// Route::get('/supplier/api/show/{id}', 'SupplierController@api_show_info')->name('supplier.api_view');
// Route::get('/supplier/api/selected_company/{id}', 'SupplierController@api_selected_company')->name('supplier.api_selected_company');
Route::get('/supplier/api/supplier/list','ItemController@api_supplier_list')->name('item.api_supplier_list');

// Route::post('/supplier/api/upload/photo/{id}', 'SupplierController@api_upload_photo')->name('supplier.api_upload_photo');
// Route::post('/supplier/store', 'SupplierController@store')->name('supplier.store');
// Route::post('/supplier/update/{id}', 'SupplierController@update')->name('supplier.update');
// Route::post('/supplier/apiGetAllSupplier', 'SupplierController@apiGetAllSupplier')->name('company.apiGetAllSupplier');


Route::get('/uom', 'UOMController@index')->name('uom.index');
Route::get('/uom/api/weight_uom/list','UOMController@weight_uom_list')->name('uom.api_weight_uom_list');
Route::get('/uom/api/item_uom/list','UOMController@item_uom_list')->name('uom.api_item_uom_list');


Route::post('/uom/item_uom/store', 'UOMController@store_item_uom')->name('uom.store_item_uom');
Route::post('/uom/weight_uom/store', 'UOMController@store_weight_uom')->name('uom.store_weight_uom');
Route::post('/uom/item_uom/delete', 'UOMController@destroy_item_uom')->name('uom.destroy_item_uom');
Route::post('/uom/weight_uom/delete', 'UOMController@destroy_weight_uom')->name('uom.destroy_weight_uom');


Route::get('/supplier', 'SupplierController@index')->name('supplier.index');
Route::get('/supplier/create', 'SupplierController@create')->name('supplier.create');
Route::get('/supplier/get_supplier_id', 'SupplierController@get_supplier_id')->name('supplier.get_supplier_id');
Route::get('/supplier/edit/{id}', 'SupplierController@edit');
Route::get('/supplier/{id}', 'SupplierController@show')->name('supplier.view');
Route::get('/supplier/api/show/{id}', 'SupplierController@api_show_info')->name('supplier.api_view');
Route::get('/supplier/api/selected_company/{id}', 'SupplierController@api_selected_company')->name('supplier.api_selected_company');
Route::get('/supplier/api/company/list','SupplierController@api_company_list')->name('supplier.api_company_list');

Route::post('/supplier/api/upload/photo/{id}', 'SupplierController@api_upload_photo')->name('supplier.api_upload_photo');
Route::post('/supplier/store', 'SupplierController@store')->name('supplier.store');
Route::post('/supplier/update/{id}', 'SupplierController@update')->name('supplier.update');
Route::post('/supplier/apiGetAllSupplier', 'SupplierController@apiGetAllSupplier')->name('company.apiGetAllSupplier');


Route::get('/warehouse', 'WarehouseController@index')->name('warehouse.index');
Route::get('/warehouse/create', 'WarehouseController@create')->name('warehouse.create');
Route::get('/warehouse/get_warehouse_id', 'WarehouseController@get_warehouse_id')->name('warehouse.get_warehouse_id');
Route::get('/warehouse/edit/{id}', 'WarehouseController@edit');
Route::get('/warehouse/{id}', 'WarehouseController@show')->name('warehouse.api_view');
Route::get('/warehouse/api/show/{id}', 'WarehouseController@api_show_info')->name('warehouse.api_view');

Route::post('/warehouse/api/upload/photo/{id}', 'WarehouseController@api_upload_photo')->name('warehouse.api_upload_photo');
Route::post('/warehouse/store', 'WarehouseController@store')->name('warehouse.store');
Route::post('/warehouse/update/{id}', 'WarehouseController@update')->name('warehouse.update');
Route::post('/warehouse/apiGetAllWarehouse', 'WarehouseController@apiGetAllWarehouse')->name('warehouse.apiGetAllWarehouse');




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