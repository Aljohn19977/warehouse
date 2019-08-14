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
          <form role="form">
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                    <label for="supplier_id">Supplier ID</label>
                    <input type="text" class="form-control" id="supplier_id" name="supplier_id" disabled>
                </div>
                <div class="form-group">
                  <label>Company Name</label>
                  <select class="form-control select2" style="width: 100%;">
                    <option>Nestle</option>
                    <option>KRAFT</option>
                    <option>Del Monte</option>
                    <option>KFC</option>
                  </select>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address">
                </div>
                <div class="form-group">
                    <label for="tel_no">Telephone No.</label>
                    <input type="text" class="form-control" id="tel_no" name="tel_no" placeholder="Telephone No">
                </div>
                <div class="form-group">
                    <label for="mobile_no">Mobile No.</label>
                    <input type="text" class="form-control" id="mobile_no" name="mobile_no" placeholder="Mobile No">
                </div>
                <div class="form-group">
                    <label for="exampleInputFile">Image File</label>
                    <div class="input-group">
                            <input type="file" id="exampleInputFile">
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
                    <textarea class="form-control" id="remarks" name="remarks" rows="5" placeholder="Remarks..."></textarea>
                </div>
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->
          </div>
          </form>
          <!-- /.card-body -->
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Back</button>
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="submit" class="btn btn-primary">Clear</button>
          </div>
        </div>
        <!-- /.card -->
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
@endsection