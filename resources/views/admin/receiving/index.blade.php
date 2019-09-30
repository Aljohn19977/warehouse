@extends('admin.partials.master')

@section('style')
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">

<link type="text/css" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/css/dataTables.checkboxes.css" rel="stylesheet" />

<style>
  .loader {
    position: absolute;
    -webkit-animation:spin 2s linear infinite;
    -moz-animation:spin 2s linear infinite;
    animation:spin 2s linear infinite;
    }
    @-moz-keyframes spin { 100% { -moz-transform: rotate(360deg); } }
    @-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
    @keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
</style>

@endsection

@section('script')
<script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables/dataTables.bootstrap4.js') }}"></script>
<script src="{{ asset('admin/plugins/inputmask/jquery.inputmask.bundle.js') }}"></script> 
<script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script>
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

get_transaction_list();
get_recieved_list();
received_item_datatable();


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

function clearError(){
  $( ".is-invalid" ).removeClass("is-invalid");
  $( ".help-block" ).remove();
}


function get_batch_id(){
    $.ajax({
        type: 'get',
        url: "{{ route('receiving.get_batch_id') }}",
        success: function(data) {
           $('#batch_id').val(data.batch_id);
        },
        error: function(error){
          console.log('error');
        }
     }); 
}

$('#expiration_date').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
});

// $('#expiration_date2').daterangepicker({
//       singleDatePicker: true,
//       showDropdowns: true,
//       autoUpdateInput: false,
//       locale: {
//         format: 'DD/MM/YYYY'
//       }
// });


$('#expiration_date2').daterangepicker({
    singleDatePicker: true,
        locale: {
            format: 'MM/DD/YYYY'
        },
        autoUpdateInput: false
    }).on("apply.daterangepicker", function (e, picker) {
        picker.element.val(picker.startDate.format(picker.locale.format));
    });


function received_item_datatable(start_date,end_date,filter_type,filter_receiving_id){
  $('#barcoding_items_table').DataTable({
              processing: true,
              serverSide: true,
              responsive: true,
              paging: true,
              lengthChange: true,
              searching: true,
              autoWidth: true,
              ajax: {
                      'url' : "{{ route('receiving.api_get_all_received_item_barcoding')}}",
                      'dataType' : 'json',
                      'type' : 'post',
                      'data' : {
                                  'start_date' : start_date,
                                  'end_date' : end_date,
                                  'filter_type': filter_type,
                                  'filter_receiving_id': filter_receiving_id
                               } 
              },
                columns : [
                            {"data" : "receiving_id"},
                            {"data" : "item_id"},
                            {"data" : "name"},
                            {"data" : "quantity"},
                            // {"data" : "price"},
                            {"data" : "item_uom"},
                            {"data" : "type"},
                            {"data" : "bar_code"},
                            {"data" : "updated_at"},
                            {"data" : "action"}
                          ],

  });
}

$('#filter').on('click', function (event) {

event.preventDefault();

var filter_type = $('#filter_type').val();
var start_date = startDate;
var end_date = endDate;
var filter_receiving_id = $('#filter_receiving_no').val();

if (start_date != null && end_date != null){
  $('#barcoding_items_table').DataTable().destroy();
  received_item_datatable(start_date,end_date,filter_type,filter_receiving_id);
  console.log(start_date+end_date);
}else if(filter_type != '' ){
  $('#barcoding_items_table').DataTable().destroy();
  received_item_datatable(start_date,end_date,filter_type,filter_receiving_id);
}else if(filter_receiving_id != null ){
  $('#barcoding_items_table').DataTable().destroy();
  received_item_datatable(start_date,end_date,filter_type,filter_receiving_id);
}
});

