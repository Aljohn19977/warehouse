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


get_supplier_list();
get_category_list();
get_weight_uom_list();
get_item_uom_list();




const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

function api_supplier_info(){

  $.ajax({
        type: 'get',
        url: "{{ route('supplier.api_view',['id' => $items->id ]) }}",
        success: function(data) {
          $("#box_photo").attr("src","/"+data.photo);
           $.each(data, function(key, value){                         
              $('#box_'+key+'').text(value);
            });
        },
        error: function(error){
          console.log('error');
        }
     }); 
}

  function get_supplier_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('item.api_supplier_list') }}",
        success: function(data) {

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, false, false);
            $('#supplier').append(newOption).trigger('change');
        })
        api_selected_supplier();
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

    function get_category_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('category.api_categoy_list') }}",
        success: function(data) {

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, false, false);
            $('#category_id').append(newOption).trigger('change');
        })
        api_selected_category();
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

  function get_weight_uom_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('uom.api_weight_uom_list') }}",
        success: function(data) {

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, false, false);
            $('#weight_uom').append(newOption).trigger('change');
        })
        api_selected_weight_uom();
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

  function get_item_uom_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('uom.api_item_uom_list') }}",
        success: function(data) {
        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, false, false);
            $('#item_uom').append(newOption).trigger('change');
        })
        api_selected_item_uom();
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

function api_selected_category(){

$.ajax({
    type: 'get',
    dataType: 'JSON',
    url: "{{ route('item.api_selected_category',['id' => $items->id ]) }}",
    success: function(data) {
      
        var selected = data.data;
        $('#category_id').val(selected).trigger("change");
    },
    error: function(error){
      console.log('error');
    }
 }); 

}

function api_selected_supplier(){

    $.ajax({
        type: 'get',
        dataType: 'JSON',
        url: "{{ route('item.api_selected_supplier',['id' => $items->id ]) }}",
        success: function(data) {
          
            var selected = data.data;
            $('#supplier').val(selected).trigger("change");
        },
        error: function(error){
          console.log('error');
        }
     }); 

}

function api_selected_weight_uom(){

$.ajax({
    type: 'get',
    dataType: 'JSON',
    url: "{{ route('item.api_selected_weight_uom',['id' => $items->id ]) }}",
    success: function(data) {
      
        var selected = data.data;
        $('#weight_uom').val(selected).trigger("change");
    },
    error: function(error){
      console.log('error');
    }
 }); 

}

function api_selected_item_uom(){

$.ajax({
    type: 'get',
    dataType: 'JSON',
    url: "{{ route('item.api_selected_item_uom',['id' => $items->id ]) }}",
    success: function(data) {
      
        var selected = data.data;
        $('#item_uom').val(selected).trigger("change");
    },
    error: function(error){
      console.log('error');
    }
 }); 

}


function clearError(){
  $( ".is-invalid" ).removeClass("is-invalid");
  $( ".help-block" ).remove();
}

