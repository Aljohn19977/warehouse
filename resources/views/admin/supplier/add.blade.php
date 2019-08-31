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

      //Initialize Select2 Elements
  $('.select2').select2();
      

  get_company_list();
  get_supplier_id();

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
        url: "{{ route('supplier.get_supplier_id') }}",
        success: function(data) {
           $('#supplier_id').val(data.supplier_id);
        },
        error: function(error){
          console.log('error');
        }
     }); 
  }

  function get_company_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('supplier.api_company_list') }}",
        success: function(data) {

        JSON.parse(data).data.forEach(row => {
            var newOption = new Option(row.name, row.id, false, false);
            $('#company').append(newOption).trigger('change');
        })
        $('.select2').select2().val(null).trigger("change");
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
            <h1>Supplier</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Supplier</li>
              <li class="breadcrumb-item active">Add Supplier</li>
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
            <h3 class="card-title">Add Supplier</h3>
          </div>
          <!-- /.card-header -->
          <form role="form" method="post" id="add_supplier" enctype="multipart/form-data">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="supplier_id">Supplier ID</label>
                      <input type="text" class="form-control" id="supplier_id" name="supplier_id" disabled>
                  </div>
                  <div class="form-group" id="fullname_this">
                      <label for="name">Full Name</label>
                      <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Name">
                  </div>
                  <div class="form-group" id="company_id_this">
                  <label>Company</label>
                  <a href="{{ route('company.create' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                  <select class="select2" id="company" name="company_id" data-placeholder="Select a Company" style="width: 100%;">
                  </select>
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
                  <div class="form-group" id="photo_this">
                      <label for="photo">Image File</label>
                      <div class="input-group">
                              <input type="file" id="photo" name="photo">
                      </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                      <label>Details</label>
                      <textarea class="form-control" id="details" name="details" rows="5" placeholder="Details..."></textarea>
                  </div>
                  <div class="form-group">
                      <label>Remarks</label>
                      <textarea class="form-control" id="remarks" name="remarks" rows="4" placeholder="Remarks..."></textarea>
                  </div>
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{ route('supplier.index') }}" class="btn btn-primary">Back</a>
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