function get_transaction_list(){
    $.ajax({
        type: 'get',
        url: "/receiving/transaction/list",
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

function get_recieved_list(){
    $.ajax({
        type: 'get',
        url: "/receiving/received/list",
        success: function(data) {

            $('#transaction_id_print_email_ro option').remove();


            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.transaction_id, row.id, false, false);
                $('#transaction_id_print_email_ro').append(newOption).trigger('change');
            })

            $('#transaction_id_print_email_ro').select2().val(null).trigger("change");

            JSON.parse(data).data.forEach(row => {
                var newOption = new Option(row.receiving_id, row.id, false, false);
                $('#receiving_order_id_print_email_ro').append(newOption).trigger('change');
            })

            $('#receiving_order_id_print_email_ro').select2().val(null).trigger("change");
            
            
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

$('#transaction_id_print_email_ro').on('select2:select', function (e) {
    var modal_data = e.params.data;
    received_order(modal_data.id);
    $('#transaction_id_print_email_ro').select2().val(modal_data.id).trigger("change");
    $('#receiving_order_id_print_email_ro').select2().val(modal_data.id).trigger("change");
});

$('#receiving_order_id_print_email_ro').on('select2:select', function (e) {
    var modal_data = e.params.data;
    received_order(modal_data.id);
    $('#transaction_id_print_email_ro').select2().val(modal_data.id).trigger("change");
    $('#receiving_order_id_print_email_ro').select2().val(modal_data.id).trigger("change");
});


function transaction_item(id){

  $.ajax({
        url: "/receiving/get_transaction_info/"+id,
        type: "get",
        datatype: "JSON",
        success: function(data) {

          $('#ordered_item_list_table  > tbody tr').remove();

          $.each(data.purchase_order_items, function(key, value){                         
              console.log(value.item_id);
              var html = '';
                        if(value.quantity_received+value.quantity_missing+value.quantity_damage != value.quantity){
                          html += '<tr bgcolor="#cecece">';
                        }else{
                          html += '<tr>';
                        }
                        html += '<td>'+value.item_id+'</td>';
                        html += '<td>'+value.item_name+'</td>';
                        html += '<td><span class="badge bg-primary">'+value.quantity+'</span></td>';
                        html += '<td><span class="badge bg-success">'+value.quantity_received+'</span></td>';
                        html += '<td><span class="badge bg-warning">'+value.quantity_missing+'</span></td>';
                        html += '<td><span class="badge bg-danger">'+value.quantity_damage+'</span></td>';
                        html += '<td>'+value.item_uom+'</td>';
                        html += '<td><button id="receive" class="btn btn-success" data-id="'+value.id+'" style="margin-right:3px;"><i class="fas fa-check"></i><button id="missing" class="btn btn-warning" data-id="'+value.id+'" style="margin-right:3px; color:white;"><i class="fas fa-question"></i><button id="damage" class="btn btn-danger" data-id="'+value.id+'" style="margin-right:3px; color:white;"><i class="fas fa-exclamation-circle"></i></td>';
                        html += '</tr>';

                        $('#ordered_item_list_table').prepend(html);

          });

        },
        error: function(error){
          Toast.fire({
            type: 'error',
            title: 'NetWork Error.'
          })
        }
      });      
}

function received_order(id){

  $.ajax({
        url: "/receiving/get_receiving_order_info/"+id,
        type: "get",
        datatype: "JSON",
        success: function(data) {

          $('#received_order_item_list_table  > tbody tr').remove();
          $('#tax').text(data.tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
          $('#subtotal').text(data.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
          $('#total').text(data.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
          $('#supplier_id_print_email_ro').val(data.supplier_id);
          $('#supplier_name_print_email_ro').val(data.supplier_name);
          $('#supplier_company_print_email_ro').val(data.supplier_company);
          $('#ordered_date_print_email_ro').val(data.ordered_date);
          $('#received_date_print_email_ro').val(data.received_date);
          $('#total_damage_item').text(data.total_damage_items);
          $('#total_missing_item').text(data.total_missing_items);
          $('#total_accepted_item').text(data.total_accepted_items);

          $.each(data.received_order_items, function(key, value){                         
              console.log(value.item_id);
              var html = '';
                        html += '<tr>';
                        html += '<td>'+value.item_id+'</td>';
                        html += '<td>'+value.item_name+'</td>';
                        html += '<td><span class="badge bg-primary">'+value.quantity+'</span></td>';
                        html += '<td><span class="badge bg-success">'+value.quantity_received+'</span></td>';
                        html += '<td><span class="badge bg-warning">'+value.quantity_missing+'</span></td>';
                        html += '<td><span class="badge bg-danger">'+value.quantity_damage+'</span></td>';
                        html += '<td>'+value.item_uom+'</td>';
                        html += '<td>'+value.tax+' %</td>';
                        html += '<td>'+value.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                        html += '<td>'+value.subtotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'</td>';
                        html += '</tr>';

                        $('#received_order_item_list_table').prepend(html);

          });

        },
        error: function(error){
          Toast.fire({
            type: 'error',
            title: 'NetWork Error.'
          })
        }
      });      
}

function received_item(id){
  $.ajax({
        url: "/receiving/get_received_item/"+id,
        type: "get",
        datatype: "JSON",
        success: function(data) {
          $.each(data.receive_order_items, function(key, value){                         
              console.log(value.item_id);
              var html = '';
                        html += '<tr>';
                        html += '<td>'+value.item_id+'</td>';
                        html += '<td>'+value.item_name+'</td>';
                        html += '<td>'+value.quantity+'</td>';
                        html += '<td>'+value.item_uom+'</td>';
                        html += '<td>'+value.date_received+'</td>';
                        html += '<td><button id="undo_receive_item" class="btn btn-danger" data-id="'+value.id+'" style="margin-right:3px;"><i class="fas fa-undo"></i></td>';
                        html += '</tr>';

                        $('#received_item_list_table').prepend(html);

          });

        },
        error: function(error){
          Toast.fire({
            type: 'error',
            title: 'NetWork Error.'
          })
        }
      });      
}

function received_missing_item(id){
  $.ajax({
        url: "/receiving/get_received_missing_item/"+id,
        type: "get",
        datatype: "JSON",
        success: function(data) {
          $.each(data.receive_order_items, function(key, value){                         
              console.log(value.item_id);
              var html = '';
                        html += '<tr>';
                        html += '<td>'+value.item_id+'</td>';
                        html += '<td>'+value.item_name+'</td>';
                        html += '<td>'+value.quantity+'</td>';
                        html += '<td>'+value.item_uom+'</td>';
                        html += '<td>'+value.date_received+'</td>';
                        html += '<td><button id="undo_missing_item" class="btn btn-danger" data-id="'+value.id+'" style="margin-right:3px;"><i class="fas fa-undo"></i></td>';
                        html += '</tr>';

                        $('#missing_item_list_table').prepend(html);

          });

        },
        error: function(error){
          Toast.fire({
            type: 'error',
            title: 'NetWork Error.'
          })
        }
      });      
}

function received_damage_item(id){
  $.ajax({
        url: "/receiving/get_received_damage_item/"+id,
        type: "get",
        datatype: "JSON",
        success: function(data) {
          $.each(data.receive_order_items, function(key, value){                         
              console.log(value.item_id);
              var html = '';
                        html += '<tr>';
                        html += '<td>'+value.item_id+'</td>';
                        html += '<td>'+value.item_name+'</td>';
                        html += '<td>'+value.quantity+'</td>';
                        html += '<td>'+value.item_uom+'</td>';
                        html += '<td>'+value.date_received+'</td>';
                        html += '<td><button id="undo_damage_item" class="btn btn-danger" data-id="'+value.id+'" style="margin-right:3px;"><i class="fas fa-undo"></i></td>';
                        html += '</tr>';

                        $('#damage_item_list_table').prepend(html);

          });

        },
        error: function(error){
          Toast.fire({
            type: 'error',
            title: 'NetWork Error.'
          })
        }
      });      
}

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
            $('#ordered_item_list_table > tbody tr').remove();;
            $('#received_item_list_table > tbody tr').remove();
            $('#missing_item_list_table > tbody tr').remove();
            $('#damage_item_list_table > tbody tr').remove();

             transaction_item(id);
             received_item(id);
             received_missing_item(id);
             received_damage_item(id);

          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Network Error.'
            })
          }
        })      
       });   
}

