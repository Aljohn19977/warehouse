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

Route::get('/receiving', 'ReceivingController@index')->name('receiving.index');;
Route::get('/receiving/transaction/list','ReceivingController@api_transaction_list')->name('receiving.api_transaction_list');
Route::get('/receiving/received/list','ReceivingController@api_received_list')->name('receiving.api_received_list');
Route::get('/receiving/get_transaction_info/{id}', 'ReceivingController@get_transaction_info')->name('receiving.get_transaction_info');
Route::get('/receiving/get_receiving_order_info/{id}', 'ReceivingController@get_receiving_order_info')->name('receiving.get_receiving_order_info');
Route::get('/receiving/receive_item_info/{id}', 'ReceivingController@receive_item_info')->name('receiving.receive_item_info');
Route::get('/receiving/get_received_item/{id}', 'ReceivingController@get_received_item')->name('receiving.get_received_item');
Route::get('/receiving/get_received_missing_item/{id}', 'ReceivingController@get_received_missing_item')->name('receiving.get_received_missing_item');
Route::get('/receiving/get_received_damage_item/{id}', 'ReceivingController@get_received_damage_item')->name('receiving.get_received_damage_item');


Route::get('/receiving/undo_receive_item_info/{id}', 'ReceivingController@undo_receive_item_info')->name('receiving.undo_receive_item_info');
Route::get('/receiving/undo_receive_missing_item_info/{id}', 'ReceivingController@undo_receive_missing_item_info')->name('receiving.undo_receive_missing_item_info');
Route::get('/receiving/undo_receive_damage_item_info/{id}', 'ReceivingController@undo_receive_damage_item_info')->name('receiving.undo_receive_damage_item_info');
Route::post('/receiving/receive_item', 'ReceivingController@receive_item')->name('receiving.receive_item');
Route::post('/receiving/receive_missing_item', 'ReceivingController@receive_missing_item')->name('receiving.receive_missing_item');
Route::post('/receiving/receive_damage_item', 'ReceivingController@receive_damage_item')->name('receiving.receive_damage_item');
Route::post('/receiving/undo_receive_item', 'ReceivingController@undo_receive_item')->name('receiving.undo_receive_item');
Route::post('/receiving/undo_receive_missing_item', 'ReceivingController@undo_receive_missing_item')->name('receiving.undo_receive_missing_item');
Route::post('/receiving/undo_receive_damage_item', 'ReceivingController@undo_receive_damage_item')->name('receiving.undo_receive_damage_item');
Route::post('/receiving/selected_serialize_item', 'ReceivingController@selected_serialize_item')->name('receiving.selected_serialize_item');

Route::post('/receiving/receiving_order', 'ReceivingController@receiving_order')->name('receiving.receiving_order');
Route::post('/receiving/receive_order', 'ReceivingController@receive_order')->name('receiving.receive_order');
Route::post('/receiving/api_get_all_received_item', 'ReceivingController@api_get_all_received_item_barcoding')->name('receiving.api_get_all_received_item_barcoding');
Route::post('/receiving/api_get_selected_received_item_barcoding', 'ReceivingController@api_get_selected_received_item_barcoding')->name('receiving.api_get_selected_received_item_barcoding');



