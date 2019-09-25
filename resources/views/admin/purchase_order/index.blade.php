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


get_purchase_order_id();
get_supplier_list();
get_weight_total();
get_volume_total();
get_subtotal();
get_total();
get_tax();


purchase_order_datatable();


get_purchase_order_cancel_list();
get_purchase_order_print_list();
get_purchase_order_view_list();


    var startDate;
    var endDate;

      $('#filter_date').daterangepicker({
      autoUpdateInput: false,
      locale: {
          cancelLabel: 'Clear'
      },
      ranges: {
              'Today': [moment(), moment()],
              'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
              'Last 7 Days': [moment().subtract('days', 6), moment()],
              'Last 30 Days': [moment().subtract('days', 29), moment()],
              'This Month': [moment().startOf('month'), moment().endOf('month')],
              'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
              'Last Year': [moment().subtract('year', 1),moment().subtract('year', 1)]
            },
  });

  $('#filter_date').val('');
  
  $('#filter_date').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
      startDate = picker.startDate.format('YYYY-MM-DD');
      endDate = picker.endDate.format('YYYY-MM-DD');  
  });

  $('#filter_date').on('cancel.daterangepicker', function(ev, picker) {
    $('#filter_date').val('');
      startDate = null;
      endDate = null;  
  });


function purchase_order_datatable(start_date,end_date,filter_status,filter_supplier){
  $('#nav5purchase_order_table').DataTable({
              processing: true,
              serverSide: true,
              responsive: true,
              paging: true,
              lengthChange: true,
              searching: true,
              ordering: true,
              autoWidth: true,
              ajax: {
                      'url' : "{{ route('purchase_order.api_get_all_purchase_order')}}",
                      'dataType' : 'json',
                      'type' : 'post',
                      'data' : {
                                  'start_date' : start_date,
                                  'end_date' : end_date,
                                  'filter_status': filter_status,
                                  'filter_supplier': filter_supplier
                               } 
              },
                columns : [
                            {"data" : "purchase_order_id"},
                            {"data" : "transaction_id"},
                            {"data" : "supplier_id"},
                            {"data" : "order_date"},
                            {"data" : "status"},
                            {"data" : "total"},
                            {"data" : "action"}
                          ],
  });
}


$('#filter').on('click', function (event) {

event.preventDefault();

var filter_status = $('#filter_status').val();
var start_date = startDate;
var end_date = endDate;
var filter_supplier = $('#filter_supplier').val();

if (start_date != null && end_date != null){
  $('#nav5purchase_order_table').DataTable().destroy();
  purchase_order_datatable(start_date,end_date,filter_status,filter_supplier);
  console.log(start_date+end_date);
}else if(filter_status != '' ){
  $('#nav5purchase_order_table').DataTable().destroy();
  purchase_order_datatable(start_date,end_date,filter_status,filter_supplier);
}else if(filter_supplier != null ){
  $('#nav5purchase_order_table').DataTable().destroy();
  purchase_order_datatable(start_date,end_date,filter_status,filter_supplier);
}

});

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

        $('#supplier option').remove();

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, false, false);
            $('#supplier').append(newOption).trigger('change');
        })
        $('#supplier').select2().val(null).trigger("change");

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name+' - '+row.supplier_id, row.id, false, false);
            $('#filter_supplier').append(newOption).trigger('change');
        })
        $('#filter_supplier').select2().val(null).trigger("change");
        },
        error: function(error){
          console.log('error');
        }
     }); 
}

// function get_purchase_order_list(po_id,nav){
//     $.ajax({
//         type: 'get',
//         url: "/purchase_order/api/list/"+nav,
//         success: function(data) {
//             $('#nav2_purchase_order_no option').remove();
//             $('#nav2_transaction_no option').remove();
//             $('#nav3_purchase_order_no option').remove();
//             $('#nav3_transaction_no option').remove();
//             $('#nav4_purchase_order_no option').remove();
//             $('#nav4_transaction_no option').remove();

//             JSON.parse(data).data.forEach(row => {
//                 var newOption = new Option(row.purchase_order_id, row.purchase_order_id, false, false);
//                 $('#nav2_purchase_order_no').append(newOption).trigger('change');
//             })

//             JSON.parse(data).data.forEach(row => {
//                 var newOption = new Option(row.purchase_order_id, row.purchase_order_id, false, false);
//                 $('#nav3_purchase_order_no').append(newOption).trigger('change');
//             })

//             JSON.parse(data).data.forEach(row => {
//                 var newOption = new Option(row.purchase_order_id, row.purchase_order_id, false, false);
//                 $('#nav4_purchase_order_no').append(newOption).trigger('change');
//             })

//             JSON.parse(data).data.forEach(row => {
//                 var newOption = new Option(row.transaction_id, row.purchase_order_id, false, false);
//                 $('#nav2_transaction_no').append(newOption).trigger('change');
//             })

//             JSON.parse(data).data.forEach(row => {
//                 var newOption = new Option(row.transaction_id, row.purchase_order_id, false, false);
//                 $('#nav3_transaction_no').append(newOption).trigger('change');
//             })

            
//             JSON.parse(data).data.forEach(row => {
//                 var newOption = new Option(row.transaction_id, row.purchase_order_id, false, false);
//                 $('#nav4_transaction_no').append(newOption).trigger('change');
//             })

//               $('#nav2_purchase_order_no').select2().val(null).trigger("change");
//               $('#nav2_transaction_no').select2().val(null).trigger("change");
//               $('#nav3_purchase_order_no').select2().val(null).trigger("change");
//               $('#nav3_transaction_no').select2().val(null).trigger("change");
//               $('#nav4_purchase_order_no').select2().val(null).trigger("change");
//               $('#nav4_transaction_no').select2().val(null).trigger("change");