$(document).on('click', '#receive', function(){

  event.preventDefault();

  var id = $(this).data().id;

    $.ajax({
            url:"/receiving/receive_item_info/"+id,
            type: "get",
            datatype: "JSON",
            success: function(data) {
              Swal.fire({
                  title: 'Enter Acceptable Quantity. <br>Theirs ( '+data.remaining_quantity+' ) remaining.',
                  input: 'number',
                  showCancelButton: true,
                  inputValidator: (value) => {
                      if ( value <= 0) {
                        return 'Please enter valid quantity!'
                      }else if(value > data.remaining_quantity){
                        return 'Quantity Exceeded their is only ('+data.remaining_quantity+') remaining!'
                      }else{

                        var order_id = data.purchase_order_id;
                          $.ajax({
                              url:"{{ route('receiving.receive_item') }}",
                              type: "post",
                              datatype: "JSON",
                              data: {
                                     "id": id,
                                     "quantity": value,
                                     "location": $('#location').val(),
                                     "receiver_name": $('#receiver_name').val(),
                                  },
                              success: function(data) {
                                $('#ordered_item_list_table > tbody tr').remove();;
                                $('#received_item_list_table > tbody tr').remove();
                                $('#missing_item_list_table > tbody tr').remove();
                                $('#damage_item_list_table > tbody tr').remove();

                                transaction_item(order_id);
                                received_item(order_id);
                                received_missing_item(order_id);
                                received_damage_item(order_id);
                              },
                              error: function(error){
                                Toast.fire({
                                  type: 'error',
                                  title: 'Network Error.'
                                })
                              }
                          });  

                      }
                    }
                })
            },
            error: function(error){
              Toast.fire({
                type: 'error',
                title: 'Network Error.'
              })
            }
          })      

});

$(document).on('click', '#missing', function(){

  event.preventDefault();

  var id = $(this).data().id;

    $.ajax({
            url:"/receiving/receive_item_info/"+id,
            type: "get",
            datatype: "JSON",
            success: function(data) {
              Swal.fire({
                  title: 'Enter Missing Quantity. <br>Theirs ( '+data.remaining_quantity+' ) remaining.',
                  input: 'number',
                  showCancelButton: true,
                  inputValidator: (value) => {
                      if ( value <= 0) {
                        return 'Please enter valid quantity!'
                      }else if(value > data.remaining_quantity){
                        return 'Quantity Exceeded their is only ('+data.remaining_quantity+') remaining!'
                      }else{

                        var order_id = data.purchase_order_id;
                          $.ajax({
                              url:"{{ route('receiving.receive_missing_item') }}",
                              type: "post",
                              datatype: "JSON",
                              data: {
                                    "id": id,
                                    "quantity": value
                                  },
                              success: function(data) {
                                $('#ordered_item_list_table > tbody tr').remove();;
                                  $('#received_item_list_table > tbody tr').remove();
                                  $('#missing_item_list_table > tbody tr').remove();
                                  $('#damage_item_list_table > tbody tr').remove();

                                  transaction_item(order_id);
                                  received_item(order_id);
                                  received_missing_item(order_id);
                                  received_damage_item(order_id);
                              },
                              error: function(error){
                                Toast.fire({
                                  type: 'error',
                                  title: 'Network Error.'
                                })
                              }
                          });  

                      }
                    }
                })
            },
            error: function(error){
              Toast.fire({
                type: 'error',
                title: 'Network Error.'
              })
            }
          })      

});

$(document).on('click', '#damage', function(){

  event.preventDefault();

  var id = $(this).data().id;

  $.ajax({
          url:"/receiving/receive_item_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
            Swal.fire({
                title: 'Enter Damage Quantity. <br>Theirs ( '+data.remaining_quantity+' ) remaining.',
                input: 'number',
                showCancelButton: true,
                inputValidator: (value) => {
                    if ( value <= 0) {
                      return 'Please enter valid quantity!'
                    }else if(value > data.remaining_quantity){
                      return 'Quantity Exceeded their is only ('+data.remaining_quantity+') remaining!'
                    }else{

                      var order_id = data.purchase_order_id;
                        $.ajax({
                            url:"{{ route('receiving.receive_damage_item') }}",
                            type: "post",
                            datatype: "JSON",
                            data: {
                                   "id": id,
                                   "quantity": value
                                },
                            success: function(data) {
                              $('#ordered_item_list_table > tbody tr').remove();;
                                $('#received_item_list_table > tbody tr').remove();
                                $('#missing_item_list_table > tbody tr').remove();
                                $('#damage_item_list_table > tbody tr').remove();

                                transaction_item(order_id);
                                received_item(order_id);
                                received_missing_item(order_id);
                                received_damage_item(order_id);
                            },
                            error: function(error){
                              Toast.fire({
                                type: 'error',
                                title: 'Network Error.'
                              })
                            }
                        });  

                    }
                  }
              })
          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Network Error.'
            })
          }
        })      

});