Route::get('/purchase_order', 'PurchaseOrderController@index')->name('purchase_order.index');
Route::get('/purchase_order/get_purchase_order_id', 'PurchaseOrderController@get_purchase_order_id')->name('purchase_order.get_purchase_order_id');
Route::get('/purchase_order/get_transaction_id', 'PurchaseOrderController@get_transaction_id')->name('purchase_order.get_transaction_id');
Route::get('/purchase_order/supplier/api/supplier/list','PurchaseOrderController@api_supplier_list')->name('purchase_order.api_supplier_list');
Route::get('/purchase_order/api/list/{id}','PurchaseOrderController@api_purchase_order_list')->name('purchase_order.api_purchase_order_list');
Route::get('/purchase_order/get_supplier_info/{id}', 'PurchaseOrderController@get_supplier_info')->name('purchase_order.get_supplier_info');
Route::get('/purchase_order/get_purchase_order_info/{id}', 'PurchaseOrderController@get_purchase_order_info')->name('purchase_order.get_purchase_order_info');
Route::get('/purchase_order/get_supplier_item_info_via_id/{id}', 'PurchaseOrderController@get_supplier_item_info_via_id')->name('purchase_order.get_supplier_item_info_via_id');
Route::get('/purchase_order/get_supplier_item_info_via_item_id/{id}', 'PurchaseOrderController@get_supplier_item_info_via_item_id')->name('purchase_order.get_supplier_item_info_via_item_id');
Route::post('/purchase_order/store', 'PurchaseOrderController@store')->name('purchase_order.store');
Route::post('/purchase_order/cancel', 'PurchaseOrderController@cancel')->name('purchase_order.cancel');
Route::post('/purchase_order/validation/add_item_table', 'PurchaseOrderController@validation_add_item_table')->name('purchase_order.validation_add_item_table');
Route::post('/purchase_order/api_get_all_purchase_order', 'PurchaseOrderController@api_get_all_purchase_order')->name('purchase_order.api_get_all_purchase_order');

Route::get('/item', 'ItemController@index')->name('item.index');
Route::get('/item/create', 'ItemController@create')->name('item.create');
Route::get('/item/get_item_id', 'ItemController@get_item_id')->name('item.get_item_id');
Route::get('/item/edit/{id}', 'ItemController@edit');
Route::get('/item/{id}', 'ItemController@show')->name('supplier.view');
Route::get('/item/api/show/{id}', 'ItemController@api_show_info')->name('item.api_view');
Route::get('/item/supplier/api/supplier/list','ItemController@api_supplier_list')->name('item.api_supplier_list');
Route::get('/item/api/selected_supplier/{id}', 'ItemController@api_selected_supplier')->name('item.api_selected_supplier');
Route::get('/item/api/selected_category/{id}', 'ItemController@api_selected_category')->name('item.api_selected_category');
Route::get('/item/api/selected_weight_uom/{id}', 'ItemController@api_selected_weight_uom')->name('item.api_selected_weight_uom');
Route::get('/item/api/selected_item_uom/{id}', 'ItemController@api_selected_item_uom')->name('item.api_selected_item_uom');
Route::get('/item/api/api_selected_item_setting/{id}', 'ItemController@api_selected_item_setting')->name('item.api_selected_item_setting');
Route::post('/item/store', 'ItemController@store')->name('item.store');
Route::post('/item/update/{id}', 'ItemController@update')->name('item.update');
Route::post('/item/api/upload/photo/{id}', 'ItemController@api_upload_photo')->name('item.api_upload_photo');
Route::post('/item/apiGetAllItem', 'ItemController@apiGetAllItem')->name('item.apiGetAllItem');


Route::get('/uom', 'UOMController@index')->name('uom.index');
Route::get('/uom/api/weight_uom/list','UOMController@weight_uom_list')->name('uom.api_weight_uom_list');
Route::get('/uom/api/item_uom/list','UOMController@item_uom_list')->name('uom.api_item_uom_list');
Route::post('/uom/item_uom/store', 'UOMController@store_item_uom')->name('uom.store_item_uom');
Route::post('/uom/weight_uom/store', 'UOMController@store_weight_uom')->name('uom.store_weight_uom');
Route::post('/uom/item_uom/delete', 'UOMController@destroy_item_uom')->name('uom.destroy_item_uom');
Route::post('/uom/weight_uom/delete', 'UOMController@destroy_weight_uom')->name('uom.destroy_weight_uom');


Route::get('/category/api/list','ItemCategoryController@api_categoy_list')->name('category.api_categoy_list');
Route::post('/category/store', 'ItemCategoryController@store_category')->name('category.store_category');
Route::post('/category/delete', 'ItemCategoryController@destroy_category')->name('category.destroy_category');


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