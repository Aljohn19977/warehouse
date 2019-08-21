@extends('admin.partials.master')

@section('style')

@endsection

@section('script')
<script>
$(document).ready(function(){


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

function api_company_info(){

  $.ajax({
        type: 'get',
        url: "{{ route('company.api_view',['id' => $companies->id ]) }}",
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
        url: "{{ route('company.api_view',['id' => $companies->id ]) }}",
        success: function(data) {
           $.each(data, function(key, value){                         
              $('#'+key+'').val(value);
              $('#'+key+'').text(value);
           });
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
                  url: "{{ route('company.api_upload_photo',['id' => $companies->id ]) }}",
                  data: formData,
                  processData: false,
                  contentType: false,
                  success: function (data) {
                    api_company_info();
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

$('#update_company').on('submit',function(event){

    event.preventDefault();
    Pace.restart();
    var formData = new FormData(this);
    formData.append( 'company_id', $('#company_id').val() );

      Pace.track(function () {
                $.ajax({
                      url: "{{ route('company.update',['id' => $companies->id ]) }}",
                      type: "post",
                      data:formData,
                      cache:false,
                      contentType: false,
                      processData: false,
                      dataType: 'JSON',
                      success: function(data) {
                        clearError();
                        api_company_info();
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
            <h1>Company</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Edit Company Info</li>
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
                       src="{{ asset($companies->photo) }}"
                       alt="User profile picture" id="box_photo">
                </div>
                <h3 class="profile-username text-center" id="box_name" >{{ $companies->name }}</h3>
                <p class="text-muted text-center" id="box_company_id">{{ $companies->company_id }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                    <p class="text-muted" id="box_address">{{ $companies->address }}</p>
                  </li>
                  <li class="list-group-item">
                    <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                    <p class="text-muted" id="box_email">{{ $companies->email }}</p>
                  </li>
                  <li class="list-group-item">
                    <strong><i class="fas fa-mobile-alt mr-1"></i> Mobile No.</strong>
                    <p class="text-muted" id="box_mobile_no">{{ $companies->mobile_no }}</p>
                  </li>
                  <li class="list-group-item">
                    <strong><i class="fas fa-phone-alt mr-1"></i> Tel no.</strong>
                    <p class="text-muted" id="box_tel_no">{{ $companies->tel_no }}</p>
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
              <a href="{{ route('company.index') }}" class="btn btn-danger float-right"><i class="nav-icon fas fa-long-arrow-alt-left" style="color:white; margin-right:10px;"></i>Back</a>
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="active nav-link" href="#settings" data-toggle="tab"><i class="fas fa-pen mr-1"></i>Edit Info</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="settings">
                    <form class="form-horizontal" role="form" method="post" id="update_company">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="company_id">Company ID</label>
                      <input type="text" class="form-control" id="company_id" name="company_id" value="{{ $companies->company_id }}" disabled>
                  </div>
                  <div class="form-group">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{ $companies->name }}">
                  </div>
                  <div class="form-group">
                      <label for="address">Address</label>
                      <input type="text" class="form-control" id="address" name="address" placeholder="Address" value="{{ $companies->address }}">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="email">Email Address</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="{{ $companies->email }}">
                  </div>
                  <div class="form-group">
                      <label for="tel_no">Telephone No.</label>
                      <input type="text" class="form-control" id="tel_no" name="tel_no" placeholder="Telephone No" value="{{ $companies->tel_no }}">
                  </div>
                  <div class="form-group">
                      <label for="mobile_no">Mobile No.</label>
                      <input type="text" class="form-control" id="mobile_no" name="mobile_no" placeholder="Mobile No" value="{{ $companies->mobile_no }}">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                      <label>Details</label>
                      <textarea class="form-control" id="details" name="details" rows="2" placeholder="Details...">{{ $companies->details }}</textarea>
                  </div>
                  <div class="form-group">
                      <label>Remarks</label>
                      <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Remarks...">{{ $companies->remarks }}</textarea>
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