//             if(nav == 2){
//                 $('#nav2_purchase_order_no').select2().val(po_id).trigger("change");
//                 $('#nav2_transaction_no').select2().val(po_id).trigger("change");
//             }else if(nav == 3){
//                 $('#nav3_purchase_order_no').select2().val(po_id).trigger("change");
//                 $('#nav3_transaction_no').select2().val(po_id).trigger("change");
//             }else if(nav == 4){
//                 $('#nav4_purchase_order_no').select2().val(po_id).trigger("change");
//                 $('#nav4_transaction_no').select2().val(po_id).trigger("change");
//             }
            
//         },
//         error: function(error){
//           console.log('error');
//         }
//      }); 
// }

function get_purchase_order_cancel_list(po_id){
    var nav = 3;
    $.ajax({
        type: 'get',
        url: "/purchase_order/api/list/"+nav,
        success: function(data) {

            $('#nav3_purchase_order_no option').remove();
            $('#nav3_transaction_no option').remove();


            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.purchase_order_id, row.purchase_order_id, false, false);
                $('#nav3_purchase_order_no').append(newOption).trigger('change');
            })

            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.transaction_id, row.purchase_order_id, false, false);
                $('#nav3_transaction_no').append(newOption).trigger('change');
            })


            if(po_id != ''){
                $('#nav3_purchase_order_no').select2().val(po_id).trigger("change");
                $('#nav3_transaction_no').select2().val(po_id).trigger("change");
            }else{
              $('#nav3_purchase_order_no').select2().val(null).trigger("change");
              $('#nav3_transaction_no').select2().val(null).trigger("change");
            }
            
        },
        error: function(error){
          console.log('error');
        }
     }); 
}

function get_purchase_order_print_list(po_id){
    var nav = 2;
    $.ajax({
        type: 'get',
        url: "/purchase_order/api/list/"+nav,
        success: function(data) {

            $('#nav2_purchase_order_no option').remove();
            $('#nav2_transaction_no option').remove();


            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.purchase_order_id, row.purchase_order_id, false, false);
                $('#nav2_purchase_order_no').append(newOption).trigger('change');
            })

            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.transaction_id, row.purchase_order_id, false, false);
                $('#nav2_transaction_no').append(newOption).trigger('change');
            })


            if(po_id != ''){
                $('#nav2_purchase_order_no').select2().val(po_id).trigger("change");
                $('#nav2_transaction_no').select2().val(po_id).trigger("change");
            }else{
              $('#nav2_purchase_order_no').select2().val(null).trigger("change");
              $('#nav2_transaction_no').select2().val(null).trigger("change");
            }
            
        },
        error: function(error){
          console.log('error');
        }
     }); 
}

function get_purchase_order_view_list(po_id){
    var nav = 4;
    $.ajax({
        type: 'get',
        url: "/purchase_order/api/list/"+nav,
        success: function(data) {

            $('#nav4_purchase_order_no option').remove();
            $('#nav4_transaction_no option').remove();


            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.purchase_order_id, row.purchase_order_id, false, false);
                $('#nav4_purchase_order_no').append(newOption).trigger('change');
            })

            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.transaction_id, row.purchase_order_id, false, false);
                $('#nav4_transaction_no').append(newOption).trigger('change');
            })


            if(po_id != ''){
                $('#nav4_purchase_order_no').select2().val(po_id).trigger("change");
                $('#nav4_transaction_no').select2().val(po_id).trigger("change");
            }else{
              $('#nav4_purchase_order_no').select2().val(null).trigger("change");
              $('#nav4_transaction_no').select2().val(null).trigger("change");
            }
            
        },
        error: function(error){
          console.log('error');
        }
     }); 
}

