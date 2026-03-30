
<?php  require 'includes/header.php';
require_once  'includes/helpers.php';

     



if (!isset($_SESSION['user-id'])) {
    header("Location: signin.php");
    exit();
}

$totalUsers = getCount('users', $connection);
$totalCategories = getCount('categories', $connection);
$totalPosts = getCount('posts', $connection);



?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <title>Dashboard </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Admin & Dashboard Template" name="description">
    <meta content="Codebucks" name="author">
    
    <!-- layout setup -->
    <!-- <script type="module" src="assets/js/layout-setup.js"></script> -->
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo-sm.png">
      <!-- select2 -->
    <link href="assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css">

    <!-- Simplebar Css -->
    <link rel="stylesheet" href="assets/libs/simplebar/simplebar.min.css">
    
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css">
    
    <!--icons css-->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css">

</head>

<body>
<!-- Begin page -->
<div id="layout-wrapper">

    <!-- Start topbar -->
    
    <!-- End topbar -->
    <!-- ========== Left Sidebar Start ========== -->
    <?= include 'includes/sidebar.php' ?>
    <!-- Left Sidebar End -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <!-- ========== Left Sidebar Start ========== -->
   
    <!-- Left Sidebar End -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
     <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                
                <!-- end page title -->
                <div class="row">
                    <div class="col-xl-6 col-xxl-5">
                        <div class="card sticky-top">
                            <div class="card-body p-0">
                                <div class="row align-items-center">
                                    <div class="col-md-5 border-end">
                                        <div class="text-center p-6">
                                            <div class="avatar avatar-xl avatar-circle overflow-hidden mb-5">
                                                <img src="assets/images/users/avatar-7.png" alt="Avatar Image" class="avatar-lg">
                                            </div>
                                            <h6 class="mb-2 fs-15">Alexandra Rodriguez</h6>
                                            <p class="text-muted small mb-5">alexandrarodriguez@gmail.com</p>
                                            <p class="fw-semibold mb-5">Appointments</p>
                                            <div class="d-flex justify-content-center mb-8">
                                                <div class="w-50 border-end">
                                                    <h4 class="mb-0 fs-18 fw-medium">10</h4>
                                                    <span class="text-muted">Past</span>
                                                </div>
                                                <div class="w-50">
                                                    <h4 class="mb-0 fs-18 fw-medium">3</h4>
                                                    <span class="text-muted">Upcoming</span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-primary w-100">Send Message</button>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="p-6">
                                            <div class="row gy-4 gx-6">
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Gender</p>
                                                    <p class="fs-14 fw-semibold mb-4">Male</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Birthday</p>
                                                    <p class="fs-14 fw-semibold mb-4">Jan 12, 1985</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Phone number</p>
                                                    <p class="fs-14 fw-semibold mb-4">(123) 456-7890</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Address</p>
                                                    <p class="fs-14 fw-semibold mb-4">123 Main St, Apt 4B</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">City</p>
                                                    <p class="fs-14 fw-semibold mb-4">New York</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">ZIP Code</p>
                                                    <p class="fs-14 fw-semibold mb-4">10001</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Registration Date</p>
                                                    <p class="fs-14 fw-semibold mb-0">Mar 15, 2020</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Status</p>
                                                    <p class="fs-14 fw-semibold mb-0">Active</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-xxl-7">
                        <div class="row">
                            <div class="col-md-6 col-xxl-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="mb-4">
                                            <img src="assets/images/apps/hospital/blood-pressure.png" width="28" alt="Blood Pressure">
                                        </div>
                                        <p class="text-muted mb-2">Blood Pressure</p>
                                        <h4 class="fw-medium mb-0">120 / 80 <small class="text-muted fw-normal">mmHg</small></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="mb-4">
                                            <img src="assets/images/apps/hospital/heart-attack.png" width="28" alt="Heart Rate">
                                        </div>
                                        <p class="text-muted mb-2">Heart Rate</p>
                                        <h4 class="fw-medium mb-0">78 <small class="text-muted fw-normal">/ min</small></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="mb-4">
                                            <img src="assets/images/apps/hospital/temperature.png" width="28" alt="Temperature">
                                        </div>
                                        <p class="text-muted mb-2">Temperature</p>
                                        <h4 class="fw-medium mb-0">98.6 <small class="text-muted fw-normal">°F</small></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="mb-4">
                                            <img src="assets/images/apps/hospital/red-blood-cells.png" width="28" alt="Oxygen">
                                        </div>
                                        <p class="text-muted mb-2">Oxygen (SpO₂)</p>
                                        <h4 class="fw-medium mb-0">97 <small class="text-muted fw-normal">%</small></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header border-bottom-0 pb-0">
                                        <h5 class="card-title">Health Metrics Timeline</h5>
                                        <div class="nav nav-pills" id="nav2-tab" role="tablist">
                                            <a class="nav-item nav-link py-1" id="nav2-home-tab" data-bs-toggle="tab" href="#nav2-home" aria-selected="true" role="tab">Heart Rate</a>
                                            <a class="nav-item nav-link py-1" id="nav2-profile-tab" data-bs-toggle="tab" href="#nav2-profile" aria-selected="false" tabindex="-1" role="tab">Blood Pressure</a>
                                            <a class="nav-item nav-link py-1" id="nav2-contact-tab" data-bs-toggle="tab" href="#nav2-contact" aria-selected="false" tabindex="-1" role="tab">Oxygen Levels</a>
                                            <a class="nav-item nav-link py-1" id="nav2-contact-tab" data-bs-toggle="tab" href="#nav2-contact" aria-selected="false" tabindex="-1" role="tab">Overall Status</a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="healthChart" data-colors='["var(--bs-primary)"]' class="apex-charts"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                </div>
            </div><!-- container-fluid -->
        </div><!-- End Page-content -->

        <!-- Begin Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <script>document.write(new Date().getFullYear())</script> © Aquiry.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="http://codebucks.in/" target="_blank" class="text-muted">Codebucks</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- END Footer -->
        <!-- Begin scroll top -->
          <div class="progress-wrap" id="progress-scroll">
            <svg class="progress-circle" width="100%" height="100%" viewBox="-1 -1 102 102">
              <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
            </svg>
          </div>
        <!-- END scroll top -->
    </div><!-- end main content-->

</div>
<!-- END layout-wrapper -->


<!-- Bootstrap bundle js -->
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Layouts main js -->
<script src="assets/libs/jquery/jquery.min.js"></script>

<!-- Metimenu js -->
<script src="assets/libs/metismenu/metisMenu.min.js"></script>

<!-- simplebar js -->
<script src="assets/libs/simplebar/simplebar.min.js"></script>

<script src="assets/libs/eva-icons/eva.min.js"></script>

<!-- Scroll Top init -->
<script src="assets/js/scroll-top.init.js"></script>
<!-- select2 -->
<script src="assets/libs/select2/js/select2.min.js"></script>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Progress js -->
<script src="assets/js/progress-bar.js"></script>

<!-- dashboard init -->
<script src="assets/js/dashboard/index.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>

</body>


</html>