$(document).on('click', '#undo_receive_item', function(){

  event.preventDefault();

  var id = $(this).data().id;

  $.ajax({
          url:"/receiving/undo_receive_item_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
              console.log(data);
            Swal.fire({
                title: 'Enter Quantity to return. <br>Theirs ( '+data.quantity+' ) remaining.',
                input: 'number',
                showCancelButton: true,
                inputValidator: (value) => {
                    if ( value <= 0) {
                      return 'Please enter valid quantity!'
                    }else if(value > data.quantity){
                      return 'Quantity Exceeded their is only ('+data.quantity+') remaining!'
                    }else{

                      var order_id = data.purchase_order_id;

                        $.ajax({
                            url:"{{ route('receiving.undo_receive_item') }}",
                            type: "post",
                            datatype: "JSON",
                            data: {
                                   "id": id,
                                   "quantity": value
                                },
                            success: function(data) {
                                $('#ordered_item_list_table > tbody tr').remove();;
                                $('#received_item_list_table > tbody tr').remove();
                                transaction_item(order_id);
                                received_item(order_id);
                            },
                            error: function(error){
                              Toast.fire({
                                type: 'error',
                                title: 'Network Error.'
                              })
                            }
                        });  

                    }
                  }
              })
          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Network Error.'
            })
          }
        })      

});

$(document).on('click', '#undo_missing_item', function(){

  event.preventDefault();

  var id = $(this).data().id;

  $.ajax({
          url:"/receiving/undo_receive_missing_item_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
              console.log(data);
            Swal.fire({
                title: 'Enter Quantity to return. <br>Theirs ( '+data.quantity+' ) remaining.',
                input: 'number',
                showCancelButton: true,
                inputValidator: (value) => {
                    if ( value <= 0) {
                      return 'Please enter valid quantity!'
                    }else if(value > data.quantity){
                      return 'Quantity Exceeded their is only ('+data.quantity+') remaining!'
                    }else{

                      var order_id = data.purchase_order_id;
                      
                        $.ajax({
                            url:"{{ route('receiving.undo_receive_missing_item') }}",
                            type: "post",
                            datatype: "JSON",
                            data: {
                                   "id": id,
                                   "quantity": value
                                },
                            success: function(data) {
                                $('#ordered_item_list_table > tbody tr').remove();;
                                $('#missing_item_list_table > tbody tr').remove();
                                transaction_item(order_id);
                                received_missing_item(order_id);
                            },
                            error: function(error){
                              Toast.fire({
                                type: 'error',
                                title: 'Network Error.'
                              })
                            }
                        });  

                    }
                  }
              })
          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Network Error.'
            })
          }
        })    
});

$(document).on('click', '#undo_damage_item', function(){

  event.preventDefault();

  var id = $(this).data().id;

  $.ajax({
          url:"/receiving/undo_receive_damage_item_info/"+id,
          type: "get",
          datatype: "JSON",
          success: function(data) {
              console.log(data);
            Swal.fire({
                title: 'Enter Quantity to return. <br>Theirs ( '+data.quantity+' ) remaining.',
                input: 'number',
                showCancelButton: true,
                inputValidator: (value) => {
                    if ( value <= 0) {
                      return 'Please enter valid quantity!'
                    }else if(value > data.quantity){
                      return 'Quantity Exceeded their is only ('+data.quantity+') remaining!'
                    }else{

                      var order_id = data.purchase_order_id;
                      
                        $.ajax({
                            url:"{{ route('receiving.undo_receive_damage_item') }}",
                            type: "post",
                            datatype: "JSON",
                            data: {
                                  "id": id,
                                  "quantity": value
                                },
                            success: function(data) {
                                $('#ordered_item_list_table > tbody tr').remove();;
                                $('#damage_item_list_table > tbody tr').remove();

                                transaction_item(order_id);
                                received_damage_item(order_id);
                            },
                            error: function(error){
                              Toast.fire({
                                type: 'error',
                                title: 'Network Error.'
                              })
                            }
                        });  

                    }
                  }
              })
          },
          error: function(error){
            Toast.fire({
              type: 'error',
              title: 'Network Error.'
            })
          }
        })   

});

$(document).on('click', '#receive_order_next', function(){

  event.preventDefault();

  $.ajax({
    url:"{{ route('receiving.receiving_order') }}",
    type: "post",
    datatype: "JSON",
    data: {
            "id": $('#transaction_id').val(),
            "location": $('#location').val(),
            "receiver_name": $('#receiver_name').val(),
        },
    success: function(data) {
      $( "#nav_receive_order" ).removeClass( "active" );
      $( "#nav_print_email_ro" ).addClass( "active" );
      $( "#receive_order" ).removeClass( "active" );
      $( "#print_email_ro" ).addClass( "active" );
    },
    error: function(error){
      Toast.fire({
        type: 'error',
        title: 'Incomplete Receiving Order Process.'
      })
    }
  });  
  
});

$(document).on('click', '#print_email_ro_next', function(){

  event.preventDefault();

  Pace.restart();

    Pace.track(function () {

        swal.fire({
          title: 'Processing...',
          allowEscapeKey: false,
          allowOutsideClick: false,
          onOpen: () => {
            swal.showLoading();
          }
        })
      $.ajax({
        url:"{{ route('receiving.receive_order') }}",
        type: "post",
        datatype: "JSON",
        data: {
                "id": $('#receiving_order_id_print_email_ro').val(),
            },
        success: function(data) {
          $( "#nav_print_email_ro" ).removeClass( "active" );
          $( "#nav_received_items" ).addClass( "active" );
          $( "#print_email_ro" ).removeClass( "active" );
          $( "#received_items" ).addClass( "active" );

              swal.fire({ 
                title: 'Process Complete!',
                type: 'success',
                timer: 2000,
                showConfirmButton: false
              })    
        },
        error: function(error){
          Toast.fire({
            type: 'error',
            title: 'Network Error.'
          })
        }
      });  
    });
});