$('#reset').click(function(event){
  event.preventDefault();
  Pace.restart();
  Pace.track(function () {
  $.ajax({
        type: 'get',
        url: "{{ route('item.api_view',['id' => $items->id ]) }}",
        success: function(data) {
           $.each(data, function(key, value){                         
              $('#'+key+'').val(value);
              $('#'+key+'').text(value);
           });
           api_selected_weight_uom();
           api_selected_item_uom();
           api_selected_supplier();
           
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }); 
});


$('#change_photo').click(function(event){
  event.preventDefault();
  Swal.fire({
          title: 'Select a Photo',
          showCancelButton: true,
          confirmButtonText: 'Upload',
          input: 'file',
          inputAttributes: {
            accept: 'image/*',
            'aria-label': 'Upload your profile picture'
          },
          onBeforeOpen: () => {
              $(".swal2-file").change(function () {
                  var reader = new FileReader();
                  reader.readAsDataURL(this.files[0]);
              });
          }
      }).then((file) => {
          if (file.value) {
              var formData = new FormData();
              var file = $('.swal2-file')[0].files[0];
              formData.append("photo", file);
              Pace.restart();
              Pace.track(function () {
              $.ajax({
                  method: 'post',
                  url: "{{ route('item.api_upload_photo',['id' => $items->id ]) }}",
                  data: formData,
                  processData: false,
                  contentType: false,
                  success: function (data) {
                    $("#box_photo").attr("src","/"+data.data);
                    Toast.fire({
                            type: 'success',
                            title: name+' Successfully Change.'
                          })
                  },
                  error: function(error) {
                    Toast.fire({
                            type: 'error',
                            title: 'Invalid Input File.'
                          })
                  }
              })
            });

          }
    })
});

$('#update_item').on('submit',function(event){

    event.preventDefault();
    Pace.restart();
    var formData = new FormData(this);

      Pace.track(function () {
                $.ajax({
                      url: "{{ route('item.update',['id' => $items->id ]) }}",
                      type: "post",
                      data:formData,
                      cache:false,
                      contentType: false,
                      processData: false,
                      dataType: 'JSON',
                      success: function(data) {
                        clearError();
                        // api_supplier_info();
                        Toast.fire({
                          type: 'success',
                          title: name+' Successfully Updated.'
                        })
                      },
                      error: function(error){
                        Toast.fire({
                          type: 'error',
                          title: 'Invalid Inputs.'
                        })
                        clearError();
                       $.each(error.responseJSON.errors, function(key, value){                         
                                $("input[id="+key+"]").addClass("is-invalid");
                                $("#"+key+"_this").append("<span class='help-block' style='color:red;'>"+value+"</span>");
                          });
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
            <h1>Item</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Item</li>
              <li class="breadcrumb-item active">Edit Item</li>
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
            <h3 class="card-title">Edit Item</h3>
          </div>
          <!-- /.card-header -->
          <form role="form" method="post" id="update_item" enctype="multipart/form-data">
            <div class="card-body">
              <div class="row">
                <div class="col-md-2">
                <div class="card-body box-profile">
                      <div class="text-center">
                      
                        <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-12">
                        @if ($items->photo != null)
                        <img class="profile-user-img img-fluid"
                            src="{{ asset($items->photo) }}"
                            alt="User profile picture" id="box_photo" style="margin-bottom:10px; width:140px;">
                        @else
                        <img class="profile-user-img img-fluid"
                            src="{{ asset('admin/dist/img/no-photos.png') }}"
                            alt="User profile picture" id="box_photo" style="margin-bottom:10px; width:140px;">
                        @endif
                        </div>
                        <div class="col-md-12">
                        <button id="change_photo" class="btn btn-block btn-primary btn-sm"><i class="nav-icon fas fa-pen" style="color:white; margin-right:10px;"></i>Change</button>
                        <button id="remove_photo" class="btn btn-block btn-primary btn-sm disabled"><i class="nav-icon fas fa-trash" style="color:white; margin-right:10px;"></i>Remove</button>
                        </div>
                        </div>
                      </div>
                    </div>
                </div>                
                <div class="col-md-5">
                  <div class="form-group" id="item_id_this">
                      <label for="name">Item ID</label>
                      <input type="text" class="form-control" id="item_id" name="item_id" value="{{ $items->item_id }}" disabled>
                  </div>  
                  <div class="form-group" id="name_this">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" value="{{ $items->name }}"  placeholder="Name">
                  </div>  
                  <div class="form-group" id="supplier_this">
                    <label>Supplier</label>
                    <a href="{{ route('supplier.create' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                    <select class="select2" id="supplier" name="supplier[]" multiple="multiple" data-placeholder="Select a Supplier" style="width: 100%;">
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group" id="weight_this">
                          <label for="name">Weight</label>
                          <input type="number" class="form-control" id="weight" name="weight" placeholder="Weight" value="{{ $items->weight }}" >
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="weight_uom_this">
                        <a href="{{ route('uom.index' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>  
                          <label>UOM <small>(Weight)</small></label>
                            <select class="select2" id="weight_uom" name="weight_uom" data-placeholder="UOM" style="width: 100%;">
                            </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="low_stock_this">
                          <label for="name">Low Stock <small>(Alert Qty)</small></label>
                          <input type="number" class="form-control" id="low_stock" name="low_stock" placeholder="Qty" value="{{ $items->low_stock }}" >
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="item_uom_this">
                      <a href="{{ route('uom.index' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                        <label>UOM <small>(Item)</small></label>
                          <select class="select2" id="item_uom" name="item_uom" data-placeholder="UOM" style="width: 100%;">
                          </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5">
                        <div class="form-group" id="unit_price_this">
                            <label for="name">Unit Price</small></label>
                            <input type="number" class="form-control" id="default_purchase_price" name="default_purchase_price" value="{{ $items->default_purchase_price }}" placeholder="Unit Price">
                        </div>
                    </div>
                    <div class="col-md-7">
                      <div class="form-group" id="category_id_this">
                        <label>Category</label>
                        <a href="{{ route('uom.index' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                        <select class="select2" id="category_id" name="category_id" data-placeholder="Select a Category" style="width: 100%;">
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                   <div class="form-group">
                          <label>Description</label>
                          <textarea class="form-control" id="description" name="description" rows="5" placeholder="Details...">{{ $items->description }}</textarea>
                    </div>
              </div>
              </div>
              <!-- /.row -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
            <button id="update" class="btn btn-primary">Update</button>
              <button id="reset" class="btn btn-primary">Reset</button>
              <a href="{{ route('item.index') }}" class="btn btn-primary">Back</a>
            </div>
          </form>
          </div>
        <!-- /.card -->
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection