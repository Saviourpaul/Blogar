<?php require 'includes/header.php';


// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    // Not logged in, redirect to login
    header("Location: auth/signin.php");
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: ../ManageCategory.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: ../ManageCategory.php');
    exit();
}

$query = "SELECT * FROM categories WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($connection, $query);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($connection));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    header('Location: ../ManageCategory.php');
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <title>Add Patient | Aquiry Admin &amp; Dashboard Template </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Admin & Dashboard Template" name="description">
    <meta content="Codebucks" name="author">

    <!-- layout setup -->
    <!-- <script type="module" src="assets/js/layout-setup.js"></script> -->

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo-sm.png">
    <!-- select2 -->
    <link href="assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css">

    <!-- Bootstrap Datepicker -->
    <link href="assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

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
       
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->

                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">category  Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">

                                       
                                        <form action="controller/edit-category-logic.php" 
                                            method="POST">
                                            <div class="col-lg-8">
                                                <div class="row g-4">
                                                    <input type="hidden" name="id" value="<?= $category['id'] ?> " >
                                                    <div class="col-md-6">
                                                        <label for="title" class="form-label">Title</label>
                                                        <input type="text" name="title" value="<?= $category['title'] ?>"
                                                            class="form-control" placeholder="Title">
                                                    </div>
                                                    <div class="mt-4">
                                                    <textarea class="form-control" id="BAddress" rows="3" name="description" placeholder="Description" style="height: 150px; width: 30rem;"> <?= $category['description'] ?></textarea>
                                                </div>
                                                   
                                                    <div class="col-md-6">
                                                        <button type="submit" name="submit"
                                                            class="btn btn-secondary">Update 
                                                            Category</button>
                                                    </div>
                                        </form>
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
                                Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="http://codebucks.in/"
                                    target="_blank" class="text-muted">Codebucks</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- END Footer -->
            <!-- Begin scroll top -->

            <!-- END scroll top -->
        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->

    <?php if (isset($_SESSION['edit-category'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: " Failed",
                    text: "<?= $_SESSION['edit-category'] ?>",
                    confirmButtonColor: "#d33"
                });

            });

        </script>

        <?php unset($_SESSION['edit-category']); ?>
        
       
    <?php endif;   ?>

    <?php if (isset($_SESSION['edit-category-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: " success",
                    text: "<?= $_SESSION['edit-category-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['edit-category-success']); ?>
        
       
    <?php endif;   ?>

    
    


    <script src="../assets/js/sweetalert.js"></script>



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
    <!-- Bootstrap datepicker -->
    <script src="assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <!-- select2 -->
    <script src="assets/libs/select2/js/select2.min.js"></script>

    <!-- Init js -->
    <script src="assets/js/apps/add-patient-init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>



</html>