$('#pallet_builder').on('click', function (event) {

event.preventDefault();
$('#modal-default').modal('show');


});

function get_batch_quantity_remaining(id){
    $.ajax({
        type: 'get',
        dataType: 'JSON',
        url: "/receiving/get_batch_quantity_remaining/"+id,
        success: function(data) {
          $('#item_quantity').text(data.quantity_remaining)
        },
        error: function(error){
              Toast.fire({
                type: 'error',
                title: 'NetWork Error.'
              })
        }
    });
} 

$(document).on('click', '#table_print_batch_tracked', function(){

event.preventDefault();

var id = $(this).data().id;

get_batch_id();
get_batch_quantity_remaining(id);

$('#modal-batch').modal('show');


  $('#batch_list_table').DataTable({
                  processing: true,
                  serverSide: true,
                  responsive: true,
                  paging: true,
                  lengthChange: true,
                  searching: true,
                  autoWidth: true,
                  ajax: {
                          'url' : "{{ route('receiving.api_get_all_batch')}}",
                          'dataType' : 'json',
                          'type' : 'post'
                  },
                aoColumnDefs: [
                      { "bSortable": false, "aTargets": [ 0, 3 ] }, 
                      { "bSearchable": false, "aTargets": [ 3 ] }
                  ],
                    columns : [
                                {"data" : "batch_id"},
                                {"data" : "name"},
                                {"data" : "expiration_date"},
                                {"data" : "action"},
                              ]

  });


  $('#add_batch_id').on('click', function(e){

    
      e.preventDefault();

      $("#modal_loader2").attr("hidden",false);

      $.ajax({
        url: "{{ route('receiving.add_batch') }}",
        type: "post",
        datatype: "JSON",
        data:{
            "batch_id": $('#batch_id').val(),
            "batch_name" : $('#batch_name').val(),
            "expiration_date" : $('#expiration_date2').val(),
        },
        success: function(data) {
          clearError();
          get_batch_id();
          $('#batch_name').val('')
          $('#expiration_date2').val('')
          $("#modal_loader2").attr("hidden",true)
          $('#batch_list_table').DataTable().ajax.reload();
        },
        error: function(error){
          $("#modal_loader2").attr("hidden",true)
          clearError();
          $.each(error.responseJSON.errors, function(key, value){                         
                                  $("input[id="+key+"]").addClass("is-invalid");
                                  $("#"+key+"_this").append("<span class='help-block' style='color:red;'>"+value+"</span>");
                            });
        }
      });  


  });

 
});

$(document).on('click', '#table_print_serialize', function(){

  event.preventDefault();

  var id = $(this).data().id;

  $('#print_button').attr("disabled", 'disabled');
  $('#mark_button').attr("disabled", 'disabled');
  $('#unmark_button').attr("disabled", 'disabled');
  $('#modal-serialize').modal('show');


    $('#barcoding_items_table_modal').DataTable().destroy();
    var table = $('#barcoding_items_table_modal').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                paging: true,
                lengthChange: true,
                searching: true,
                autoWidth: true,
                columnDefs : [
                  {
                      'targets': 0,
                      'checkboxes': {
                        'selectRow': true
                      }
                  }
                ],
                'select': {
                  'style': 'multi'
                },
                ajax: {
                        'url' : "{{ route('receiving.api_get_selected_received_item_barcoding')}}",
                        'dataType' : 'json',
                        'type' : 'post',
                        'data' : {
                                    'id' : id,  
                                } 
                },
                  columns : [
                              {"data" : "check_box"},
                              {"data" : "serialize_item_id"},
                              {"data" : "bar_code"},
                            ]

    });

  $('#selected_serialize_item').on('submit', function(e){
      

      $('input[name="id\[\]"]', form).remove();

      var form = this;
      
      var rows_selected = table.column(0).checkboxes.selected();

      var selected_item = JSON.parse("[" + rows_selected.join(",") + "]");


            // Iterate over all selected checkboxes
      $.each(rows_selected, function(index, rowId){
         // Create a hidden element 
         $(form).append(
             $('<input>')
                .attr('type', 'hidden')
                .attr('name', 'id[]')
                .val(rowId)
         );
      });

  
    
   });  


   $('#mark_button').on('click', function(e){

      e.preventDefault();
      
     $("#modal_loader").attr("hidden",false);

      var rows_selected = table.column(0).checkboxes.selected();

      var selected_item = JSON.parse("[" + rows_selected.join(",") + "]");
      
      if(selected_item != ""){
          $.ajax({
            url: "{{ route('receiving.selected_serialize_item') }}",
            type: "post",
            datatype: "JSON",
            data:{

                "id": selected_item,
                "action" : 'mark_printed',
            },
            success: function(data) {
              $('#barcoding_items_table_modal').DataTable().ajax.reload();
              $('#barcoding_items_table').DataTable().ajax.reload();
              $("#modal_loader").attr("hidden",true);
            },
            error: function(error){
              Toast.fire({
                type: 'error',
                title: 'NetWork Error.'
              })
            }
          });  
      }


   });

      $('#unmark_button').on('click', function(e){

      e.preventDefault();

       $("#modal_loader").attr("hidden",false);

      var rows_selected = table.column(0).checkboxes.selected();

      var selected_item = JSON.parse("[" + rows_selected.join(",") + "]");

      if(selected_item != ""){
          $.ajax({
            url: "{{ route('receiving.selected_serialize_item') }}",
            type: "post",
            datatype: "JSON",
            data:{

                "id": selected_item,
                "action" : 'unmark_printed',
            },
            success: function(data) {
              $('#barcoding_items_table_modal').DataTable().ajax.reload();
              $('#barcoding_items_table').DataTable().ajax.reload();
              $("#modal_loader").attr("hidden",true);
            },
            error: function(error){
              Toast.fire({
                type: 'error',
                title: 'NetWork Error.'
              })
            }
          });  
      }


      }); 
});