function get_total(){
  var total = 0;
      $(".row_total").each(function(){
        total += parseFloat($(this).text().replace(/,/g, ''));
      });
      $('#total').text(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
}

function get_tax(){
  var total = 0;
      $(".row_tax").each(function(){
        total += parseFloat($(this).text().replace(/,/g, ''));
      });
      $('#tax').text(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
}

function get_subtotal(){
  var total = 0;
      $(".row_subtotal").each(function(){
        total += parseFloat($(this).text().replace(/,/g, ''));
      });
      $('#subtotal').text(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
}

function get_weight_total(){
  var total = 0;
      $(".row_weight").each(function(){
        total += parseFloat($(this).text().replace(/,/g, ''));
      });

      $('#weight_total').text(Math.round(total * 100) / 100);
}

function get_volume_total(){
  var total = 0;
      $(".row_volume").each(function(){
        total += parseFloat($(this).text().replace(/,/g, ''));
      });
      $('#volume_total').text(Math.round(total * 100) / 100);
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
            $('#volume_modal').val('');
            $('#weight_modal').val('');
            $('#subtotal_modal').val('');
            $('#tax_modal').val('');
            $('#taxtotal_modal').val('');
            $('#total_modal').val('');
            $('#item_name_modal').select2().val(null).trigger("change");
            $('#item_id_modal').select2().val(null).trigger("change");
}

function print_email_po(id){
    Pace.restart();

    Pace.track(function () {
    $.ajax({
          url: "/purchase_order/get_purchase_order_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
            get_purchase_order_print_list(data.purchase_order_id);
            $('#nav2_order_date').val(data.order_date);
            $('#nav2_deliver_to').val(data.deliver_to);
            $('#nav2_supplier_id').val(data.supplier_id);
            $('#nav2_supplier_company').val(data.supplier_company);
            $('#nav2_supplier_name').val(data.supplier_name);
            $('#nav2total').text(data.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav2comments').text(data.comments);
            $('#nav2subtotal').text(data.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav2tax').text(data.total_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav2weight_total').text(data.total_weight.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav2volume_total').text(data.total_volume.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav2purchase_order_table  > tbody tr').remove();


              $("#email_po").removeAttr("disabled");
              $("#print_po").removeAttr("disabled");
            $.each(data.purchase_order_items, function(key, value){                         
                console.log(value.item_id);
                var html = '';
                          html += '<tr>';
                          html += '<td>'+value.item_id+'</td>';
                          html += '<td>'+value.item_name+'</td>';
                          html += '<td>'+value.quantity+'</td>';
                          html += '<td>'+value.item_uom+'</td>';
                          html += '<td>'+value.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.line_total+'</td>';
                          html += '<td>'+value.tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.tax_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '</tr>';

                          $('#nav2purchase_order_table').prepend(html);
             });

          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Invalid Inputs.'
            })
          }
        })      
       });   
}

function cancel_po(id){
    Pace.restart();

    Pace.track(function () {
    $.ajax({
          url: "/purchase_order/get_purchase_order_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
            get_purchase_order_cancel_list(data.purchase_order_id);
            $("#cancel").removeAttr("disabled");
            $('#nav3_order_date').val(data.order_date);
            $('#nav3_deliver_to').val(data.deliver_to);
            $('#nav3_supplier_id').val(data.supplier_id);
            $('#nav3_supplier_company').val(data.supplier_company);
            $('#nav3_supplier_name').val(data.supplier_name);
            $('#nav3total').text(data.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav3comments').text(data.comments);
            $('#nav3subtotal').text(data.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav3tax').text(data.total_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav3weight_total').text(data.total_weight.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav3volume_total').text(data.total_volume.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav3purchase_order_table  > tbody tr').remove();


            $.each(data.purchase_order_items, function(key, value){                         
                console.log(value.item_id);
                var html = '';
                          html += '<tr>';
                          html += '<td>'+value.item_id+'</td>';
                          html += '<td>'+value.item_name+'</td>';
                          html += '<td>'+value.quantity+'</td>';
                          html += '<td>'+value.item_uom+'</td>';
                          html += '<td>'+value.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.line_total+'</td>';
                          html += '<td>'+value.tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.tax_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '</tr>';

                          $('#nav3purchase_order_table').prepend(html);
             });

          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Invalid Inputs.'
            })
          }
        })      
       });   
}

function view_po(id){
    Pace.restart();

    Pace.track(function () {
    $.ajax({
          url: "/purchase_order/get_purchase_order_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
            
            get_purchase_order_view_list(data.purchase_order_id);
            $('#nav4_order_date').val(data.order_date);
            $('#nav4_deliver_to').val(data.deliver_to);
            $('#nav4_supplier_id').val(data.supplier_id);
            $('#nav4_supplier_company').val(data.supplier_company);
            $('#nav4_supplier_name').val(data.supplier_name);
            $('#status_icon').remove();

            if(data.status == 'Placed'){
              $('#nav4_status').append('<button id="status_icon" class="btn btn-xs btn-flat btn-success">Placed</button>');
            }else if (data.status =='Received'){
              $('#nav4_status').append('<button id="status_icon" class="btn btn-xs btn-flat btn-primary">Recieved</button>');
            }else if (data.status =='Receiving'){
              $('#nav4_status').append('<button id="status_icon" class="btn btn-xs btn-flat btn-warning">Receiving</button>');
            }
            else if(data.status == 'Canceled'){
              $('#nav4_status').append('<button id="status_icon" class="btn btn-xs btn-flat btn-danger">Canceled</button> ');
            }

         
            $('#nav4total').text(data.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav4comments').text(data.comments);
            $('#nav4subtotal').text(data.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav4tax').text(data.total_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav4weight_total').text(data.total_weight.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#nav4volume_total').text(data.total_volume.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

            $('#nav4purchase_order_table  > tbody tr').remove();


            $.each(data.purchase_order_items, function(key, value){                         
                console.log(value.item_id);
                var html = '';
                          html += '<tr>';
                          html += '<td>'+value.item_id+'</td>';
                          html += '<td>'+value.item_name+'</td>';
                          html += '<td>'+value.quantity+'</td>';
                          html += '<td>'+value.item_uom+'</td>';
                          html += '<td>'+value.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.line_total+'</td>';
                          html += '<td>'+value.tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.tax_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '<td>'+value.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                          html += '</tr>';

                          $('#nav4purchase_order_table').prepend(html);
             });

          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Invalid Inputs.'
            })
          }
        })      
       });   
}


