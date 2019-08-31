@extends('admin.partials.master')

@section('style')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
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
          <form role="form" method="post" id="update_company" enctype="multipart/form-data">
            <div class="card-body">
              <div class="row">
                <div class="col-lg-2 col-md-12">
                <div class="card-body box-profile">
                      <div class="text-center">
                      
                        <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-12">
                        @if ($companies->photo != null)
                        <img class="profile-user-img img-fluid"
                            src="{{ asset($companies->photo) }}"
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
                <div class="col-lg-5 col-md-12">
                  <div class="form-group">
                      <label for="company_id">Company ID</label>
                      <input type="text" class="form-control" id="company_id" name="company_id" value="{{ $companies->company_id }}" disabled>
                  </div>
                  <div class="form-group" id="name_this">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{ $companies->name }}">
                  </div>
                  <div class="form-group" id="address_this">
                      <label for="address">Address</label>
                      <input type="text" class="form-control" id="address" name="address" placeholder="Address" value="{{ $companies->address }}">
                  </div>
                </div>
                <div class="col-lg-5 col-md-12">
                  <div class="form-group" id="email_this">
                      <label for="email">Email Address</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="{{ $companies->email }}">
                  </div>
                  <div class="form-group" id="tel_no_this">
                      <label for="tel_no">Telephone No.</label>
                      <input type="text" class="form-control" id="tel_no" name="tel_no" placeholder="Telephone No" value="{{ $companies->tel_no }}">
                  </div>
                  <div class="form-group" id="mobile_no_this">
                      <label for="mobile_no">Mobile No.</label>
                      <input type="text" class="form-control" id="mobile_no" name="mobile_no" placeholder="Mobile No" value="{{ $companies->mobile_no }}">
                  </div>
                </div>
              </div>
              <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label>Details</label>
                            <textarea class="form-control" id="details" name="details" rows="2" placeholder="Details...">{{ $companies->details }}</textarea>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Remarks...">{{ $companies->remarks }}</textarea>
                        </div>
                    </div>
                </div>
              <!-- /.row -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
            <button id="update" class="btn btn-primary">Update</button>
              <button id="reset" class="btn btn-primary">Reset</button>
              <a href="{{ route('company.index') }}" class="btn btn-primary">Back</a>
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