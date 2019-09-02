@extends('admin.partials.master')

@section('style')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
@endsection

@section('script')
<!-- Select2 -->
<script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>

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

get_purchase_order_id();
get_supplier_list();
get_total();

function get_purchase_order_id(){
    $.ajax({
        type: 'get',
        url: "{{ route('purchase_order.get_purchase_order_id') }}",
        success: function(data) {
           $('#purchase_order_id').text(data.purchase_order_id);
           $('#transaction_id').text(data.transaction_id);
           $('#order_date').val(data.order_date);
        },
        error: function(error){
          console.log('error');
        }
     }); 
}


function get_supplier_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('purchase_order.api_supplier_list') }}",
        success: function(data) {

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, false, false);
            $('#supplier').append(newOption).trigger('change');
        })
        $('#supplier').select2().val(null).trigger("change");
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

function get_total(){
  var total = 0;
      $(".row_subtotal").each(function(){
        total += parseFloat($(this).text().replace(/,/g, ''));
      });
      $('#total').text(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
}

function clearError(){
    $( ".is-invalid" ).removeClass("is-invalid");
    $( ".help-block" ).remove();
}

function clean_modal(){
            $('#unit_price_modal').val('');
            $('#item_uom_modal').val('');
            $('#primary_id').val('');
            $('#quantity_modal').val('');
            $('#subtotal_modal').val('');
            $('#item_name_modal').select2().val(null).trigger("change");
            $('#item_id_modal').select2().val(null).trigger("change");
}

$('#supplier').on('select2:select', function (e) {
    var data = e.params.data;
    $.ajax({
        type: 'get',
        url: "/purchase_order/get_supplier_info/"+data.id,
        success: function(data) {

            $('#supplier_company').val(data.supplier_company);
            $('#supplier_id').val(data.supplier_id);
            $('#item_name_modal option').remove();
            $('#item_id_modal option').remove();
            $("#add_item").removeAttr('disabled');

            $.each(data.supplier_item, function(key, value){                         
              var newOption = new Option(value.name, value.id, false, false);
              $('#item_name_modal').append(newOption).trigger('change');
            });

            $.each(data.supplier_item, function(key, value){                         
              var newOption = new Option(value.item_id, value.item_id, false, false);
              $('#item_id_modal').append(newOption).trigger('change');
            });

            clean_modal();
        },
        error: function(error){
          console.log('error');
        }
     }); 
 });

 $('#item_name_modal').on('select2:select', function (e) {
    var modal_data = e.params.data;
     $.ajax({
        type: 'get',
        url: "/purchase_order/get_supplier_item_info_via_id/"+modal_data.id,
        success: function(data) {
            $('#item_id_modal').select2().val(data.item_id).trigger("change");
            $('#unit_price_modal').val(data.unit_price);
            $('#item_uom_modal').val(data.item_uom);
            $('#item_name').val(data.item_name);
            $('#primary_id').val(data.id);
            $('#quantity_modal').val('');
            $('#subtotal_modal').val('');
        },
        error: function(error){
          console.log('error');
        }
     });    

 });

  $('#item_id_modal').on('select2:select', function (e) {
    var modal_data = e.params.data;

     $.ajax({
        type: 'get',
        url: "/purchase_order/get_supplier_item_info_via_item_id/"+modal_data.id,
        success: function(data) {
            $('#item_name_modal').select2().val(data.id).trigger("change");
            $('#unit_price_modal').val(data.unit_price);
            $('#item_uom_modal').val(data.item_uom);
            $('#item_name').val(data.item_name);
            $('#primary_id').val(data.id);
            $('#quantity_modal').val('');
            $('#subtotal_modal').val('');
        },
        error: function(error){
          console.log('error');
        }
     });    

 });


$("#quantity_modal" ).change(function() {

var quantity = $('#quantity_modal').val();
var price = $('#unit_price_modal').val();
var subtotal = quantity*price;

$('#subtotal_modal').val(subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

});

$('#add_item').on('click', function (event) {

  event.preventDefault();
  $('#modal-default').modal('show');
  

});

$('#modal_add_close').on('click', function (event) {

event.preventDefault();

var form_data = $('#add_item_table').serialize();
var item_id = $('#primary_id').val();
var item_id_modal = $('#item_id_modal').val();
var item_name = $('#item_name').val();
var quantity = $('#quantity_modal').val();
var uom_item = $('#item_uom_modal').val();
var item_price = $('#unit_price_modal').val();
var subtotal_table = $('#subtotal_modal').val();
var subtotal = $('#subtotal_modal').val().replace(/,/g, '');

   Pace.restart();
   
   Pace.track(function () {
             $.ajax({
                   url: "{{ route('purchase_order.validation_add_item_table') }}",
                   type: "post",
                   data:form_data,
                   success: function(data) {
                    $('#modal-default').modal('hide');
                     Toast.fire({
                       type: 'success',
                       title: name+' Successfully Added.'
                     })
                     var html = '';
                      html += '<tr>';
                      html += '<td><input type="text" class="form-control" name="row_item_id[]" value="'+item_id+'" hidden>'+item_id_modal+'</td>';
                      html += '<td><input type="text" class="form-control" value="'+item_name+'" hidden>'+item_name+'</td>';
                      html += '<td><input type="text" class="form-control" name="row_quantity[]" value="'+quantity+'" hidden>'+quantity+'</td>';
                      html += '<td><input type="text" class="form-control" value="'+uom_item+'" hidden>'+uom_item+'</td>';
                      html += '<td><input type="text" class="form-control" name="row_item_price[]" value="'+item_price+'" hidden>'+item_price+'</td>';
                      html += '<td class="row_subtotal">'+subtotal_table+'<input type="text" class="form-control" name="row_subtotal[]" value="'+subtotal+'" hidden></td>';
                      html += '<td><button class="btn btn-sm btn-default" id="remove_table_item"><i class="fas fa-times"></i></button></td>';
                      html += '</tr>';

                      $('#purchase_order_table').prepend(html);

                      get_total();
                      clean_modal();
                      clearError();
                   },
                   error: function(error){
                        clearError();
                        $.each(error.responseJSON.errors, function(key, value){                         
                              $("input[id="+key+"]").addClass("is-invalid");
                        });
                        Toast.fire({
                        type: 'error',
                        title: 'Invalid Inputs.'
                      })
                   }
               }); 
   });  

           


});


$('#modal_add_new').on('click', function (event) {
  

event.preventDefault();

var form_data = $('#add_item_table').serialize();
var item_id = $('#primary_id').val();
var item_id_modal = $('#item_id_modal').val();
var item_name = $('#item_name').val();
var quantity = $('#quantity_modal').val();
var uom_item = $('#item_uom_modal').val();
var item_price = $('#unit_price_modal').val();
var subtotal_table = $('#subtotal_modal').val();
var subtotal = $('#subtotal_modal').val().replace(/,/g, '');

      Pace.restart();
   
       Pace.track(function () {
                 $.ajax({
                       url: "{{ route('purchase_order.validation_add_item_table') }}",
                       type: "post",
                       data:form_data,
                       success: function(data) {
                         Toast.fire({
                           type: 'success',
                           title: name+' Successfully Added.'
                         })
                         var html = '';
                          html += '<tr>';
                          html += '<td><input type="text" class="form-control" name="row_item_id[]" value="'+item_id+'" hidden>'+item_id_modal+'</td>';
                          html += '<td><input type="text" class="form-control" value="'+item_name+'" hidden>'+item_name+'</td>';
                          html += '<td><input type="text" class="form-control" name="row_quantity[]" value="'+quantity+'" hidden>'+quantity+'</td>';
                          html += '<td><input type="text" class="form-control" value="'+uom_item+'" hidden>'+uom_item+'</td>';
                          html += '<td><input type="text" class="form-control" name="row_item_price[]" value="'+item_price+'" hidden>'+item_price+'</td>';
                          html += '<td class="row_subtotal">'+subtotal_table+'<input type="text" class="form-control" name="row_subtotal[]" value="'+subtotal+'" hidden></td>';
                          html += '<td><button class="btn btn-sm btn-default" id="remove_table_item"><i class="fas fa-times"></i></button></td>';
                          html += '</tr>';

                          $('#purchase_order_table').prepend(html);

                          get_total();
                          clean_modal();
                          clearError();
                       },
                       error: function(error){
                            clearError();
                            $.each(error.responseJSON.errors, function(key, value){                         
                                  $("input[id="+key+"]").addClass("is-invalid");
                            });
                            Toast.fire({
                            type: 'error',
                            title: 'Invalid Inputs.'
                          })
                       }
                   }); 
       });  

});

$(document).on('click', '#remove_table_item', function(){
     $(this).closest('tr').remove();
     get_total();
});

function print_email_po(id){
    $.ajax({
          url: "{{ route('purchase_order.store') }}",
          type: "post",
          data:form_data
      
          success: function(data) {
                          
            print_email_po( $('#purchase_order_id').text());

            Toast.fire({
              type: 'success',
              title: name+' Successfully Added.'
            })

            $( "#nav_po_create" ).removeClass( "active" );
            $( "#nav_print_email_po" ).addClass( "active" );
            $( "#po_create" ).removeClass( "active" );
            $( "#print_email_po" ).addClass( "active" );



            $("#add_item").attr("disabled", true);
            $('#supplier').select2().val(null).trigger("change");
            $('#supplier_id').val('');
            $('#supplier_company').val('');
            $('#purchase_order_table > tbody  tr:not(:last)').remove();

            get_purchase_order_id();
            get_supplier_list();
            get_total();
          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Invalid Inputs.'
            })
          }
       });   
}

