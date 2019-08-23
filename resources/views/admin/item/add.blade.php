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


      $('.select2').select2();
      get_supplier_id();


  get_supplier_list();
  // get_supplier_id();

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

  function get_supplier_id(){
    $.ajax({
        type: 'get',
        url: "{{ route('item.get_item_id') }}",
        success: function(data) {
           $('#item_id').val(data.item_id);
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
            $("#supplier").append('<option value="'+row.id+'">'+row.name+'</option>');
        })

        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

  function clear_fields(){
      $('#fullname').val('');
      $('#address').val('');
      $('#email').val('');
      $("#tel_no").val('');
      $("#mobile_no").val('');
      $("#photo").val('');
      $("#details").val('');
      $("#remarks").val('');
      $company_multi_select.val(null).trigger("change");
  }

  function clearError(){
    $( ".is-invalid" ).removeClass("is-invalid");
    $( ".help-block" ).remove();
  }
  
  $('#clear').click(function(event){
    event.preventDefault();
    clear_fields();
  });

  $('#add_supplier').on('submit',function(event){

      event.preventDefault();
      Pace.restart();
      var formData = new FormData(this);
      
      formData.append( 'supplier_id', $('#supplier_id').val());

        Pace.track(function () {
                  $.ajax({
                        url: "{{ route('supplier.store') }}",
                        type: "post",
                        data:formData,
                        cache:false,
                        contentType: false,
                        processData: false,
                        dataType: 'JSON',
                        success: function(data) {
                          clearError();
                          get_supplier_id();
                          clear_fields();
                          Toast.fire({
                            type: 'success',
                            title: name+' Successfully Added.'
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
              <li class="breadcrumb-item active">Add Itemlier</li>
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
            <h3 class="card-title">Add Item</h3>
          </div>
          <!-- /.card-header -->
          <form role="form" method="post" id="add_supplier" enctype="multipart/form-data">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="item_id">Item ID</label>
                      <input type="text" class="form-control" id="item_id" name="item_id" disabled>
                  </div>
                  <div class="form-group" id="name_this">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name">
                  </div>  
                  <div class="form-group" id="supplier_this">
                    <label>Supplier</label>
                    <a href="{{ route('supplier.create' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                    <select class="select2" id="supplier" name="supplier[]" multiple="multiple" data-placeholder="Select a State" style="width: 100%;">
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="row">
                <div class="col-md-6">
                      <div class="form-group" id="weight_this">
                          <label for="name">Weight</label>
                          <input type="number" class="form-control" id="weight" name="weight" placeholder="Weight">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="weight_uom_this">
                          <label for="name">UOM <small>(Weight)</small></label>
                          <input type="text" class="form-control" id="weight_uom" name="weight_uom" placeholder="unit">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="low_stock_this">
                          <label for="name">Low Stock <small>(Alert Qty)</small></label>
                          <input type="number" class="form-control" id="low_stock" name="low_stock" placeholder="Qty">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="item_uom_this">
                      <label>UOM <small>(Item)</small></label>
                        <select class="select2" id="item_uom" name="item_uom" data-placeholder="Select a State" style="width: 100%;">
                          <option value="BAG">Bag - BAG</option>
                          <option value="BND">Bucket - BND</option>
                          <option value="BOWL">Bowl - BOWL</option>
                          <option value="CRD">Card</option>
                          <option value="CS">Centimeters</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="form-group" id="category_id_this">
                    <label>Category</label>
                    <a href="{{ route('company.create' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                    <select class="select2" id="category_id" name="category_id" data-placeholder="Select a State" style="width: 100%;">
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                          <label>Description</label>
                          <textarea class="form-control" id="details" name="details" rows="5" placeholder="Details..."></textarea>
                    </div>
                    <div class="form-group" id="photo_this">
                          <label for="photo">Image File</label>
                          <div class="input-group">
                                  <input type="file" id="photo" name="photo">
                          </div>
                    </div>
                </div>
                </div>
              <!-- /.row -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{ route('item.index') }}" class="btn btn-primary">Back</a>
              <button type="submit" class="btn btn-primary">Submit</button>
              <button id="clear" class="btn btn-primary">Clear</button>
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