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

var $company_multi_select = $('.select2').select2();


get_company_list();


const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

function api_supplier_info(){

  $.ajax({
        type: 'get',
        url: "{{ route('supplier.api_view',['id' => $suppliers->id ]) }}",
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

function get_company_list(){
    $.ajax({
        type: 'get',
        url: "{{ route('supplier.api_company_list') }}",
        success: function(data) {

        JSON.parse(data).data.forEach(row => {
            $("#company").append('<option value="'+row.id+'">'+row.name+'</option>');
        })

        api_selected_company();

        },
        error: function(error){
          console.log('error');
        }
     }); 

}

function api_selected_company(){

    $.ajax({
        type: 'get',
        dataType: 'JSON',
        url: "{{ route('supplier.api_selected_company',['id' => $suppliers->id ]) }}",
        success: function(data) {

            var test = data.data;

            $company_multi_select.val(test).trigger("change");

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
        url: "{{ route('supplier.api_view',['id' => $suppliers->id ]) }}",
        success: function(data) {
           $.each(data, function(key, value){                         
              $('#'+key+'').val(value);
              $('#'+key+'').text(value);
           });
           api_selected_company();
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
                  url: "{{ route('supplier.api_upload_photo',['id' => $suppliers->id ]) }}",
                  data: formData,
                  processData: false,
                  contentType: false,
                  success: function (data) {
                    api_supplier_info();
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

$('#update_supplier').on('submit',function(event){

    event.preventDefault();
    Pace.restart();
    var formData = new FormData(this);
    formData.append( 'supplier_id', $('#supplier_id').val());
      Pace.track(function () {
                $.ajax({
                      url: "{{ route('supplier.update',['id' => $suppliers->id ]) }}",
                      type: "post",
                      data:formData,
                      cache:false,
                      contentType: false,
                      processData: false,
                      dataType: 'JSON',
                      success: function(data) {
                        clearError();
                        api_supplier_info();
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
            <h1>Supplier</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Edit Supplier Info</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3 col-sm-12">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <div class="row" style="margin-bottom:20px;">
                  <div class="col-md-6">
                  <button id="change_photo" class="btn btn-primary btn-sm"><i class="nav-icon fas fa-pen" style="color:white; margin-right:10px;"></i>Change</button>
                  </div>
                  <div class="col-md-6">
                  <button id="remove_photo" class="btn btn-primary btn-sm disabled"><i class="nav-icon fas fa-trash" style="color:white; margin-right:10px;"></i>Remove</button>
                  </div>
                  </div>
                  <img class="profile-user-img img-fluid img-circle"
                       src="{{ asset($suppliers->photo) }}"
                       alt="User profile picture" id="box_photo">
                </div>
                <h3 class="profile-username text-center" id="box_fullname" >{{ $suppliers->fullname }}</h3>
                <p class="text-muted text-center" id="box_supplier_id">{{ $suppliers->supplier_id }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                    <p class="text-muted" id="box_address">{{ $suppliers->address }}</p>
                  </li>
                  <li class="list-group-item">
                    <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                    <p class="text-muted" id="box_email">{{ $suppliers->email }}</p>
                  </li>
                  <li class="list-group-item">
                    <strong><i class="fas fa-mobile-alt mr-1"></i> Mobile No.</strong>
                    <p class="text-muted" id="box_mobile_no">{{ $suppliers->mobile_no }}</p>
                  </li>
                  <li class="list-group-item">
                    <strong><i class="fas fa-phone-alt mr-1"></i> Tel no.</strong>
                    <p class="text-muted" id="box_tel_no">{{ $suppliers->tel_no }}</p>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.card -->
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
              <a href="{{ route('supplier.index') }}" class="btn btn-danger float-right"><i class="nav-icon fas fa-long-arrow-alt-left" style="color:white; margin-right:10px;"></i>Back</a>
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="active nav-link" href="#settings" data-toggle="tab"><i class="fas fa-pen mr-1"></i>Edit Info</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="settings">
                    <form class="form-horizontal" role="form" method="post" id="update_supplier">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="company_id">Company ID</label>
                      <input type="text" class="form-control" id="supplier_id" name="supplier_id" value="{{ $suppliers->supplier_id }}" disabled>
                  </div>
                  <div class="form-group">
                      <label for="name">Full Name</label>
                      <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Name" value="{{ $suppliers->fullname }}">
                  </div>
                  <div class="form-group" id="company_id_this">
                  <label>Company</label>
                  <a href="{{ route('company.create' )}}" class="btn btn-primary btn-sm float-right"><i class="nav-icon fas fa-plus" style="color:white;"></i></a>
                  <select class="select2" id="company" name="company_id" data-placeholder="Select a State" style="width: 100%;">
                  </select>
                  </div>
                  <div class="form-group">
                      <label for="address">Address</label>
                      <input type="text" class="form-control" id="address" name="address" placeholder="Address" value="{{ $suppliers->address }}">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="email">Email Address</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="{{ $suppliers->email }}">
                  </div>
                  <div class="form-group">
                      <label for="tel_no">Telephone No.</label>
                      <input type="text" class="form-control" id="tel_no" name="tel_no" placeholder="Telephone No" value="{{ $suppliers->tel_no }}">
                  </div>
                  <div class="form-group">
                      <label for="mobile_no">Mobile No.</label>
                      <input type="text" class="form-control" id="mobile_no" name="mobile_no" placeholder="Mobile No" value="{{ $suppliers->mobile_no }}">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                      <label>Details</label>
                      <textarea class="form-control" id="details" name="details" rows="2" placeholder="Details...">{{ $suppliers->details }}</textarea>
                  </div>
                  <div class="form-group">
                      <label>Remarks</label>
                      <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Remarks...">{{ $suppliers->remarks }}</textarea>
                  </div>
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <button id="update" class="btn btn-primary">Update</button>
              <button id="reset" class="btn btn-primary">Reset</button>
            </div>
                    </form>
                  </div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.nav-tabs-custom -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection