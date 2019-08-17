<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
@include('admin.partials.head')
<body class="hold-transition sidebar-mini sidebar-collapes">
<div class="wrapper">

  <!-- Navbar -->
  @include('admin.partials.navbar')
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  @include('admin.partials.mainsidebar')
  <!-- Content Wrapper. Contains page content -->
  @yield('content')
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->

 @yield('control_sidebar') 
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
  @include('admin.partials.footer')
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@yield('script')
<!-- AdminLTE App -->
<script src="{{ asset('admin/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('admin/dist/js/pace.min.js') }}"></script>
<script src="{{ asset('admin/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
  /** add active class and stay opened when selected */
var url = window.location;

// for sidebar menu entirely but not cover treeview
$('ul.nav-sidebar a').filter(function() {
    return this.href == url;
}).addClass('active');

// for treeview
$('ul.nav-treeview a').filter(function() {
    return this.href == url;
}).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open') .prev('a').addClass('active');
</script>
</body>
</html>
