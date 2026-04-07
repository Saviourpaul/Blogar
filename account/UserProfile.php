
<?php $pageTitle = 'UserProfile';
  require 'includes/header.php';






$id= filter_var($_GET['id'], FILTER_VALIDATE_INT);

if (!$id) {
    die("Invalid user ID");
}

$stmt = $connection->prepare("
    SELECT id, username, email, firstname, lastname, phone, bio, gender, birthday, address1, address2, country, zip_code, avatar 
    FROM users WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");

}


$user = $result->fetch_assoc();



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
                
                <!-- end page title -->
                 
                <div class="row">
                    <div class="col-xl-6 col-xxl-5">
                        <div class="card sticky-top">
                            <div class="card-body p-0">
                                <div class="row align-items-center">
                                    <div class="col-md-5 border-end">
                                        <div class="text-center p-6">
                                            <div class="avatar avatar-xl avatar-circle overflow-hidden mb-3 hei">
                                                 <img src="./uploads/<?= $user['avatar'] ?>" alt="User Avatar" class="avatar-img" height="100">
                                            </div>
                                            <h6 class="mb-2 fs-15"><?= htmlspecialchars($user['firstname'] ?? '') ?> <?= htmlspecialchars($user['lastname'] ?? '') ?></h6>
                                            <p class="text-muted small mb-5"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                            <p class="fw-semibold mb-5"><?= htmlspecialchars($user['bio'] ?? '') ?></p>
                                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                                            <p></p>
                                                                          
                                            </div>
                                            
                                    </div>
                                    <div class="col-md-7">
                                        <div class="p-6">
                                            <div class="row gy-4 gx-6">
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Gender</p>
                                                    <p class="fs-14 fw-semibold mb-4"> <?= htmlspecialchars($user['gender'] ?? '-') ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Date of Birth</p>
                                                    <p class="fs-14 fw-semibold mb-4"> <?= htmlspecialchars($user['birthday'] ?? '-') ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Phone</p>
                                                    <p class="fs-14 fw-semibold mb-4"> <?= htmlspecialchars($user['phone'] ?? '-') ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">bio</p>
                                                    <p class="fs-14 fw-semibold mb-4"> <?= htmlspecialchars($user['bio'] ?? '-') ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Address</p>
                                                    <p class="fs-14 fw-semibold mb-4"> <?= htmlspecialchars($user['address1'] ?? '-') ?> <?= htmlspecialchars($user['address2'] ?? '-') ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Country</p>
                                                    <p class="fs-14 fw-semibold mb-4"> <?= htmlspecialchars($user['country'] ?? '-') ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">ZIP Code</p>
                                                    <p class="fs-14 fw-semibold mb-4"> <?= htmlspecialchars($user['zip_code'] ?? '-') ?></p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted mb-3">Status</p>
                                                    <p class="fs-14 fw-semibold mb-0"> <?= htmlspecialchars($user['status'] ?? '-') ?></p>
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
                <?php include 'includes/footer.php' ?> 

        <!-- END scroll top -->
    </div><!-- end main content-->

</div>
<!-- END layout-wrapper -->
<?php if (isset($_SESSION['update-user-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "<?= $_SESSION['update-user-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['update-user-success']); ?>
        

    <?php endif; ?>
    <script src="assets/js/sweetalert.js"></script>


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