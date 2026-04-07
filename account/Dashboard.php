
<?php $pageTitle = 'Dashboard';
  require 'includes/header.php';
require_once  'includes/helpers.php';


     



if (!isset($_SESSION['user-id'])) {
    header("Location: signin.php");
    exit();
}

$totalUsers = getCount('users', $connection);
$totalCategories = getCount('categories', $connection);
$totalPosts = getCount('posts', $connection);



?>



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
                <div class="row">
                    <div class="col-12">
                        
                    </div>
                </div>
                <!-- end page title -->
                <div class="row">
                    <div class="col-xl-8 col-xxl-9">
                        <div class="row">
                            <div class="col-xxl-5">
                              
                            </div>
                            <div class="col-xxl-7">
                                <div class="card">
                                   
                                    
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between mb-6">
                                            <div>
                                                <h6 class="mb-3">Total Post</h6>
                                                <p class="text-muted mb-0"><?= $totalPosts ?></p>
                                            </div>
                                            <div class="position-relative d-inline-block avatar-progress progress-75">
                                                <svg class="position-absolute top-0 start-0 progress-svg">
                                                    <circle class="progress-bg"></circle>
                                                    <circle class="progress-circle stroke-info"></circle>
                                                </svg>
                                                <div class="avatar size-11 avatar-label-info avatar-circle">
                                                    <i data-eva="shopping-bag-outline"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0 fw-medium"><?= $totalPosts ?></h4>
                                            <div class="dropdown">
                                                <a href="#!" class="text-muted" data-bs-toggle="dropdown" aria-label="more"><i data-eva="more-horizontal-outline" class="size-4"></i></a>
                                                <ul class="dropdown-menu dropdown-menu-animated dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#!">Today</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Week</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Month</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between mb-6">
                                            <div>
                                                <h6 class="mb-3">Total Users</h6>
                                                <p class="text-muted mb-0"><span class="text-success me-1"><i data-eva="trending-up" class="size-4 me-1"></i>5.7%</span> vs last week</p>
                                            </div>
                                            <div class="position-relative d-inline-block avatar-progress progress-80">
                                                <svg class="position-absolute top-0 start-0 progress-svg">
                                                    <circle class="progress-bg"></circle>
                                                    <circle class="progress-circle stroke-success"></circle>
                                                </svg>
                                                <div class="avatar size-11 avatar-label-success avatar-circle">
                                                    <i data-eva="credit-card-outline"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0 fw-medium"><span data-counter="<?= $totalUsers ?>" data-prefix=""></span> <?= $totalUsers ?></h4>
                                            <div class="dropdown">
                                                <a href="#!" class="text-muted" data-bs-toggle="dropdown" aria-label="more"><i data-eva="more-horizontal-outline" class="size-4"></i></a>
                                                <ul class="dropdown-menu dropdown-menu-animated dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#!">Today</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Week</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Month</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between mb-6">
                                            <div>
                                                <h6 class="mb-3">comments</h6>
                                                <p class="text-muted mb-0"><span class="text-danger me-1"><i data-eva="trending-down" class="size-4 me-1"></i>2.1%</span> vs last week</p>
                                            </div>
                                            <div class="position-relative d-inline-block avatar-progress progress-60">
                                                <svg class="position-absolute top-0 start-0 progress-svg">
                                                    <circle class="progress-bg"></circle>
                                                    <circle class="progress-circle stroke-warning"></circle>
                                                </svg>
                                                <div class="avatar size-11 avatar-label-warning avatar-circle">
                                                    <i data-eva="shopping-cart-outline"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0 fw-medium"><span data-counter="1245"></span> <span class="small fw-normal text-muted">/weekly</span></h4>
                                            <div class="dropdown">
                                                <a href="#!" class="text-muted" data-bs-toggle="dropdown" aria-label="more"><i data-eva="more-horizontal-outline" class="size-4"></i></a>
                                                <ul class="dropdown-menu dropdown-menu-animated dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#!">Today</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Week</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Month</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between mb-6">
                                            <div>
                                                <h6 class="mb-3">Followers</h6>
                                                <p class="text-muted mb-0"><span class="text-success me-1"><i data-eva="trending-up" class="size-4 me-1"></i>12%</span> vs last week</p>
                                            </div>
                                            <div class="position-relative d-inline-block avatar-progress progress-66">
                                                <svg class="position-absolute top-0 start-0 progress-svg">
                                                    <circle class="progress-bg"></circle>
                                                    <circle class="progress-circle stroke-danger"></circle>
                                                </svg>
                                                <div class="avatar size-11 avatar-label-danger avatar-circle">
                                                    <i data-eva="people-outline"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0 fw-medium"><span data-counter="320"></span> <span class="small fw-normal text-muted">/weekly</span></h4>
                                            <div class="dropdown">
                                                <a href="#!" class="text-muted" data-bs-toggle="dropdown" aria-label="more"><i data-eva="more-horizontal-outline" class="size-4"></i></a>
                                                <ul class="dropdown-menu dropdown-menu-animated dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#!">Today</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Week</a></li>
                                                    <li><a class="dropdown-item" href="#!">This Month</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5 class="card-title">Revenue Statistics</h5>
                                <div class="card-addon">
                                    <a href="#!" class="btn btn-secondary btn-sm"><i class="mdi mdi-download-outline me-1"></i>Download</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2 align-items-start justify-content-between">
                                    <div class="d-flex flex-wrap gap-12 align-items-center mb-6">
                                        <div>
                                            <p class="mb-2 text-muted">Total Revenue</p>
                                            <h5 class="mb-0 fw-medium">85,24k</h5>
                                        </div>
                                        <div class="d-flex gap-4 align-items-center">
                                            <div>
                                                <p class="mb-2 text-muted">Total Refunds</p>
                                                <h5 class="mb-0 fw-medium">4,125</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="nav nav-pills nav-group-tabs" id="contact-tab" role="tablist">
                                        <a class="nav-item nav-link active" id="tab-active" data-bs-toggle="tab" href="#pane-monthly" role="tab" aria-selected="false">Monthly</a>
                                        <a class="nav-item nav-link" id="tab-inactive" data-bs-toggle="tab" href="#pane-yearly" role="tab" aria-selected="false">Yearly</a>
                                        <a class="nav-item nav-link" id="tab-all" data-bs-toggle="tab" href="#pane-weekly" role="tab" aria-selected="true">Weekly</a>
                                    </div>
                                </div>
                                <div id="teamProductivityChart" data-colors='["var(--bs-success)", "var(--bs-secondary)"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7 col-xxl-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Top Selling Products</h5>
                                <div class="dropdown">
                                    <a href="#!" class="text-muted dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="more">Weekly</a>
                                    <ul class="dropdown-menu dropdown-menu-animated dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#!">Weekly</a></li>
                                        <li><a class="dropdown-item" href="#!">Monthly</a></li>
                                        <li><a class="dropdown-item" href="#!">Yearly</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Units Sold</th>
                                                <th>Revenue</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <a href="apps-product-overview.html" class="text-body fw-medium d-flex align-items-center gap-2">
                                                        <img src="assets/images/apps/ecommrece/buds.png" alt="Product" class="size-8 rounded">
                                                        <span>Wireless Earbuds</span>
                                                    </a>
                                                </td>
                                                <td>Electronics</td>
                                                <td><span class="badge badge-label-success">In Stock</span></td>
                                                <td>1,240</td>
                                                <td>$24,800</td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-label-success btn-icon" aria-label="View"><i data-eva="eye-outline"></i></button>
                                                    <button type="button" class="btn btn-sm btn-label-secondary btn-icon" aria-label="Edit"><i data-eva="edit-2-outline"></i></button>
                                                    <button type="button" class="btn btn-sm btn-label-danger btn-icon" aria-label="Delete"><i data-eva="trash-2-outline"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="apps-product-overview.html" class="text-body fw-medium d-flex align-items-center gap-2">
                                                        <img src="assets/images/apps/ecommrece/product-2.png" alt="Product" class="size-8 rounded">
                                                        <span>Smart Watch</span>
                                                    </a>
                                                </td>
                                                <td>Electronics</td>
                                                <td><span class="badge badge-label-warning">Low Stock</span></td>
                                                <td>980</td>
                                                <td>$49,000</td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-label-success btn-icon" aria-label="View"><i data-eva="eye-outline"></i></button>
                                                    <button type="button" class="btn btn-sm btn-label-secondary btn-icon" aria-label="Edit"><i data-eva="edit-2-outline"></i></button>
                                                    <button type="button" class="btn btn-sm btn-label-danger btn-icon" aria-label="Delete"><i data-eva="trash-2-outline"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="apps-product-overview.html" class="text-body fw-medium d-flex align-items-center gap-2">
                                                        <img src="assets/images/apps/ecommrece/iphone.png" alt="Product" class="size-8 rounded">
                                                        <span>iPhone 15 Pro</span>
                                                    </a>
                                                </td>
                                                <td>Electronics</td>
                                                <td><span class="badge badge-label-success">In Stock</span></td>
                                                <td>1,100</td>
                                                <td>$1,320,000</td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-label-success btn-icon" aria-label="View"><i data-eva="eye-outline"></i></button>
                                                    <button type="button" class="btn btn-sm btn-label-secondary btn-icon" aria-label="Edit"><i data-eva="edit-2-outline"></i></button>
                                                    <button type="button" class="btn btn-sm btn-label-danger btn-icon" aria-label="Delete"><i data-eva="trash-2-outline"></i></button>
                                                </td>
                                            </tr>                  
                                        </tbody>
                                    </table>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div><!-- container-fluid -->
        </div><!-- End Page-content -->

    <?php include 'includes/footer.php' ?> 

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