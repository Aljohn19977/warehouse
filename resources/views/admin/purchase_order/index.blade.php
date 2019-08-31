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
  var sum = 0;
      $(".row_subtotal").each(function(){
        sum += parseFloat($(this).text().replace(/,/g, ''));
      });
      $('#sum').text(sum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
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
var item_id = $('#item_id_modal').val();
var item_name = $('#item_name').val();
var quantity = $('#quantity_modal').val();
var uom_item = $('#item_uom_modal').val();
var item_price = $('#unit_price_modal').val();
var subtotal = $('#subtotal_modal').val();

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
                      html += '<td><input type="text" class="form-control" name="row_item_id[]" value="'+item_id+'" readonly></td>';
                      html += '<td><input type="text" class="form-control" name="row_item_name[]" value="'+item_name+'" readonly></td>';
                      html += '<td><input type="text" class="form-control" name="row_quantity[]" value="'+quantity+'" readonly></td>';
                      html += '<td><input type="text" class="form-control" name="row_item_uom[]" value="'+uom_item+'" readonly></td>';
                      html += '<td><input type="text" class="form-control" name="row_item_price[]" value="'+item_price+'" readonly></td>';
                      html += '<td class="row_subtotal">'+subtotal+'<input type="text" class="form-control" name="row_subtotal[]" value="'+subtotal+'" hidden></td>';
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
var item_id = $('#item_id_modal').val();
var item_name = $('#item_name').val();
var quantity = $('#quantity_modal').val();
var uom_item = $('#item_uom_modal').val();
var item_price = $('#unit_price_modal').val();
var subtotal = $('#subtotal_modal').val();

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
                          html += '<td><input type="text" class="form-control" name="row_item_id[]" value="'+item_id+'" readonly></td>';
                          html += '<td><input type="text" class="form-control" name="row_item_name[]" value="'+item_name+'" readonly></td>';
                          html += '<td><input type="text" class="form-control" name="row_quantity[]" value="'+quantity+'" readonly></td>';
                          html += '<td><input type="text" class="form-control" name="row_item_uom[]" value="'+uom_item+'" readonly></td>';
                          html += '<td><input type="text" class="form-control" name="row_item_price[]" value="'+item_price+'" readonly></td>';
                          html += '<td class="row_subtotal">'+subtotal+'<input type="text" class="form-control" name="row_subtotal[]" value="'+subtotal+'" hidden></td>';
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

$(document).on('click','#submit',function(event){
  event.preventDefault();

      Pace.restart();
     
      var form_data = $('#confirm_order').serialize();
    
        Pace.track(function () {
                  $.ajax({
                        url: "{{ route('purchase_order.store') }}",
                        type: "post",
                        data:form_data,
                        success: function(data) {
                          Toast.fire({
                            type: 'success',
                            title: name+' Successfully Added.'
                          })
                          $('#head_button').removeAttr('hidden');
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
        <div class="card card-default">
          <div class="card-header" id="head_button" hidden>
            <button class="btn btn-primary" ><i class="fas fa-print"></i> Print PO</button>
            <button class="btn btn-primary" ><i class="fas fa-envelope"></i> Send PO by Email</button>
            <button class="btn btn-danger" ><i class="fas fa-window-close"></i>  Cancel Order</button>
          </div>
          <!-- /.card-header -->
          <form role="form" class="form-horizontal" method="post" id="confirm_order">
            <div class="card-body">
              <div class="row" style="margin-bottom:25px">
                <div class="col-lg-6" style="margin-bottom:25px;">
                  <h2>Purchase Order No : <span id="purchase_order_id" style="color:red;"></span></h2>
                </div>
                <div class="col-lg-6" style="margin-bottom:25px;">
                  <h6 class="float-right">Transaction No : <span id="transaction_id"></span></h6>
                </div>
                <div class="col-lg-4 col-md-12">
                   <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier</label>
                      <div class="col-sm-8">
                        <select class="select2" id="supplier" name="supplier" data-placeholder="Select a Supplier" style="width: 100%;">
                        </select>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                      <div class="col-sm-8">
                         <input type="text" class="form-control" id="supplier_id" name="supplier_id" placeholder="Supplier ID" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier Company</label>
                      <div class="col-sm-8">
                         <input type="text" class="form-control" id="supplier_company" name="supplier_company" placeholder="Supplier Company" readonly>
                      </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-12">
 
                </div>
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
                      <li class="nav-item"><button class="btn btn-block btn-primary" id="add_item" disabled><i class="fas fa-plus"></i>  Add Item</button></li>
                    </ul>
                  </div><!-- /.card-header -->
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
                          <th colspan="7" style="text-align:center"><span id="sum"></span></th>
                        </tr>
                          </tbody>
                        </table>                         
                      </div>
                      <!-- /.tab-pane -->
                    </div>
                    <!-- /.tab-content -->
                  </div><!-- /.card-body -->
                </div>    
               </div>
              </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <button id="submit" class="btn btn-primary">Confirm Order</button>
              <a href="{{ route('item.index') }}" class="btn btn-primary">Cancel</a>
            </div>
          </form>
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
        <!-- /.card -->
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection