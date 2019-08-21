@extends('admin.partials.master')

@section('style')
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables/dataTables.bootstrap4.css') }}">
@endsection

@section('script')
<script src="{{ asset('admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables/dataTables.bootstrap4.js') }}"></script>
<script>
$('#example2').DataTable();
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
              <li class="breadcrumb-item active">Supplier Profile</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle"
                       src="{{ asset($suppliers->photo) }}"
                       alt="User profile picture">
                </div>
                <h3 class="profile-username text-center">{{ $suppliers->fullname }}</h3>
                <p class="text-muted text-center">{{ $suppliers->supplier_id }}</p>

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
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
              <a href="{{ route('supplier.index') }}" class="btn btn-danger float-right"><i class="nav-icon fas fa-long-arrow-alt-left" style="color:white; margin-right:10px;"></i>Back</a>
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#companies" data-toggle="tab">Companies</a></li>
                  <li class="nav-item"><a class="nav-link" href="#items" data-toggle="tab">Items</a></li>
                  <li class="nav-item"><a class="nav-link" href="#info" data-toggle="tab"><i class="fas fa-info mr-1"></i>Info</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="companies">
                  <table id="example2" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                      <th>ID</th>
                      <th>Photo</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach( $suppliers->company as $company )
                    <tr>
                      <td>{{ $company->company_id }}</td>
                      <td><div class="text-center"> <img class="img-fluid img-circle"
                          src="{{ asset($company->photo) }}" style="max-width:50px;"
                          alt="User profile picture"> </div></td>
                      <td>{{ $company->name }}</td>
                      <td>{{ $company->email }}</td>
                      <td>
                          <a class="btn btn-success" href="/company/{{ $company->id }}" style="color:white;"><i class="fas fa-eye"></i></a></td>
                      </tr>
                    @endforeach
                    </tbody>
                  </table>
                  </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="items">

                  </div>
                  <!-- /.tab-pane -->

                  <div class="tab-pane" id="info">
                    <form class="form-horizontal" role="form" method="post" id="update_company">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                      <label for="company_id">Company ID</label>
                      <input type="text" class="form-control" id="company_id" name="company_id" value="{{ $suppliers->supplier_id }}" disabled>
                  </div>
                  <div class="form-group">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="full_name" name="fullname" placeholder="Name" value="{{ $suppliers->fullname }}">
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