$(document).on('click','#submit',function(event){
  event.preventDefault();

      Pace.restart();
     
      var form_data = $('#confirm_order').serialize();
    
        Pace.track(function () {
                  $.ajax({
                        url: "{{ route('purchase_order.store') }}",
                        type: "post",
                        data:form_data
                        + "&purchase_order_id=" + $('#purchase_order_id').text()
                        + "&transaction_id=" + $('#transaction_id').text()
                        + "&total=" + $('#total').text().replace(/,/g, ''),
                        success: function(data) {

                          print_email_po( $('#purchase_order_id').text());

                          Toast.fire({
                            type: 'success',
                            title: name+' Successfully Added.'
                          })

                          $( "#nav_po_create" ).removeClass( "active" );
                          $( "#nav_print_email_po" ).addClass( "active" );
                          $( "#po_create" ).removeClass( "active" );
                          $( "#print_email_po" ).addClass( "active" );



                          $("#add_item").attr("disabled", true);
                          $('#supplier').select2().val(null).trigger("change");
                          $('#supplier_id').val('');
                          $('#supplier_company').val('');
                          $('#purchase_order_table > tbody  tr:not(:last)').remove();

                          get_purchase_order_id();
                          get_supplier_list();
                          get_total();
                        },
                        error: function(error){
                          Toast.fire({
                            type: 'error',
                            title: 'Invalid Inputs.'
                          })
                        }
                    }); 
        });  

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
            <h1>Purchase Order</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Purchase Order</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link active" id="nav_po_create" href="#po_create" data-toggle="tab"><i class="fas fa-plus"></i> Create Purchase Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_print_email_po" href="#print_email_po" data-toggle="tab"><i class="fas fa-print"></i> Print and Email Purchase Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_po_list" href="#po_list" data-toggle="tab"><i class="fas fa-list"></i> Purchase Order List</a></li>
            </ul>
          </div><!-- /.card-header -->
          <div class="card-body">
          <div class="tab-content">
          <div class="active tab-pane" id="po_create">
            <form role="form" method="post" id="confirm_order">
              <div class="row" style="margin-bottom:25px">
                <div class="col-lg-6" style="margin-bottom:25px;">
                  <h2>Purchase Order No : 
                    <span id="purchase_order_id" style="color:red;"></span>
                  </h2>
                </div>
                <div class="col-lg-6" style="margin-bottom:25px;">
                  <h6 class="float-right">Transaction No : 
                    <span id="transaction_id"></span>
                  </h6>
                </div>
                <div class="col-lg-4 col-md-12">
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Supplier</label>
                    <div class="col-sm-8">
                      <select class="select2" id="supplier" name="supplier_id" data-placeholder="Select a Supplier" style="width: 100%;"></select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="supplier_id" placeholder="Supplier ID" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier Company</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="supplier_company" placeholder="Supplier Company" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-2 col-md-12"></div>
                    <div class="col-lg-4 col-md-12">
                      <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-3 control-label">Order Date</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control" id="order_date" name="order_date" placeholder="Order Date" readonly>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="inputEmail3" class="col-sm-3 control-label">Deliver To</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" id="deliver_to" name="deliver_to" value="Main Warehouse" placeholder="Deliver To" readonly>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-lg-12">
                          <div class="card">
                            <div class="card-header p-2">
                              <ul class="nav nav-pills float-right">
                                <li class="nav-item">
                                  <button class="btn btn-block btn-primary" id="add_item" disabled>
                                    <i class="fas fa-plus"></i>  Add Item
                                  </button>
                                </li>
                              </ul>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                              <div class="tab-content">
                                <div class="active tab-pane" id="Items">
                                  <table class="table" id="purchase_order_table">
                                    <thead>
                                      <tr>
                                        <th>Item ID</th>
                                        <th>Name</th>
                                        <th>Quantity</th>
                                        <th>UOM(Item)</th>
                                        <th>Item Price</th>
                                        <th>Sub Total</th>
                                        <th></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr>
                                        <th colspan="5" style="text-align:right">Total:</th>
                                        <th colspan="7" style="text-align:center">
                                          <span id="total"></span>
                                        </th>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                                <!-- /.tab-pane -->
                              </div>
                              <!-- /.tab-content -->
                            </div>
                            <!-- /.card-body -->
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-12">
                        <button id="submit" class="btn btn-primary">Create</button>
                        <button id="back" class="btn btn-primary">Back</button>
                      </div>
            </form>
          </div>
           <!-- /.tab-pane -->
           <div class="tab-pane" id="print_email_po">
            <div class="row" style="margin-bottom:25px">
              <div class="col-lg-4 col-md-12">
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
                  <div class="col-lg-4 col-md-12">
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="nav2_supplier_id" placeholder="Supplier ID" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Supplier Name</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="nav2_supplier_name" placeholder="Supplier Company" readonly>
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
                      <table class="table" id="purchase_order_table">
                        <thead>
                          <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>UOM(Item)</th>
                            <th>Item Price</th>
                            <th>Sub Total</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <th colspan="5" style="text-align:right">Total:</th>
                            <th colspan="7" style="text-align:center">
                              <span id="total"></span>
                            </th>
                          </tr>
                        </tbody>
                      </table>
                      <div class="col-lg-12">
                        <button id="submit" class="btn btn-primary">Print PO</button>
                        <button id="back" class="btn btn-primary">Send PO via Email</button>
                      </div>
					</div>
          <!-- /.tab-pane -->    
          <div class="tab-pane" id="po_list">
            <table class="table" id="purchase_order_table">
              <thead>
                <tr>
                  <th>PO No.</th>
                  <th>Transaction No.</th>
                  <th>Supplier</th>
                  <th>Order Date</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
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