$(document).on('change', '#expiration_switch', function(){


  if($('#expiration_date').is(':disabled')== false ){
    $('#expiration_date').attr("disabled", 'disabled');
  }else{
    $("#expiration_date").removeAttr("disabled");
  }

});

$(document).on('change', '.dt-checkboxes-cell', function(){

  if($('.dt-checkboxes').is(':checked')){
    $('#print_button').removeAttr("disabled");
    $('#mark_button').removeAttr("disabled");
    $('#unmark_button').removeAttr("disabled");
  }else{
    $('#print_button').attr("disabled", 'disabled');
    $('#mark_button').attr("disabled", 'disabled');
    $('#unmark_button').attr("disabled", 'disabled');
  }
  
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
            <h1>Order Receiving</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Order Receiving</li>
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
              <li class="nav-item"><a class="nav-link active" id="nav_receive_order" href="#receive_order" data-toggle="tab"><i class="fas fa-truck-loading"></i> Receive Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_print_email_ro" href="#print_email_ro" data-toggle="tab"><i class="fas fa-print" style="margin-right:5px;"></i>Print and Email Received Order</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_labeling_items" href="#labeling_items" data-toggle="tab"><i class="far fa-barcode-alt"></i> Barcoding Items</a></li>
              <li class="nav-item"><a class="nav-link" id="nav_received_items" href="#received_items" data-toggle="tab"><i class="fas fa-box-check"></i> Received Items</a></li>
            </ul>
          </div><!-- /.card-header -->
          <div class="card-body">
          <div class="tab-content">
          <div class="active tab-pane" id="receive_order">
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
                  <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Receiver Name</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="receiver_name" name="receiver_name" placeholder="Receiver Name" value="Aljohn Mirandilla" readonly>
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
                          <label for="inputEmail3" class="col-sm-4 control-label">Receiving Dock</label>
                          <div class="col-sm-8">
                          <select class="select2" id="location" name="location" data-placeholder="Receiving Dock" style="width: 100%;">
                            <option></option>
                            <option value="DOCK 1">DOCK 1</option>
                            <option value="DOCK 2" >DOCK 2</option>
                          </select>
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
                              <li class="nav-item"><a class="nav-link" href="#received_item_list" data-toggle="tab">Acceptable Item</a></li>
                              <li class="nav-item"><a class="nav-link" href="#received_missing_item_list" data-toggle="tab">Missing Item</a></li>
                              <li class="nav-item"><a class="nav-link" href="#received_damage_item_list" data-toggle="tab">Damage/Defective Item</a></li>
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
                                      <th>Ordered Qty</th>
                                      <th>Acceptable Qty</th>
                                      <th>Missing Qty</th>
                                      <th>Damage/Defective Qty</th>
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
                                      <th>Date Added</th>
                                      <th>Action</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                  </tbody>
                                </table>
                               </div>
                              <!-- /.tab-pane -->
                              <div class="tab-pane" id="received_missing_item_list">
                                <table class="table" id="missing_item_list_table">
                                  <thead>
                                    <tr>
                                      <th>Item ID</th>
                                      <th>Name</th>
                                      <th>Quantity</th>
                                      <th>UOM(Item)</th>
                                      <th>Date Added</th>
                                      <th>Action</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                  </tbody>
                                </table>
                               </div>      
                              <!-- /.tab-pane -->
                              <div class="tab-pane" id="received_damage_item_list">
                                <table class="table" id="damage_item_list_table">
                                  <thead>
                                    <tr>
                                      <th>Item ID</th>
                                      <th>Name</th>
                                      <th>Quantity</th>
                                      <th>UOM(Item)</th>
                                      <th>Date Added</th>
                                      <th>Action</th>
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
                        </div>
                        <!-- /.nav-tabs-custom -->
                      </div>
                      </div>
            </form>
              <div class="col-lg-12">
              <button id="back" class="btn btn-primary">Back</button>
                <button id="receive_order_next" class="btn btn-primary">Next Process<i class="fas fa-arrow-right" style="margin-left:4px;"></i></button>
              </div>
          </div>
           <!-- /.tab-pane -->
           <div class="tab-pane" id="print_email_ro">
            <form role="form" method="post" id="confirm_order">
                <div class="row" style="margin-bottom:25px">
                  <div class="col-lg-5 col-md-12">
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Transaction No.</label>
                      <div class="col-sm-8">
                        <select class="select2" id="transaction_id_print_email_ro" name="transaction_id_print_email_ro" data-placeholder="Select a Transaction No." style="width: 100%;"></select>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Receiving Order No.</label>
                      <div class="col-sm-8">
                        <select class="select2" id="receiving_order_id_print_email_ro" name="receiving_order_id__print_email_ro" data-placeholder="Select a Receiving Order No." style="width: 100%;"></select>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Ordered Date</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="ordered_date_print_email_ro" name="ordered_date_print_email_ro" value="" placeholder="Ordered Date" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Received Date</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="received_date_print_email_ro" name="received_date_print_email_ro" value="" placeholder="Received Date" readonly>
                        </div>
                    </div>
                      </div>
                      <div class="col-lg-2 col-md-12"></div>
                      <div class="col-lg-5 col-md-12">
                      <div class="form-group row">
                      <label for="inputEmail3" class="col-sm-4 control-label">Supplier ID</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control" id="supplier_id_print_email_ro" placeholder="Supplier ID" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Supplier Name</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="supplier_name_print_email_ro" placeholder="Supplier Name" readonly>
                          </div>
                        </div>
                        <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-4 control-label">Supplier Company</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="supplier_company_print_email_ro" placeholder="Supplier Company" readonly>
                          </div>
                        </div>
                        </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                              <div class="card">
                                  <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                      <li class="nav-item"><a class="nav-link active" href="#ordered_item_list" data-toggle="tab">Received Item</a></li>
                                    </ul>
                                  </div><!-- /.card-header -->
                                  <div class="card-body">
                                    <div class="tab-content">
                                      <div class="active tab-pane" id="ordered_item_list">
                                        <table class="table" id="received_order_item_list_table">
                                          <thead>
                                            <tr>
                                              <th>Item ID</th>
                                              <th>Name</th>
                                              <th>Ordered Qty</th>
                                              <th>Accepted Qty</th>
                                              <th>Missing Qty</th>
                                              <th>Damage/Defective Qty</th>
                                              <th>UOM(Item)</th>
                                              <th>Tax Rate %</th>
                                              <th>Price</th>
                                              <th>Subtotal</th>
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
                              </div>
                            <!-- /.nav-tabs-custom -->
                          </div>
                          
                        </div>
                        <div class="row">
                                    <div class="col-sm-12 col-md-12 col-lg-6">
                                      <div class="form-group">
                                        <label>Comments</label>
                                        <textarea class="form-control" id="comments" name="comments" rows="9" placeholder="Details..."></textarea>
                                      </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-6">
                                      <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-4">
                                          <div class="info-box bg-green">
                                            <div class="info-box-content">
                                              <span class="info-box-text">Total Accepted Item</span>
                                              <span class="info-box-number" id="total_accepted_item">0</span>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-sm-12 col-md-12 col-lg-4">
                                          <div class="info-box bg-yellow">
                                            <div class="info-box-content">
                                              <span class="info-box-text">Total Missing Item</span>
                                              <span class="info-box-number" id="total_missing_item">0</span>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-sm-12 col-md-12 col-lg-4">
                                          <div class="info-box bg-red">
                                            <div class="info-box-content">
                                              <span class="info-box-text">Total Damage Item</span>
                                              <span class="info-box-number" id="total_damage_item">0</span>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="row">
                                       <div class="col-sm-12 col-md-12 col-lg-6">
                                          <div class="info-box bg-blue">
                                            <div class="info-box-content">
                                              <span class="info-box-text">Tax Total</span>
                                              <span class="info-box-number" id="tax">0</span>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-sm-12 col-md-12 col-lg-6">
                                          <div class="info-box bg-blue">
                                            <div class="info-box-content">
                                              <span class="info-box-text">Sub Total</span>
                                              <span class="info-box-number" id="subtotal">0</span>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                          <div class="info-box bg-secondary">
                                            <div class="info-box-content">
                                              <span class="info-box-text">Total</span>
                                              <span class="info-box-number" id="total">0<span>
                                            </span></span></div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
              </form>
              <div class="col-lg-12">
                <button id="print_ro" class="btn btn-primary">Print RO</button>
                <button id="email_ro" class="btn btn-primary">Send RO via Email</i></button>
                <button id="print_email_ro_next" class="btn btn-primary">Next Process<i class="fas fa-arrow-right" style="margin-left:4px;"></i></button>
              </div>
					</div>
          <!-- /.tab-pane -->
          <div class="tab-pane" id="labeling_items">
          <div class="row">
              <div class="col-lg-3">
                  <!-- Date range -->
                  <div class="form-group">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">
                          <i class="far fa-calendar-alt"></i>
                        </span>
                      </div>
                      <input type="text" class="form-control float-right" id="filter_ro" placeholder="Click to select date range.">
                    </div>
                    <!-- /.input group -->
                  </div>
                  <!-- /.form group -->
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <select class="select2" id="filter_type" name="filter_type" data-placeholder="Filter Type" style="width: 100%;">
                      <option></option>
                      <option value="Serialize">Serialize</option>
                      <option value="Batch Tracked" >Batch Tracked</option>
                      </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <select class="select2" id="filter_receiving_no" name="filter_type" data-placeholder="Filter Receiving No." style="width: 100%;">
                      </select>
                  </div>
                </div>
                <div class="col-lg-1">
                <button id="filter" class="btn btn-primary btn-block float-left"><i class="fas fa-filter"></i> Filter</button>
                </div>
                <div class="col-lg-1">
                <button id="reset" class="btn btn-primary btn-block float-left"><i class="fas fa-undo"></i> Reset</button>
                </div>
                <div class="col-lg-12">
                  <table class="table table-hover" id="barcoding_items_table" style="width: 100%;">
                    <thead>
                      <tr>
                        <th>RO No.</th>
                        <th>Item ID</th>
                        <th>Name</th>
                        <th>Quantity</th>
                          <!-- <th>Price</th> --> 
                        <th>UOM(Item)</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date Received</th>
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
           <!-- /.tab-pane -->
          <div class="tab-pane" id="received_items">

          </div>
            <!-- /.tab-content -->
          </div><!-- /.card-body -->
          <div class="modal fade" id="modal-serialize">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="overlay" id="modal_loader" hidden>
                    <i class="fas fa-3x fa-sync-alt loader"></i>
                  </div>
                  <div class="modal-header">
                    <h4 class="modal-title">Print Barcode</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                  <form role="form" class="form-horizontal" method="POST" target="POPUPW" onsubmit="POPUPW = window.open('about:blank','POPUPW','width=600,height=500');" action="{{ route('receiving.selected_serialize_item') }}" id="selected_serialize_item">
                  {{ csrf_field() }}
                  <button type="submit" class="btn btn-primary btn-block" id="print_button" style="margin-bottom:20px;" disabled><i class="fas fa-print"></i>  Print</button>
                  <div class="row">
                    <div class="col-6">
                      <button class="btn btn-success btn-block" id="mark_button" style="margin-bottom:20px;" disabled><i class="far fa-check-square"></i>  Mark as Printed</button>
                    </div>
                    <div class="col-6">
                      <button class="btn btn-danger btn-block" id="unmark_button" style="margin-bottom:20px;" disabled><i class="far fa-window-close"></i>  Mark as Unprinted</button>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-6">
                    <div class="form-group" style="margin-top:33px;">
                    <div class="custom-control custom-switch">
                      <input type="checkbox" class="custom-control-input" id="expiration_switch">
                      <label class="custom-control-label" for="expiration_switch">Has Expiration Date</label>
                    </div>
                  </div>
                    </div>
                    <div class="col-6">
                       <!-- Date range -->
                      <div class="form-group">
                        <label>Select Expiration Date: </label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text">
                              <i class="far fa-calendar-alt"></i>
                            </span>
                          </div>
                          <input type="text" class="form-control float-right" id="expiration_date" name="expiration_date" placeholder="Click to select Expiration Date." disabled>
                        </div>
                        <!-- /.input group -->
                      </div>
                      <!-- /.form group -->
                    </div>
                  </div>
                  <div class="row">
                  <div class="col-lg-12">
                  <table class="table table-hover" id="barcoding_items_table_modal" style="width: 100%;">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Serial ID</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
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
          <div class="modal fade" id="modal-batch">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="overlay" id="modal_loader2" hidden>
                    <i class="fas fa-3x fa-sync-alt loader"></i>
                  </div>
                  <div class="modal-header">
                    <h4 class="modal-title">Print Barcode</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                  <div class="row">
                  <div class="col-9">
                    <button type="submit" class="btn btn-primary btn-block" id="print_button" style="margin-bottom:20px;" disabled><i class="fas fa-print"></i>  Print</button>
                    <div class="row">
                      <div class="col-6">
                        <button class="btn btn-success btn-block" id="mark_button" style="margin-bottom:20px;" disabled><i class="far fa-check-square"></i>  Mark as Printed</button>
                      </div>
                      <div class="col-6">
                        <button class="btn btn-danger btn-block" id="unmark_button" style="margin-bottom:20px;" disabled><i class="far fa-window-close"></i>  Mark as Unprinted</button>
                      </div>
                    </div>
                  </div>
                  <div class="col-3">
                      <div class="form-group" id="email_this">
                      <label for="email">Quantity Remaining</label>
                        <h1 id="item_quantity">0</h1>
                      </div>
                  </div>
                  </div>
                  <div>
                     
                  </div>
                  <div class="row">
                      <div class="col-md-12">
                        <div class="card">
                          <div class="card-header p-2">
                            <ul class="nav nav-pills">
                              <li class="nav-item"><a class="nav-link active" href="#1" data-toggle="tab">Batch List</a></li>
                              <li class="nav-item"><a class="nav-link" href="#2" data-toggle="tab">Acceptable Item</a></li>
                            </ul>
                          </div><!-- /.card-header -->
                          <div class="card-body">
                            <div class="tab-content">
                              <div class="active tab-pane" id="1">
                                <div class="row">
                                  <div class="col-3">
                                    <div class="form-group" id="batch_id_this">
                                        <input type="text" class="form-control" id="batch_id" name="batch_id" placeholder="Batch ID" readOnly>
                                    </div>
                                  </div>
                                  <div class="col-3">
                                    <div class="form-group" id="batch_name_this">
                                        <input type="text" class="form-control" id="batch_name" name="batch_name" placeholder="Name" required>
                                    </div>
                                  </div>
                                  <div class="col-2">
                                    <div class="form-group" id="batch_qty_this">
                                        <input type="text" class="form-control" id="batch_qty" name="batch_qty" placeholder="Qty" required>
                                    </div>
                                  </div>
                                  <div class="col-3">
                                    <!-- Date range -->
                                    <div class="form-group">
                                      <div class="input-group">
                                        <div class="input-group-prepend">
                                          <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                          </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="expiration_date2" name="expiration_date2" placeholder="Expiration Date.">
                                      </div>
                                      <!-- /.input group -->
                                    </div>
                                    <!-- /.form group -->
                                  </div>
                                  <div class="col-1">
                                    <button class="btn btn-primary" id="add_batch_id"><i class="fas fa-plus"></i></button>
                                  </div>
                                </div>
                                <table class="table" id="batch_list_table" style="width:100%;">
                                  <thead>
                                    <tr>
                                      <th>Batch ID</th>
                                      <th>Name</th>
                                      <th>Expiration Date</th>
                                      <th>Action</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                  </tbody>
                                </table>
                              </div>
                              <!-- /.tab-pane -->
                              <div class="tab-pane" id="2">
                                <table class="table" id="2">
                                  <thead>
                                    <tr>
                                      <th>Item ID</th>
                                      <th>Name</th>
                                      <th>Quantity</th>
                                      <th>UOM(Item)</th>
                                      <th>Date Added</th>
                                      <th>Action</th>
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
                        </div>
                        <!-- /.nav-tabs-custom -->
                      </div>
                      </div>
                  <div class="modal-footer">
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