$('#supplier').on('select2:select', function (e) {
    Pace.restart();

    Pace.track(function () {
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

            $('#purchase_order_table  > tbody tr ').remove();
            $('#total').text(0);

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
     }) 
    })
});

 $('#item_name_modal').on('select2:select', function (e) {
    var modal_data = e.params.data;
     $.ajax({
        type: 'get',
        url: "/purchase_order/get_supplier_item_info_via_id/"+modal_data.id,
        success: function(data) {
            $('#item_id_modal').select2().val(data.item_id).trigger("change");
            $('#unit_price_modal').val(data.purchase_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#item_uom_modal').val(data.item_uom);
            $('#item_name').val(data.item_name);
            $('#tax_modal').val(data.tax);
            $('#primary_id').val(data.id);
            $('#volume_modal').val(data.volume);
            $('#type_modal').val(data.type);
            $('#weight_modal').val(data.weight);
            $('#quantity_modal').val('');
            $('#subtotal_modal').val('');
            $('#total_modal').val('');
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
            $('#unit_price_modal').val(data.purchase_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $('#item_uom_modal').val(data.item_uom);
            $('#item_name').val(data.item_name);
            $('#tax_modal').val(data.tax);
            $('#primary_id').val(data.id);
            $('#volume_modal').val(data.volume);
            $('#type_modal').val(data.type);
            $('#weight_modal').val(data.weight);
            $('#quantity_modal').val('');
            $('#subtotal_modal').val('');
            $('#total_modal').val('');
        },
        error: function(error){
          console.log('error');
        }
     });    

});

$('#nav2_purchase_order_no').on('select2:select', function (e) {
    var modal_data = e.params.data;
    print_email_po(modal_data.id)

});

$('#nav2_transaction_no').on('select2:select', function (e) {
    var modal_data = e.params.data;
    print_email_po(modal_data.id)
});

$('#nav3_purchase_order_no').on('select2:select', function (e) {
    var modal_data = e.params.data;
    cancel_po(modal_data.id)

});

$('#nav3_transaction_no').on('select2:select', function (e) {
    var modal_data = e.params.data;
    cancel_po(modal_data.id)
});

$('#nav4_purchase_order_no').on('select2:select', function (e) {
    var modal_data = e.params.data;
    view_po(modal_data.id)

});

$('#nav4_transaction_no').on('select2:select', function (e) {
    var modal_data = e.params.data;
    view_po(modal_data.id)
});

$("#quantity_modal" ).change(function() {

  var quantity = $('#quantity_modal').val();
  var tax = $('#tax_modal').val();
  var price = $('#unit_price_modal').val().replace(/,/g, '');
  var subtotal = quantity*price;
  var taxtotal = (subtotal*tax)/100;
  var total = subtotal+taxtotal;

  $('#subtotal_modal').val(subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
  $('#taxtotal_modal').val(taxtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
  $('#total_modal').val(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

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
  var taxtotal_table = $('#taxtotal_modal').val();
  var tax_modal = $('#tax_modal').val();
  var volume_modal = $('#volume_modal').val();
  var type_modal = $('#type_modal').val();
  var weight_modal = $('#weight_modal').val();
  var total_volume = Math.round((volume_modal*quantity) * 100.0) / 100.0;
  var total_weight = Math.round((weight_modal*quantity) * 100.0) / 100.0;
  var total = $('#total_modal').val();

   Pace.restart();
   
   Pace.track(function () {
             $.ajax({
                   url: "{{ route('purchase_order.validation_add_item_table') }}",
                   type: "post",
                   data:form_data,
                   success: function(data) {
                    $('#modal-default').modal('hide');

                    var id = $('#purchase_order_table_tbody').children('tr').length + 1;

                     var html = '';
                      html += '<tr>';
                      html += '<td><input type="text" class="form-control" id="id_'+id+'" name="row_item_id[]" value="'+item_id+'" hidden>'+item_id_modal+'</td>';
                          html += '<td><input type="text" class="form-control" value="'+item_name+'" hidden>'+item_name+'</td>';
                          html += '<td><input type="number" class="form-control" data-id="'+id+'" id="quantity" name="row_quantity[]" value="'+quantity+'"></td>';
                          html += '<td><input type="text" class="form-control" name="row_item_uom[]" value="'+uom_item+'" hidden>'+uom_item+'</td>';
                          html += '<td><input type="text" class="form-control" id="price_'+id+'" name="row_item_price[]" value="'+item_price.replace(/,/g, '')+'" hidden>'+item_price+'</td>';
                          html += '<td class="row_subtotal"><input type="text" id="subtotal_table_'+id+'" class="form-control" name="row_line_total[]" value="'+subtotal_table+'" hidden><span id="subtotal_table_td_'+id+'">'+subtotal_table+'</span></td>';
                          html += '<td><input type="text" class="form-control" id="tax_'+id+'" name="row_tax[]" value="'+tax_modal+'" hidden>'+tax_modal+' %</td>';
                          html += '<td class="row_tax"><input type="text"  id="taxtotal_table_'+id+'" class="form-control" name="row_tax_total[]" value="'+taxtotal_table+'" hidden><span id="taxtotal_table_td_'+id+'">'+taxtotal_table+'</span></td>';
                          html += '<td class="row_total"><span id="total_table_td_'+id+'">'+total+'</span><input type="text"  id="total_table_'+id+'" class="form-control" name="row_total[]" value="'+total+'" hidden></td>';
                          html += '<td class="row_volume" style="display:none;"><span id="total_volume_td_'+id+'">'+total_volume+'</span><<input type="text" class="form-control" id="total_volume_'+id+'" name="row_total_volume[]" value="'+total_volume+'" hidden></td>';
                          html += '<td class="row_weight" style="display:none;"><span id="total_weight_td_'+id+'">'+total_weight+'</span><input type="text" class="form-control" id="total_weight_'+id+'" name="row_total_weight[]" value="'+total_weight+'" hidden></td>';
                          html += '<td style="display:none;"><input type="text" class="form-control" id="volume_'+id+'" name="row_volume[]" value="'+volume_modal+'" hidden></td>';
                          html += '<td style="display:none;"><input type="text" class="form-control" id="weight_'+id+'" name="row_weight[]" value="'+weight_modal+'" hidden></td>';
                          html += '<td style="display:none;"><input type="text" class="form-control" name="row_type[]" value="'+type_modal+'" hidden></td>';
                          html += '<td><button class="btn btn-sm btn-default" id="remove_table_item"><i class="fas fa-times"></i></button></td>';
                      html += '</tr>';

                      $('#purchase_order_table').prepend(html);

                      get_total();
                      get_subtotal();
                      get_weight_total();
                      get_volume_total();
                      get_tax();
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
  var taxtotal_table = $('#taxtotal_modal').val();
  var tax_modal = $('#tax_modal').val();
  var volume_modal = $('#volume_modal').val();
  var type_modal = $('#type_modal').val();
  var weight_modal = $('#weight_modal').val();
  var total_volume = Math.round((volume_modal*quantity) * 100.0) / 100.0;
  var total_weight = Math.round((weight_modal*quantity) * 100.0) / 100.0;
  var total = $('#total_modal').val();


      Pace.restart();
   
       Pace.track(function () {
                 $.ajax({
                       url: "{{ route('purchase_order.validation_add_item_table') }}",
                       type: "post",
                       data:form_data,
                       success: function(data) {

                         var id = $('#purchase_order_table_tbody').children('tr').length + 1;

                         var html = '';
                          html += '<tr>';
                          html += '<td><input type="text" class="form-control" id="id_'+id+'" name="row_item_id[]" value="'+item_id+'" hidden>'+item_id_modal+'</td>';
                          html += '<td><input type="text" class="form-control" value="'+item_name+'" hidden>'+item_name+'</td>';
                          html += '<td><input type="number" class="form-control" data-id="'+id+'" id="quantity" name="row_quantity[]" value="'+quantity+'"></td>';
                          html += '<td><input type="text" class="form-control" name="row_item_uom[]" value="'+uom_item+'" hidden>'+uom_item+'</td>';
                          html += '<td><input type="text" class="form-control" id="price_'+id+'" name="row_item_price[]" value="'+item_price.replace(/,/g, '')+'" hidden>'+item_price+'</td>';
                          html += '<td class="row_subtotal"><input type="text" id="subtotal_table_'+id+'" class="form-control" name="row_line_total[]" value="'+subtotal_table+'" hidden><span id="subtotal_table_td_'+id+'">'+subtotal_table+'</span></td>';
                          html += '<td><input type="text" class="form-control" id="tax_'+id+'" name="row_tax[]" value="'+tax_modal+'" hidden>'+tax_modal+' %</td>';
                          html += '<td class="row_tax"><input type="text"  id="taxtotal_table_'+id+'" class="form-control" name="row_tax_total[]" value="'+taxtotal_table.replace(/,/g, '')+'" hidden><span id="taxtotal_table_td_'+id+'">'+taxtotal_table+'</span></td>';
                          html += '<td class="row_total"><span id="total_table_td_'+id+'">'+total+'</span><input type="text"  id="total_table_'+id+'" class="form-control" name="row_total[]" value="'+total.replace(/,/g, '')+'" hidden></td>';
                          html += '<td class="row_volume" style="display:none;"><span id="total_volume_td_'+id+'">'+total_volume+'</span><<input type="text" class="form-control" id="total_volume_'+id+'" name="row_total_volume[]" value="'+total_volume+'" hidden></td>';
                          html += '<td class="row_weight" style="display:none;"><span id="total_weight_td_'+id+'">'+total_weight+'</span><input type="text" class="form-control" id="total_weight_'+id+'" name="row_total_weight[]" value="'+total_weight+'" hidden></td>';
                          html += '<td style="display:none;"><input type="text" class="form-control" id="volume_'+id+'" name="row_volume[]" value="'+volume_modal+'" hidden></td>';
                          html += '<td style="display:none;"><input type="text" class="form-control" id="weight_'+id+'" name="row_weight[]" value="'+weight_modal+'" hidden></td>';
                          html += '<td style="display:none;"><input type="text" class="form-control" name="row_type[]" value="'+type_modal+'" hidden></td>';
                          html += '<td><button class="btn btn-sm btn-default" id="remove_table_item"><i class="fas fa-times"></i></button></td>';
                          html += '</tr>';

                          $('#purchase_order_table').prepend(html);

                          get_total();
                          get_subtotal();
                          get_weight_total();
                          get_volume_total();
                          get_tax();
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

$(document).on('change', '#quantity', function(){

  
  event.preventDefault();

  var quantity = $(this).val();
  var id = $(this).data().id;
  
  var item_price = $('#price_'+id).val().replace(/,/g, '');;
  var volume_modal = $('#volume_'+id).val();
  var weight_modal = $('#weight_'+id).val();
  var total_volume = Math.round((volume_modal*quantity) * 100.0) / 100.0;
  var total_weight = Math.round((weight_modal*quantity) * 100.0) / 100.0;
  var subtotal_table = $('#subtotal_table_'+id).val();
  var taxtotal_table = $('#taxtotal_table_'+id).val();
  var tax_modal = $('#tax_'+id).val();
  var total = $('#total_table_'+id).val();

 
  var new_subtotal = quantity*item_price;

  var new_taxtotal = (new_subtotal*tax_modal)/100;

  var new_total = new_subtotal+new_taxtotal;
  
  $('#subtotal_table_'+id).val(new_subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
  $('#subtotal_table_td_'+id).text(new_subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

  $('#taxtotal_table_td_'+id).text(new_taxtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
  $('#taxtotal_table_'+id).val(new_taxtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

  $('#total_table_'+id).val(new_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
  $('#total_table_td_'+id).text(new_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

  $('#total_volume_td_'+id).text(total_volume);
  $('#total_weight_td_'+id).text(total_weight);


   get_total();
   get_subtotal();
   get_weight_total();
   get_volume_total();
   get_tax();

});

$('#cancel').on('click', function (event) {

 
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes'
  }).then((result) => {
    if (result.value) {

      Pace.restart();
      Pace.track(function () {
               $.ajax({
                   url: "{{ route('purchase_order.cancel') }}",
                   type: "post",
                   data: {
                      'purchase_order_no': $('#nav3_purchase_order_no').val()
                   },
                   success: function(data) {

                      get_purchase_order_cancel_list();
                      get_purchase_order_print_list();

                      $('#cancel').attr("disabled", 'disabled');

                      $('#nav3_order_date').val('');
                      $('#nav3_deliver_to').val('');
                      $('#nav3_supplier_id').val('');
                      $('#nav3_supplier_company').val('');
                      $('#nav3_supplier_name').val('');
                      $('#nav3total').text(0);
                      $('#nav3comments').text(0);
                      $('#nav3subtotal').text(0);
                      $('#nav3tax').text(0);
                      $('#nav3weight_total').text(0);
                      $('#nav3volume_total').text(0);
                      $('#nav3purchase_order_table  > tbody tr').remove();

                      Swal.fire(
                        'Canceled!',
                        'Order has been canceled.',
                        'success'
                      )
                   },
                   error: function(error){
                        Toast.fire({
                        type: 'error',
                        title: 'Invalid Inputs.'
                      })
                   }
                  });
               });   

    }
  })    


});

$('#nav_po_create').on('click', function (event) {

  
  get_purchase_order_cancel_list();
  get_purchase_order_print_list();
  get_purchase_order_view_list();
  $('#nav2_purchase_order_no').select2().val(null).trigger("change");
  $('#nav2_transaction_no').select2().val(null).trigger("change");
  $('#nav3_purchase_order_no').select2().val(null).trigger("change");
  $('#nav3_transaction_no').select2().val(null).trigger("change");
  $('#nav4_purchase_order_no').select2().val(null).trigger("change");
  $('#nav4_transaction_no').select2().val(null).trigger("change");
  $('#nav2_order_date').val('');
  $('#nav2_deliver_to').val('');
  $('#nav2_supplier_id').val('');
  $('#nav2_supplier_company').val('');
  $('#nav2_supplier_name').val('');
  $('#nav2total').text(0);
  $('#nav2subtotal').text(0);
  $('#nav2tax').text(0);
  $('#nav2weight_total').text(0);
  $('#nav2volume_total').text(0);
  $('#nav2comments').text('');
  $('#nav2purchase_order_table  > tbody tr').remove();
  $('#nav3_order_date').val('');
  $('#nav3_deliver_to').val('');
  $('#nav3_supplier_id').val('');
  $('#nav3_supplier_company').val('');
  $('#nav3_supplier_name').val('');
  $('#nav3total').text(0);
  $('#nav3comments').text('');
  $('#nav3purchase_order_table  > tbody tr').remove();
  $('#nav4_order_date').val('');
  $('#nav4_deliver_to').val('');
  $('#nav4_supplier_id').val('');
  $('#nav4_supplier_company').val('');
  $('#nav4_supplier_name').val('');
  $('#nav4total').text(0);
  $('#nav4subtotal').text(0);
  $('#nav4tax').text(0);
  $('#nav4weight_total').text(0);
  $('#nav4volume_total').text(0);
  $('#nav4comments').text('');
  $('#nav4purchase_order_table  > tbody tr').remove();
  $("#email_po").attr("disabled", "disabled");
  $("#print_po").attr("disabled", "disabled");
  $("#cancel").attr("disabled", "disabled");
  
});

$('#nav_print_email_po').on('click', function (event) {

  get_purchase_order_cancel_list();
  get_purchase_order_print_list();
  get_purchase_order_view_list();
  $('#nav3_purchase_order_no').select2().val(null).trigger("change");
  $('#nav3_transaction_no').select2().val(null).trigger("change");
  $('#nav4_purchase_order_no').select2().val(null).trigger("change");
  $('#nav4_transaction_no').select2().val(null).trigger("change");
  $('#nav3_order_date').val('');
  $('#nav3_deliver_to').val('');
  $('#nav3_supplier_id').val('');
  $('#nav3_supplier_company').val('');
  $('#nav3_supplier_name').val('');
  $('#nav3total').text(0);
  $('#nav3subtotal').text(0);
  $('#nav3tax').text(0);
  $('#nav3weight_total').text(0);
  $('#nav3volume_total').text(0);
  $('#nav3comments').text('');
  $('#nav3purchase_order_table  > tbody tr').remove();
  $('#nav4_order_date').val('');
  $('#nav4_deliver_to').val('');
  $('#nav4_supplier_id').val('');
  $('#nav4_supplier_company').val('');
  $('#nav4_supplier_name').val('');
  $('#nav4total').text(0);
  $('#nav4subtotal').text(0);
  $('#nav4tax').text(0);
  $('#nav4weight_total').text(0);
  $('#nav4volume_total').text(0);
  $('#nav4comments').text('');
  $('#nav4purchase_order_table  > tbody tr').remove();
  $("#cancel").attr("disabled", "disabled");

});

$('#nav_po_cancel').on('click', function (event) {

  get_purchase_order_cancel_list();
  get_purchase_order_print_list();
  get_purchase_order_view_list();
  $('#nav2_purchase_order_no').select2().val(null).trigger("change");
  $('#nav2_transaction_no').select2().val(null).trigger("change");
  $('#nav4_purchase_order_no').select2().val(null).trigger("change");
  $('#nav4_transaction_no').select2().val(null).trigger("change");
  $('#nav4_order_date').val('');
  $('#nav4_deliver_to').val('');
  $('#nav4_supplier_id').val('');
  $('#nav4_supplier_company').val('');
  $('#nav4_supplier_name').val('');
  $('#nav4total').text(0);
  $('#nav4subtotal').text(0);
  $('#nav4tax').text(0);
  $('#nav4weight_total').text(0);
  $('#nav4volume_total').text(0);
  $('#nav4comments').text('');
  $('#nav4purchase_order_table  > tbody tr').remove();
  $('#nav2_order_date').val('');
  $('#nav2_deliver_to').val('');
  $('#nav2_supplier_id').val('');
  $('#nav2_supplier_company').val('');
  $('#nav2_supplier_name').val('');
  $('#nav2total').text(0);
  $('#nav2subtotal').text(0);
  $('#nav2tax').text(0);
  $('#nav2weight_total').text(0);
  $('#nav2volume_total').text(0);
  $('#nav2comments').text('');
  $('#nav2purchase_order_table  > tbody tr').remove();
  $("#email_po").attr("disabled", "disabled");
  $("#print_po").attr("disabled", "disabled");

});

$('#nav_po_view').on('click', function (event) {


  get_purchase_order_cancel_list();
  get_purchase_order_print_list();
  get_purchase_order_view_list();
  $('#nav2_purchase_order_no').select2().val(null).trigger("change");
  $('#nav2_transaction_no').select2().val(null).trigger("change");
  $('#nav3_purchase_order_no').select2().val(null).trigger("change");
  $('#nav3_transaction_no').select2().val(null).trigger("change");
  $('#nav2_order_date').val('');
  $('#nav2_deliver_to').val('');
  $('#nav2_supplier_id').val('');
  $('#nav2_supplier_company').val('');
  $('#nav2_supplier_name').val('');
  $('#nav2total').text(0);
  $('#nav2subtotal').text(0);
  $('#nav2tax').text(0);
  $('#nav2weight_total').text(0);
  $('#nav2volume_total').text(0);
  $('#nav2comments').text('');
  $('#nav2purchase_order_table  > tbody tr').remove();
  $('#nav3_order_date').val('');
  $('#nav3_deliver_to').val('');
  $('#nav3_supplier_id').val('');
  $('#nav3_supplier_company').val('');
  $('#nav3_supplier_name').val('');
  $('#nav3total').text(0);
  $('#nav3subtotal').text(0);
  $('#nav3tax').text(0);
  $('#nav3weight_total').text(0);
  $('#nav3volume_total').text(0);
  $('#nav3comments').text('');
  $('#nav3purchase_order_table  > tbody tr').remove();
  $("#email_po").attr("disabled", "disabled");
  $("#print_po").attr("disabled", "disabled");
  $("#cancel").attr("disabled", "disabled");

});

$(document).on('click', '#remove_table_item', function(){
     $(this).closest('tr').remove();
     get_total();
     get_subtotal();
     get_weight_total();
     get_volume_total();
     get_tax();
});

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
                        + "&total_volume=" + $('#volume_total').text()
                        + "&total_weight=" + $('#weight_total').text()
                        + "&total_tax=" + $('#tax').text().replace(/,/g, '')
                        + "&subtotal=" + $('#subtotal').text().replace(/,/g, '')
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
                          $('#purchase_order_table  > tbody tr  tr').remove();


                            get_purchase_order_id();
                            get_total();
                            get_subtotal();
                            get_weight_total();
                            get_volume_total();
                            get_tax();

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

$(document).on('click', '#table_print', function(){
  console.log($(this).data().id);
                          $( "#nav_po_list" ).removeClass( "active" );
                          $( "#nav_print_email_po" ).addClass( "active" );
                          $( "#po_list" ).removeClass( "active" );
                          $( "#print_email_po" ).addClass( "active" );
                          print_email_po($(this).data().id);
});

$(document).on('click', '#table_view', function(){
  console.log($(this).data().id);
                          $( "#nav_po_list" ).removeClass( "active" );
                          $( "#nav_po_view" ).addClass( "active" );
                          $( "#po_list" ).removeClass( "active" );
                          $( "#view_po" ).addClass( "active" );
                          view_po($(this).data().id);
});

$(document).on('click', '#table_cancel', function(){
  console.log($(this).data().id);
  $( "#nav_po_list" ).removeClass( "active" );
                          $( "#nav_po_cancel" ).addClass( "active" );
                          $( "#po_list" ).removeClass( "active" );
                          $( "#cancel_po" ).addClass( "active" );
                          cancel_po($(this).data().id);
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
          <div class="card-header p-3">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link active" id="nav_po_create" href="#po_create" data-toggle="tab"><i class="fas fa-plus"></i> Create Purchase Order</a></li>
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
                <div class="col-lg-5 col-md-12">
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
                    <div class="col-lg-5 col-md-12">
                      <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Order Date</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="order_date" name="order_date" placeholder="Order Date" readonly>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="inputEmail3" class="col-sm-4 control-label">Deliver To</label>
                          <div class="col-sm-8">
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
                                        <th>Price</th>
                                        <th>Line Total</th>
                                        <th>Tax Rate</th>
                                        <th>Tax Total</th>
                                        <th>Sub Total</th>
                                        <th></th>
                                      </tr>
                                    </thead>
                                    <tbody id="purchase_order_table_tbody">
                                      <!-- <tr>
                                        <th colspan="5" style="text-align:right">Total:</th>
                                        <th colspan="7" style="text-align:center">
                                          <span id="total"></span>
                                        </th>
                                      </tr> -->
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
                      <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="form-group">
                            <label>Comments</label>
                            <textarea class="form-control" id="comments" name="comments" rows="5" placeholder="Details..."></textarea>
                          </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(m&#179;)</span>
                                  <span class="info-box-number" id="volume_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(kg)</span>
                                  <span class="info-box-number" id="weight_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-yellow">
                                <div class="info-box-content">
                                  <span class="info-box-text">Tax Total</span>
                                  <span class="info-box-number" id="tax"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-red">
                                <div class="info-box-content">
                                  <span class="info-box-text">Sub Total</span>
                                  <span class="info-box-number" id="subtotal"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-green">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total</span>
                                  <span class="info-box-number" id="total"><span>
                                </span></span></div>
                              </div>
                            </div>
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
                              <th>Price</th>
                              <th>Line Total</th>
                              <th>Tax Rate</th>
                              <th>Tax Total</th>
                              <th>Sub Total</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                      <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="form-group">
                            <label>Comments</label>
                            <textarea class="form-control" id="nav2comments" name="nav2comments" rows="5" placeholder="Details..."></textarea>
                          </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(m&#179;)</span>
                                  <span class="info-box-number" id="nav2volume_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(kg)</span>
                                  <span class="info-box-number" id="nav2weight_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-yellow">
                                <div class="info-box-content">
                                  <span class="info-box-text">Tax Total</span>
                                  <span class="info-box-number" id="nav2tax"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-red">
                                <div class="info-box-content">
                                  <span class="info-box-text">Sub Total</span>
                                  <span class="info-box-number" id="nav2subtotal"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-green">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total</span>
                                  <span class="info-box-number" id="nav2total"><span>
                                </span></span></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
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
                              <th>Price</th>
                              <th>Line Total</th>
                              <th>Tax Rate</th>
                              <th>Tax Total</th>
                              <th>Sub Total</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                      <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="form-group">
                            <label>Comments</label>
                            <textarea class="form-control" id="nav3comments" name="nav3comments" rows="5" placeholder="Details..."></textarea>
                          </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(m&#179;)</span>
                                  <span class="info-box-number" id="nav3volume_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(kg)</span>
                                  <span class="info-box-number" id="nav3weight_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-yellow">
                                <div class="info-box-content">
                                  <span class="info-box-text">Tax Total</span>
                                  <span class="info-box-number" id="nav3tax"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-red">
                                <div class="info-box-content">
                                  <span class="info-box-text">Sub Total</span>
                                  <span class="info-box-number" id="nav3subtotal"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-green">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total</span>
                                  <span class="info-box-number" id="nav3total"><span>
                                </span></span></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
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
                              <th>Price</th>
                              <th>Line Total</th>
                              <th>Tax Rate</th>
                              <th>Tax Total</th>
                              <th>Sub Total</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                      <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="form-group">
                            <label>Comments</label>
                            <textarea class="form-control" id="nav4comments" name="nav4comments" rows="5" placeholder="Details..."></textarea>
                          </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6">
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(m&#179;)</span>
                                  <span class="info-box-number" id="nav4volume_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-blue">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total Volume(kg)</span>
                                  <span class="info-box-number" id="nav4weight_total"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-4">
                              <div class="info-box bg-yellow">
                                <div class="info-box-content">
                                  <span class="info-box-text">Tax Total</span>
                                  <span class="info-box-number" id="nav4tax"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-red">
                                <div class="info-box-content">
                                  <span class="info-box-text">Sub Total</span>
                                  <span class="info-box-number" id="nav4subtotal"></span>
                                </div>
                              </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                              <div class="info-box bg-green">
                                <div class="info-box-content">
                                  <span class="info-box-text">Total</span>
                                  <span class="info-box-number" id="nav4total"><span>
                                </span></span></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
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
                    <option value="Placed">Placed</option>
                    <option value="Canceled" >Canceled</option>
                    <option value="Received" >Received</option>
                    <option value="Receiving" >Receiving</option>
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
                    <div class="form-group row"  id="tax_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Tax Rate</label>
                      <div class="col-sm-9">
                         <input type="text" class="form-control" id="tax_modal" name="tax_modal" placeholder="Tax" readonly>
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
                    <div class="form-group row" id="taxtotal_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Tax Total</label>
                      <div class="col-sm-9">
                         <input type="text" class="form-control" id="taxtotal_modal" name="taxtotal_modal" placeholder="Tax Total" readonly>
                      </div>
                    </div>
                    <div class="form-group row" id="subtotal_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Sub Total</label>
                      <div class="col-sm-9">
                         <input type="text" class="form-control" id="subtotal_modal" name="subtotal_modal" placeholder="Sub Total" readonly>
                         <input type="text" class="form-control" id="weight_modal" name="weight_modal" hidden>
                         <input type="text" class="form-control" id="volume_modal" name="volume_modal" hidden>
                         <input type="text" class="form-control" id="type_modal" name="type_modal" hidden>
                      </div>
                    </div>
                    <div class="form-group row" id="total_modal_this">
                      <label for="inputEmail3" class="col-sm-3 control-label">Total</label>
                      <div class="col-sm-9">
                         <input type="text" class="form-control" id="total_modal" name="total_modal" placeholder="Total" readonly>
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