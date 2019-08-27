@extends('admin.partials.master')

@section('style')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">

<style>
.select2-dropdown{
  display : none;
}
</style>
@endsection

@section('script')
<!-- Select2 -->
<script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
$(document).ready(function(){
 
  //Initialize Select2 Elements
  $('.select2').select2();
      

  get_uom_weight_list();
  get_uom_item_list();
  get_category_list();

  $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
  });

  
  function get_category_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('category.api_categoy_list') }}",
        success: function(data) {
          
        $('#item_category').empty();

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, true, true);
             $('#item_category').append(newOption).trigger('change');
        })

        },
        error: function(error){
          console.log('error');
        }
     }); 
  }


  function get_uom_weight_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('uom.api_weight_uom_list') }}",
        success: function(data) {
          
        $('#uom_weight').empty();

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name+' - '+row.acronym, row.id, true, true);
             $('#uom_weight').append(newOption).trigger('change');
        })

        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

  function get_uom_item_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('uom.api_item_uom_list') }}",
        success: function(data) {

         $('#uom_item').empty();

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name+' - '+row.acronym, row.id, true, true);
             $('#uom_item').append(newOption).trigger('change');
        })

        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

  $('#add_item_category').on('click',function(event){
    
	
    Swal.fire({
         title: 'Add Item Category',
         html:
          '<input id="category_name" class="swal2-input" placeholder="Input Name">',
        focusConfirm: false,
        preConfirm: () => {
          // alert($('#swal-input1').val() + $('#swal-input2').val());
          // // $("#uom_weight").append('<option value="asdasd">asdasd</option>');
          var category_name = $('#category_name').val();

          // uom.store_weight_uom

          // var newOption = new Option(text+'-'+acronym, acronym, true, true);

          // $('#uom_weight').append(newOption).trigger('change');

          // $("#uom_weight option[value="+acronym+"]").remove();
          
      
                   $.ajax({
                        url: "{{ route('category.store_category') }}",
                        type: "post",
                        data:  {
                          'category_name' : category_name,
                        },
                        dataType: 'JSON',
                        success: function(data) {
                            Toast.fire({
                              type: 'success',
                              title: name+' Successfully Added.'
                            })
                            get_category_list();

                        },
                        error: function(error){
                          Toast.fire({
                            type: 'error',
                            title: 'Invalid Inputs.'
                          })
                        }
                  });         
        }
    })



 }); 

  $('#add_uom_weight').on('click',function(event){
    
	
    Swal.fire({
         title: 'Add Weight UOM',
         html:
          '<input id="weight_uom_name" class="swal2-input" placeholder="Input Name">' +
          '<input id="weight_uom_acronym" class="swal2-input" placeholder="Input Acronym Name">',
        focusConfirm: false,
        preConfirm: () => {
          // alert($('#swal-input1').val() + $('#swal-input2').val());
          // // $("#uom_weight").append('<option value="asdasd">asdasd</option>');
          var name = $('#weight_uom_name').val();
          var acronym = $('#weight_uom_acronym').val();

          // uom.store_weight_uom

          // var newOption = new Option(text+'-'+acronym, acronym, true, true);

          // $('#uom_weight').append(newOption).trigger('change');

          // $("#uom_weight option[value="+acronym+"]").remove();
          
      
                   $.ajax({
                        url: "{{ route('uom.store_weight_uom') }}",
                        type: "post",
                        data:  {
                          'weight_uom_name' : name,
                          'weight_uom_acronym' : acronym,
                        },
                        dataType: 'JSON',
                        success: function(data) {
                            Toast.fire({
                              type: 'success',
                              title: name+' Successfully Added.'
                            })
                            get_uom_weight_list();

                        },
                        error: function(error){
                          Toast.fire({
                            type: 'error',
                            title: 'Invalid Inputs.'
                          })
                        }
                  });         
        }
    })



 }); 

   $('#add_uom_item').on('click',function(event){
    
	
    Swal.fire({
         title: 'Add Item UOM',
         html:
          '<input id="item_uom_name" class="swal2-input" placeholder="Input Name">' +
          '<input id="item_uom_acronym" class="swal2-input" placeholder="Input Acronym Name">',
        focusConfirm: false,
        preConfirm: () => {
          // alert($('#swal-input1').val() + $('#swal-input2').val());
          // // $("#uom_weight").append('<option value="asdasd">asdasd</option>');
          var name = $('#item_uom_name').val();
          var acronym = $('#item_uom_acronym').val();

          // uom.store_weight_uom

          // var newOption = new Option(text+'-'+acronym, acronym, true, true);

          // $('#uom_weight').append(newOption).trigger('change');

          // $("#uom_weight option[value="+acronym+"]").remove();
          
      
                   $.ajax({
                        url: "{{ route('uom.store_item_uom') }}",
                        type: "post",
                        data:  {
                          'item_uom_name' : name,
                          'item_uom_acronym' : acronym,
                        },
                        dataType: 'JSON',
                        success: function(data) {
                            Toast.fire({
                              type: 'success',
                              title: name+' Successfully Added.'
                            })
                            get_uom_item_list();

                        },
                        error: function(error){
                          Toast.fire({
                            type: 'error',
                            title: 'Invalid Inputs.'
                          })
                        }
                  });         
        }
    })



 }); 

  $('#uom_weight').on('select2:unselect', function (e) {
      var data = e.params.data;
      var id = data.id;
                       $.ajax({
                        url: "{{ route('uom.destroy_weight_uom') }}",
                        type: "post",
                        data:  {
                          'id' : id,
                        },
                        dataType: 'JSON',
                        success: function(data) {
                          $('#uom_weight').select2('close');
                            Toast.fire({
                              type: 'success',
                              title:' Deleted.'
                            })
                            $("#uom_weight option[value="+id+"]").remove();
                            get_uom_weight_list();

                        },
                        error: function(error){
                          Toast.fire({
                            type: 'error',
                            title: 'Invalid Inputs.'
                          })
                        }
                  });   
  });


    $('#uom_item').on('select2:unselect', function (e) {
      var data = e.params.data;
      var id = data.id;
                       $.ajax({
                        url: "{{ route('uom.destroy_item_uom') }}",
                        type: "post",
                        data:  {
                          'id' : id,
                        },
                        dataType: 'JSON',
                        success: function(data) {
                          $('#uom_item').select2('close');
                            Toast.fire({
                              type: 'success',
                              title:' Deleted.'
                            })
                            $("#uom_item option[value="+id+"]").remove();
                            get_uom_item_list();

                        },
                        error: function(error){
                          Toast.fire({
                            type: 'error',
                            title: 'Invalid Inputs.'
                          })
                        }
                  });   
  });

      $('#item_category').on('select2:unselect', function (e) {
      var data = e.params.data;
      var id = data.id;
                       $.ajax({
                        url: "{{ route('category.destroy_category') }}",
                        type: "post",
                        data:  {
                          'id' : id,
                        },
                        dataType: 'JSON',
                        success: function(data) {
                          $('#uom_item').select2('close');
                            Toast.fire({
                              type: 'success',
                              title:' Deleted.'
                            })
                            $("#item_category option[value="+id+"]").remove();
                            get_uom_item_list();

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
            <h1>Item Settings</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Item Settings</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="card card-default">
          <div class="card-header">
            <h3 class="card-title">Item Setting</h3>
          </div>
          <!-- /.card-header -->
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group" id="company_this">
                  <label>UOM Weight</label>
                  <button id="add_uom_weight" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></button>
                  <select class="select2" id="uom_weight" multiple="multiple" data-placeholder="Select a State" style="width: 100%;">
                  </select>
                  </div>
                  <div class="form-group" id="company_this">
                  <label>Item Category</label>
                  <button id="add_item_category" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></button>
                  <select class="select2" id="item_category" multiple="multiple" data-placeholder="Select a State" style="width: 100%;">
                  </select>
                  </div>
                </div>
                <div class="col-md-6">
                <div class="form-group" id="company_this">
                  <label>UOM Item</label>
                  <button id="add_uom_item" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></button>
                  <select class="select2" id="uom_item" multiple="multiple" data-placeholder="Select a State" style="width: 100%;">
                  </select>
                  </div>
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{ route('supplier.index') }}" class="btn btn-primary">Back</a>
            </div>
          </div>
        <!-- /.card -->
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
@endsection