@extends('admin.partials.master')

@section('style')

@endsection

@section('script')

<script>
$(document).ready(function(){


  get_warehouse_id();

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

  function get_warehouse_id(){
    $.ajax({
        type: 'get',
        url: "{{ route('warehouse.get_warehouse_id') }}",
        success: function(data) {
           $('#warehouse_id').val(data.warehouse_id);
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }


  function clear_fields(){
      $('#name').val('');
      $('#address').val('');
      $('#email').val('');
      $("#tel_no").val('');
      $("#mobile_no").val('');
      $("#photo").val('');
      $("#details").val('');
      $("#remarks").val('');
  }

  function clearError(){
    $( ".is-invalid" ).removeClass("is-invalid");
    $( ".help-block" ).remove();
  }
  
  $('#clear').click(function(event){
    event.preventDefault();
    clear_fields();
  });

  $('#add_warehouse').on('submit',function(event){

      event.preventDefault();
      Pace.restart();
      var formData = new FormData(this);
      
      formData.append( 'warehouse_id', $('#warehouse_id').val());

        Pace.track(function () {
                  $.ajax({
                        url: "{{ route('warehouse.store') }}",
                        type: "post",
                        data:formData,
                        cache:false,
                        contentType: false,
                        processData: false,
                        dataType: 'JSON',
                        success: function(data) {
                          clearError();
                          get_warehouse_id();
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
            <h1>Warehouse</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Warehouse</li>
              <li class="breadcrumb-item active">Add Warehouse</li>
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
            <h3 class="card-title">Add Warehouse</h3>
          </div>
          <!-- /.card-header -->
          <form role="form" method="post" id="add_warehouse" enctype="multipart/form-data">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="warehouse_id">Warehouse ID</label>
                      <input type="text" class="form-control" id="warehouse_id" name="warehouse_id" disabled>
                  </div>
                  <div class="form-group" id="name_this">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name">
                  </div>
                  <div class="form-group" id="address_this">
                      <label for="address">Address</label>
                      <input type="text" class="form-control" id="address" name="address" placeholder="Address">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group" id="email_this">
                      <label for="email">Email Address</label>
                      <input type="text" class="form-control" id="email" name="email" placeholder="Email Address">
                  </div>
                  <div class="form-group" id="tel_no_this">
                      <label for="tel_no">Telephone No.</label>
                      <input type="text" class="form-control" id="tel_no" name="tel_no" placeholder="Telephone No">
                  </div>
                  <div class="form-group" id="mobile_no_this">
                      <label for="mobile_no">Mobile No.</label>
                      <input type="text" class="form-control" id="mobile_no" name="mobile_no" placeholder="Mobile No">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                      <label>Details</label>
                      <textarea class="form-control" id="details" name="details" rows="2" placeholder="Details..."></textarea>
                  </div>
                  <div class="form-group">
                      <label>Remarks</label>
                      <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Remarks..."></textarea>
                  </div>
                  <div class="form-group" id="photo_this">
                      <label for="photo">Image File</label>
                      <div class="input-group">
                              <input type="file" id="photo" name="photo">
                      </div>
                  </div>
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{ route('warehouse.index') }}" class="btn btn-primary">Back</a>
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