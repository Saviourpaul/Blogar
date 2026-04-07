<?php $pageTitle = 'Edit Post';
require 'includes/header.php';

 //fetch categories from database
 $category_query = "SELECT * FROM categories";
 $categories = mysqli_query($connection, $category_query);

 if(isset($_GET['id'])){
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $query = "SELECT * FROM posts WHERE id=$id";
    $post = mysqli_query($connection, $query);
    $post = mysqli_fetch_assoc($post);
 }else{
    header('location: ../managePost.php');
    die();
}

$id = $_GET['id'];
$query = "SELECT * FROM posts WHERE id=$id LIMIT 1";
$result = mysqli_query($connection, $query);
$post = mysqli_fetch_assoc($result);
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
       
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->

                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">post  Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">

                                       
                                         <form action="controller/UpdatePost-logic.php" enctype="multipart/form-data"
                                            method="POST">
                                            <div class="col-lg-8">
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                         <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                                         <input type="hidden" name="previous_thumbnail_name" value="<?= $post['thumbnail'] ?>">
           
                                                        <label for="title" class="form-label">Title </label>
                                                        <input type="text" name="title" value="<?=$post['title'] ?>"
                                                            class="form-control" placeholder="Enter Title">
                                                    </div>
                                                   <textarea class="form-control" id="BAddress" rows="3" name="body" placeholder="Description" style="height: 150px; width: 30rem;"><?=$post['body'] ?></textarea>

                                                   
                                                   <div class="col-md-6">
                                                        <label for="role" class="form-label">Category *</label>
                                                        <select name="category" id="category" class="form-select" required>
                                                            <option selected disabled>Select Category</option>
                                                            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                                            
                                                            <option value="<?= $category['id'] ?>"><?= $category['title'] ?></option>
                                                             <?php endwhile ?>
                                                        </select>
                                                    </div>
                                                    
                                                <div class="md-4">
                                                     <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                                        
                                                            <input type="checkbox" name="is_featured" value="1" id="is_featured" checked>
                                                            <label for="is_featured">featured</label>
                                                        </div>
                                                    <?php endif ?>
                                                    

                                                    <div class="col-md-6">
                                                        <label for="addPatient"
                                                            class="form-label border h-28 d-flex justify-content-center align-items-center flex-column gap-1 bg-body-tertiary rounded-2 cursor-pointer text-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24"
                                                                class="eva eva-cloud-upload-outline">
                                                                <g data-name="Layer 2">
                                                                    <g data-name="cloud-upload">
                                                                        <rect width="24" height="24" opacity="0"></rect>
                                                                        <path
                                                                            d="M12.71 11.29a1 1 0 0 0-1.4 0l-3 2.9a1 1 0 1 0 1.38 1.44L11 14.36V20a1 1 0 0 0 2 0v-5.59l1.29 1.3a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42z">
                                                                        </path>
                                                                        <path
                                                                            d="M17.67 7A6 6 0 0 0 6.33 7a5 5 0 0 0-3.08 8.27A1 1 0 1 0 4.75 14 3 3 0 0 1 7 9h.1a1 1 0 0 0 1-.8 4 4 0 0 1 7.84 0 1 1 0 0 0 1 .8H17a3 3 0 0 1 2.25 5 1 1 0 0 0 .09 1.42 1 1 0 0 0 .66.25 1 1 0 0 0 .75-.34A5 5 0 0 0 17.67 7z">
                                                                        </path>
                                                                    </g>
                                                                </g>
                                                            </svg>
                                                            <h6>Change your Image</h6>
                                                            <input type="file"  name="thumbnail"  class="d-none" id="addPatient"
                                                               >
                                                        </label>
                                                    </div>
                                                </div>
                                                <br>
                                                    
                                                    <div class="md-4">
                                                        <button type="submit" name="submit"
                                                            class="btn btn-secondary">update
                                                            Post</button>
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
                   <?php include 'includes/footer.php' ?> 

            <!-- END Footer -->
            <!-- Begin scroll top -->

            <!-- END scroll top -->
        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->

    <?php if (isset($_SESSION['edit-post'])): ?>

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

        <?php unset($_SESSION['edit-post']); ?>
        
       
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