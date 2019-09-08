@extends('admin.partials.master')

@section('style')
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">
@endsection

@section('script')
<script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables/dataTables.bootstrap4.js') }}"></script>
<script src="{{ asset('admin/plugins/inputmask/jquery.inputmask.bundle.js') }}"></script> 
<script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script>
$(document).ready(function(){


$.ajaxSetup({
  headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

$('.select2').select2();

  const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
  });

get_transantion_list();



function get_transantion_list(){
    $.ajax({
        type: 'get',
        url: "/receiving/transaction/list/",
        success: function(data) {

            $('#transaction_id option').remove();


            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.transaction_id, row.id, false, false);
                $('#transaction_id').append(newOption).trigger('change');
            })

            $('#transaction_id').select2().val(null).trigger("change");

            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.purchase_order_id, row.id, false, false);
                $('#purchase_order_id').append(newOption).trigger('change');
            })

            $('#purchase_order_id').select2().val(null).trigger("change");
            
            
        },
        error: function(error){
          console.log('error');
        }
     }); 
}

$('#purchase_order_id').on('select2:select', function (e) {
    var modal_data = e.params.data;
    transaction_info(modal_data.id);
    $('#purchase_order_id').select2().val(modal_data.id).trigger("change");
    $('#transaction_id').select2().val(modal_data.id).trigger("change");
});

$('#transaction_id').on('select2:select', function (e) {
    var modal_data = e.params.data;
    transaction_info(modal_data.id);
    $('#purchase_order_id').select2().val(modal_data.id).trigger("change");
    $('#transaction_id').select2().val(modal_data.id).trigger("change");
});




function transaction_info(id){
    Pace.restart();

    Pace.track(function () {
    $.ajax({
          url: "/receiving/get_transaction_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
            // get_purchase_order_print_list(data.purchase_order_id);
            $('#ordered_date').val(data.order_date);
            $('#supplier_id').val(data.supplier_id);
            $('#supplier_company').val(data.supplier_company);
            $('#supplier_name').val(data.supplier_name);
            $('#ordered_item_list_table > tbody tr').remove();

            $.each(data.purchase_order_items, function(key, value){                         
                console.log(value.item_id);
                var html = '';
                          html += '<tr>';
                          html += '<td>'+value.item_id+'</td>';
                          html += '<td>'+value.item_name+'</td>';
                          html += '<td>'+value.quantity+'</td>';
                          html += '<td>'+value.item_uom+'</td>';
                          html += '<td><button id="receive" class="btn btn-primary" data-id="'+value.id+'"><i class="fas fa-truck-loading"></i></button></td>';
                          html += '</tr>';

                          $('#ordered_item_list_table').prepend(html);
             });

          },
          error: function(error){
            // Toast.fire({
            //   type: 'error',
            //   title: 'Invalid Inputs.'
            // })
          }
        })      
       });   
}

$(document).on('click', '#receive', function(){
  event.preventDefault();
  alert($(this).data().id);

  	
  Swal.fire({
    title: 'Enter Received Quantity',
    input: 'number',
    showCancelButton: true,
    inputValidator: (value) => {
      if (!value || value <= 0) {
        return 'Please enter valid quantity!'
      }else{
        alert (value);
      }
    }
  })

});


});



</script>
@endsection

