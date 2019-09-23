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
          
  get_item_id();
  get_supplier_list();
  get_category_list();
  get_item_uom_list();
  // get_supplier_id();;

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

  function get_item_id(){
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
            var newOption = new Option(row.name, row.id, false, false);
            $('#supplier').append(newOption).trigger('change');
        })
        $('.select2').select2().val(null).trigger("change");
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
        $('.select2').select2().val(null).trigger("change");
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
            var newOption = new Option(row.name, row.acronym, false, false);
            $('#item_uom').append(newOption).trigger('change');
        })
        
        $('.select2').select2().val(null).trigger("change");

        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

  function clear_fields(){
      $('#name').val('');
      $('#weight').val('');
      $('#low_stock').val('');
      $('#purchase_price').val('');
      $('#sale_price').val('');
      $("#photo").val('');
      $("#description").val('');
      $('.select2').select2().val(null).trigger("change");
  }

  function clearError(){
    $( ".is-invalid" ).removeClass("is-invalid");
    $( ".help-block" ).remove();
  }
  
  $('#clear').click(function(event){
    event.preventDefault();
    clear_fields();
  });

  $("#length" ).change(function() {

    var length = $('#length').val();
    var width = $('#width').val();
    var depth = $('#depth').val();
    var volume = length*width*depth;

    $('#volume').val(Math.round(volume * 100.0) / 100.0);

  });

  $("#width" ).change(function() {
    var length = $('#length').val();
    var width = $('#width').val();
    var depth = $('#depth').val();
    var volume = length*width*depth;

    $('#volume').val(Math.round(volume * 100.0) / 100.0);

  });

  $("#depth" ).change(function() {

    var length = $('#length').val();
    var width = $('#width').val();
    var depth = $('#depth').val();
    var volume = length*width*depth;

    $('#volume').val(Math.round(volume * 100.0) / 100.0);

  });

  $('#add_item').on('submit',function(event){

      event.preventDefault();
      Pace.restart();
      var formData = new FormData(this);
      
      formData.append( 'item_id', $('#item_id').val());

        Pace.track(function () {
                  $.ajax({
                        url: "{{ route('item.store') }}",
                        type: "post",
                        data:formData,
                        cache:false,
                        contentType: false,
                        processData: false,
                        dataType: 'JSON',
                        success: function(data) {
                          clearError();
                          get_item_id();
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
          <form role="form" method="post" id="add_item" enctype="multipart/form-data">
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
                    <select class="select2" id="supplier" name="supplier[]" multiple="multiple" data-placeholder="Select a Supplier" style="width: 100%;">
                    </select>
                  </div>
                  <div class="form-group" id="category_id_this">
                        <label>Category</label>
                        <a href="{{ route('uom.index' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                        <select class="select2" id="category_id" name="category_id" data-placeholder="Select a Category" style="width: 100%;">
                        </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="row">
                <div class="col-md-6">
                      <div class="form-group" id="weight_this">
                          <label for="name">Weight <small>(Kilogram)</small></label>
                          <input type="text" class="form-control" id="weight" name="weight" placeholder="Weight">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="width_this">
                          <label>Width <small>(Meter)</small></label>
                          <input type="text" class="form-control" id="width" name="width" placeholder="Width">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="length_this">
                          <label>Length <small>(Meter)</small></label>
                          <input type="text" class="form-control" id="length" name="length" placeholder="Length">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="depth_this">
                          <label>Depth <small>(Meter)</small></label>
                          <input type="text" class="form-control" id="depth"  name="depth" placeholder="Depth">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="cubic_this">
                          <label>Cubic Total <small>(Meter)</small></label>
                          <input type="text" class="form-control" id="volume" name="volume" placeholder="Cubic" readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="low_stock_this">
                          <label for="name">Minimum Stock <small>(Qty)</small></label>
                          <input type="text" class="form-control" id="min_stock" name="min_stock" placeholder="Qty">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group" id="low_stock_this">
                          <label for="name">Maximum Stock <small>(Qty)</small></label>
                          <input type="text" class="form-control" id="max_stock" name="max_stock" placeholder="Qty">
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
                </div>
                <div class="col-md-4">
                    <div class="row">
                      <div class="col-md-6">
                          <div class="form-group" id="purchase_price_this">
                              <label for="name">Default Purchase Price</small></label>
                              <input type="text" class="form-control" id="purchase_price" name="purchase_price" placeholder="Unit Price">
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group" id="sale_price_this">
                              <label for="name">Default Sale Price</small></label>
                              <input type="text" class="form-control" id="sale_price" name="sale_price" placeholder="Unit Price">
                          </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group" id="tax_this">
                          <label>Tax Percentage</label>
                          <input type="text" class="form-control" id="tax" name="tax" placeholder="Tax">
                      </div>
                      </div>                     
                      <div class="col-md-6">
                        <div class="form-group" id="type_this">
                          <label>Item Type</label>
                            <select class="select2" id="type" name="type" data-placeholder="Item Type" style="width: 100%;">
                              <option></option>
                              <option value="Serialize">Serialize</option>
                              <option value="Batch Tracked">Batch Tracked</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                          <label>Description</label>
                          <textarea class="form-control" id="description" name="description" rows="2" placeholder="Details..."></textarea>
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