@section('control_sidebar')
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>PO Receiving</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">PO Receiving</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header p-3">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link active" id="nav_po_create" href="#po_create" data-toggle="tab"><i class="fas fa-truck-loading"></i> Receive Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_print_email_po" href="#print_email_po" data-toggle="tab"><i class="fas fa-print" style="margin-right:5px;"></i>Print and Email Purchase Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_po_cancel" href="#cancel_po" data-toggle="tab"><i class="fas fa-ban"></i> Cancel Purchase Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_po_view" href="#view_po" data-toggle="tab"><i class="far fa-eye"></i> View Purchase Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_po_list" href="#po_list" data-toggle="tab"><i class="fas fa-list"></i> Purchase Order List</a></li>
            </ul>
          </div><!-- /.card-header -->
          <div class="card-body">
          <div class="tab-content">
          <div class="active tab-pane" id="po_create">
            <form role="form" method="post" id="confirm_order">
              <div class="row" style="margin-bottom:25px">
                <!-- <div class="col-lg-6" style="margin-bottom:25px;">
                  <h2>Purchase Order No : 
                    <span id="purchase_order_id" style="color:red;"></span>
                  </h2>
                </div>
                <div class="col-lg-6" style="margin-bottom:25px;">
                  <h6 class="float-right">Transaction No : 
                    <span id="transaction_id"></span>
                  </h6>
                </div> -->
                <div class="col-lg-5 col-md-12">
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Transaction No.</label>
                    <div class="col-sm-8">
                      <select class="select2" id="transaction_id" name="transaction_id" data-placeholder="Select a Transaction No." style="width: 100%;"></select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Purchase Order No.</label>
                    <div class="col-sm-8">
                      <select class="select2" id="purchase_order_id" name="purchase_order_id" data-placeholder="Select a Purchase Order No." style="width: 100%;"></select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Ordered Date</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="ordered_date" value="" placeholder="Ordered Date" readonly>
                      </div>
                    </div>
                    </div>
                    <div class="col-lg-2 col-md-12"></div>
                    <div class="col-lg-5 col-md-12">
                    <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="supplier_id" placeholder="Supplier ID" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier Name</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="supplier_name" placeholder="Supplier Name" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier Company</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="supplier_company" placeholder="Supplier Company" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                          <label for="inputEmail3" class="col-sm-4 control-label">Status</label>
                          <div class="col-sm-8" id="nav4_status">
                             <button id="receive" class="btn btn-flat btn-xs btn-success">Open</button>
                             <button id="receive" class="btn btn-flat btn-xs btn-warning">Ongoing</button>
                             <button id="receive" class="btn btn-flat btn-xs btn-danger">Closed</button>
                          </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                      <div class="col-md-12">
                        <div class="card">
                          <div class="card-header p-2">
                            <ul class="nav nav-pills">
                              <li class="nav-item"><a class="nav-link active" href="#ordered_item_list" data-toggle="tab">Ordered Item</a></li>
                              <li class="nav-item"><a class="nav-link" href="#received_item_list" data-toggle="tab">Received Item</a></li>
                            </ul>
                          </div><!-- /.card-header -->
                          <div class="card-body">
                            <div class="tab-content">
                              <div class="active tab-pane" id="ordered_item_list">
                                <table class="table" id="ordered_item_list_table">
                                  <thead>
                                    <tr>
                                      <th>Item ID</th>
                                      <th>Name</th>
                                      <th>Remaining Quantity</th>
                                      <th>UOM(Item)</th>
                                      <th>Action</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                  </tbody>
                                </table>
                              </div>
                              <!-- /.tab-pane -->
                              <div class="tab-pane" id="received_item_list">
                              <table class="table" id="received_item_list_table">
                                  <thead>
                                    <tr>
                                      <th>Item ID</th>
                                      <th>Name</th>
                                      <th>Quantity</th>
                                      <th>UOM(Item)</th>
                                      <th>Date Received</th>
                                      <th>Action</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr>
                                      <td>ITM-2131123213</td>
                                      <td>Melon</td>
                                      <td>1000</td>
                                      <td>EA</td>
                                      <td>EA</td>
                                      <td><button id="receive" class="btn btn-danger"><i class="fas fa-undo"></i></button></td>
                                    </tr>
                                    <tr>
                                      <td>ITM-2131123213</td>
                                      <td>Melon</td>
                                      <td>1000</td>
                                      <td>EA</td>
                                      <td>EA</td>
                                      <td><button id="receive" class="btn btn-danger"><i class="fas fa-undo"></i></button></td>
                                    </tr>
                                    <tr>
                                      <td>ITM-2131123213</td>
                                      <td>Melon</td>
                                      <td>1000</td>
                                      <td>EA</td>
                                      <td>EA</td>
                                      <td><button id="receive" class="btn btn-danger"><i class="fas fa-undo"></i></button></td>
                                    </tr>
                                  </tbody>
                                </table>
                               </div>
                               <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                          </div><!-- /.card-body -->
                        </div>
                        <!-- /.nav-tabs-custom -->
                      </div>
                      </div>
            </form>
          </div>
           <!-- /.tab-pane -->
           <div class="tab-pane" id="print_email_po">
            <div class="row" style="margin-bottom:25px">
              <div class="col-lg-5 col-md-12">
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Purchase Order No</label>
                  <div class="col-sm-8">
                    <select class="select2" id="nav2_purchase_order_no" name="nav2_purchase_order_no" data-placeholder="Purchase Order No" style="width: 100%;"></select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Transaction No</label>
                  <div class="col-sm-8">
                    <select class="select2" id="nav2_transaction_no" name="nav2_transaction_no" data-placeholder="Transaction No" style="width: 100%;"></select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Order Date</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control" id="nav2_order_date" placeholder="Order Date" readonly>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Deliver To</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="nav2_deliver_to" value="Main Warehouse" placeholder="Deliver To" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-2 col-md-12"></div>
                  <div class="col-lg-5 col-md-12">
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="nav2_supplier_id" placeholder="Supplier ID" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Supplier Name</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="nav2_supplier_name" placeholder="Supplier Name" readonly>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="inputEmail3" class="col-sm-4 control-label">Supplier Company</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control" id="nav2_supplier_company" placeholder="Supplier Company" readonly>
                            </div>
                          </div>
                        </div>
                      </div>
                      <table class="table" id="nav2purchase_order_table">
                        <thead>
                          <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>UOM(Item)</th>
                            <th>Item Price</th>
                            <th>Sub Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <th colspan="5" style="text-align:right">Total:</th>
                            <th colspan="7" style="text-align:center">
                              <span id="nav2total">0</span>
                            </th>
                          </tr>
                        </tbody>
                      </table>
                      <div class="col-lg-12">
                        <button id="print_po" class="btn btn-primary" disabled>Print PO</button>
                        <button id="email_po" class="btn btn-primary" disabled>Send PO via Email</button>
                      </div>
					</div>
          <!-- /.tab-pane -->
          <div class="tab-pane" id="cancel_po">
            <div class="row" style="margin-bottom:25px">
              <div class="col-lg-5 col-md-12">
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Purchase Order No</label>
                  <div class="col-sm-8">
                    <select class="select2" id="nav3_purchase_order_no" name="nav3_purchase_order_no" data-placeholder="Purchase Order No" style="width: 100%;"></select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Transaction No</label>
                  <div class="col-sm-8">
                    <select class="select2" id="nav3_transaction_no" name="nav3_transaction_no" data-placeholder="Transaction No" style="width: 100%;"></select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Order Date</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control" id="nav3_order_date" placeholder="Order Date" readonly>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Deliver To</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="nav3_deliver_to" value="Main Warehouse" placeholder="Deliver To" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-2 col-md-12"></div>
                  <div class="col-lg-5 col-md-12">
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="nav3_supplier_id" placeholder="Supplier ID" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Supplier Name</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="nav3_supplier_name" placeholder="Supplier Name" readonly>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="inputEmail3" class="col-sm-4 control-label">Supplier Company</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control" id="nav3_supplier_company" placeholder="Supplier Company" readonly>
                            </div>
                          </div>
                        </div>
                      </div>
                      <table class="table" id="nav3purchase_order_table">
                        <thead>
                          <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>UOM(Item)</th>
                            <th>Item Price</th>
                            <th>Sub Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <th colspan="5" style="text-align:right">Total:</th>
                            <th colspan="7" style="text-align:center">
                              <span id="nav3total">0</span>
                            </th>
                          </tr>
                        </tbody>
                      </table>
                      <div class="col-lg-12">
                        <button id="cancel" class="btn btn-primary" disabled>Cancel Purchase Order</button>
                      </div>
					</div>
          <!-- /.tab-pane -->  
          <div class="tab-pane" id="view_po">
            <div class="row" style="margin-bottom:25px">
              <div class="col-lg-5 col-md-12">
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Purchase Order No</label>
                  <div class="col-sm-8">
                    <select class="select2" id="nav4_purchase_order_no" name="nav4_purchase_order_no" data-placeholder="Purchase Order No" style="width: 100%;"></select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Transaction No</label>
                  <div class="col-sm-8">
                    <select class="select2" id="nav4_transaction_no" name="nav4_transaction_no" data-placeholder="Transaction No" style="width: 100%;"></select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="inputEmail3" class="col-sm-4 control-label">Order Date</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control" id="nav4_order_date" placeholder="Order Date" readonly>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Deliver To</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="nav4_deliver_to" value="Main Warehouse" placeholder="Deliver To" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-2 col-md-12"></div>
                  <div class="col-lg-5 col-md-12">
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="nav4_supplier_id" placeholder="Supplier ID" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Supplier Name</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="nav4_supplier_name" placeholder="Supplier Name" readonly>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="inputEmail3" class="col-sm-4 control-label">Supplier Company</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control" id="nav4_supplier_company" placeholder="Supplier Company" readonly>
                          </div>
                          </div>
                          <div class="form-group row">
                          <label for="inputEmail3" class="col-sm-4 control-label">Status</label>
                          <div class="col-sm-8" id="nav4_status">

                          </div>
                          </div>
                        </div>
                      </div>
                      <table class="table" id="nav4purchase_order_table">
                        <thead>
                          <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>UOM(Item)</th>
                            <th>Item Price</th>
                            <th>Sub Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <th colspan="5" style="text-align:right">Total:</th>
                            <th colspan="7" style="text-align:center">
                              <span id="nav4total">0</span>
                            </th>
                          </tr>
                        </tbody>
                      </table>
					</div>
          <!-- /.tab-pane -->                
          <div class="tab-pane" id="po_list">
            <div class="row">
            <div class="col-lg-4">
                <!-- Date range -->
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                      </span>
                    </div>
                    <input type="text" class="form-control float-right" id="filter_date" placeholder="Click to select date range.">
                  </div>
                  <!-- /.input group -->
                </div>
                <!-- /.form group -->
              </div>
              <div class="col-lg-3">
                <div class="form-group">
                    <select class="select2" id="filter_status" name="filter_status" data-placeholder="Filter Status" style="width: 100%;">
                    <option></option>
                    <option value="open">Open</option>
                    <option value="canceled" >Canceled</option>
                    <option value="closed" >Closed</option>
                    </select>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="form-group">
                   <select class="select2" id="filter_supplier" name="filter_supplier" data-placeholder="Filter Supplier" style="width: 100%;">
                    </select>
                </div>
              </div>
              <div class="col-lg-1">
              <button id="filter" class="btn btn-primary float-left"><i class="fas fa-filter"></i> Filter</button>
              </div>
              <div class="col-lg-12">
                <table class="table table-hover" id="nav5purchase_order_table" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>PO No.</th>
                      <th>Transaction No.</th>
                      <th>Supplier</th>
                      <th>Order Date</th>
                      <th>Status</th>
                      <th>Total</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
				  </div>
					<!-- /.tab-pane -->                  

             </div>
            <!-- /.tab-content -->
          </div><!-- /.card-body -->
          <div class="modal fade" id="modal-default">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Add Item</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                  <form role="form" class="form-horizontal" method="post" id="add_item_table">
                  <div class="form-group row" id="item_id_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Item ID</label>
                      <div class="col-sm-9">
                        <select class="select2" id="item_id_modal" name="item_id_modal" data-placeholder="Select a Item ID" style="width: 100%;">
                        </select>
                      </div>
                    </div>
                    <div class="form-group row"  id="item_name_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Item Name</label>
                      <div class="col-sm-9">
                        <select class="select2" id="item_name_modal" name="item_name_modal" data-placeholder="Select a Item" style="width: 100%;">
                        </select>
                        <input type="text" class="form-control" id="item_name" name="item_name" placeholder="Price" hidden>
                        <input type="text" class="form-control" id="primary_id" name="primary_id" placeholder="Price" hidden>
                      </div>
                    </div>
                    <div class="form-group row"  id="unit_price_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Price</label>
                      <div class="col-sm-9">
                         <input type="text" class="form-control" id="unit_price_modal" name="unit_price_modal" placeholder="Price" readonly>
                      </div>
                    </div>
                    <div class="form-group row" id="quantity_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Quantity</label>
                      <div class="col-sm-6">
                         <input type="text" class="form-control" id="quantity_modal" name="quantity_modal" placeholder="Quantity">
                      </div>
                      <div class="col-sm-3">
                         <input type="text" class="form-control" id="item_uom_modal" name="item_uom_modal" placeholder="UOM(Item)" readonly>
                      </div>
                    </div>
                    <div class="form-group row" id="subtotal_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Subtotal</label>
                      <div class="col-sm-9">
                         <input type="text" class="form-control" id="subtotal_modal" name="subtotal_modal" placeholder="Subtotal" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" id="modal_add_close" class="btn btn-default">Add & Close</button>
                    <button type="button" id="modal_add_new" class="btn btn-default">Add & New</button>
                    <button type="button" id="modal_close" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                  </form>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
          </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection