<?php $pageTitle = 'ManagePost';
require 'includes/header.php';


// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    // Not logged in, redirect to login
    header("Location: auth/signin.php");
    exit();
}
$current_user_id = $_SESSION['user-id'];
$query = "SELECT * FROM posts WHERE author_id=$current_user_id ORDER BY id DESC";
$posts = mysqli_query($connection, $query);




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
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Manage post</h4>
                                <nav aria-label="breadcrumb" class="page-title-right">

                                </nav>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->


                    <div class="card">
                        <div class="card-header flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap flex-lg-nowrap">
                                <form class="app-search">
                                    <div class="position-relative">
                                        <input type="text" class="form-control bg-body-tertiary min-w-60"
                                            placeholder="Search Patient...">
                                        <i data-eva="search-outline" class="align-middle"></i>
                                    </div>
                                </form>
                                <div class="dropdown">
                                    <button type="button" class="btn bg-body-tertiary dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i data-eva="options-2-outline" class="size-4"></i> Filter
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-animated dropdown-menu-md-end">
                                        <a class="dropdown-item" href="#!">Date: Newest First</a>
                                        <a class="dropdown-item" href="#!">Date: Oldest First</a>
                                        <a class="dropdown-item" href="#!">Customer Name A-Z</a>
                                        <a class="dropdown-item" href="#!">Customer Name Z-A</a>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <div class="dropdown">
                                    <button type="button" class="btn bg-body-tertiary dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        All Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-animated dropdown-menu-md-end">
                                        <li><a class="dropdown-item" href="#!">Active</a></li>
                                        <li><a class="dropdown-item" href="#!">Inactive</a></li>
                                        <li><a class="dropdown-item" href="#!">Pending</a></li>
                                        <li><a class="dropdown-item" href="#!">Completed</a></li>
                                        <li><a class="dropdown-item" href="#!">Archived</a></li>
                                    </ul>
                                </div>
                                <a href="CreatePost.php" class="btn btn-secondary"><i data-eva="plus-circle-outline"
                                        class="size-4"></i> New Post</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <?php if (mysqli_num_rows(result: $posts) > 0): ?>
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>category</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($post = mysqli_fetch_assoc($posts)): ?>

                                                <?php $category_id = $post['category_id'];
                                                $query = "SELECT * FROM categories WHERE id=$category_id";
                                                $category = mysqli_query($connection, $query);
                                                $category = mysqli_fetch_assoc($category);
                                                ?>
                                                <tr>

                                                    <td><?= $post['title'] ?></td>
                                                    <td><?= $category['title'] ?></td>
                                                    <td>
                                                        <a href="UpdatePost.php?id=<?= $post['id'] ?>"
                                                            class="btn sm"><button type="button"
                                                                class="btn btn-sm btn-label-primary btn-icon"><i
                                                                    data-eva="edit-2-outline"></i></button>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="controller/delete-post.php?id=<?= $post['id'] ?>"
                                                            class="btn sm"><button type="button"
                                                                class="btn btn-sm btn-label-danger btn-icon"><i
                                                                    data-eva="trash-2-outline"></i></button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="alert_message error">
                                        <p>No post found.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex align-items-center gap-4 justify-content-between mt-3 flex-wrap">
                                <div>
                                    <p class="mb-0 text-muted">Showing <span class="fw-semibold text-body">1</span> -
                                        <span class="fw-semibold text-body">10</span> of <span
                                            class="fw-semibold text-body">50</span> Results
                                    </p>
                                </div>
                                <ul class="pagination pagination-arrow mb-0 ms-auto">
                                    <li class="page-item"><a class="page-link page-prev" href="#!"><i
                                                class="mdi mdi-chevron-left align-middle pagination-left"></i><i
                                                class="mdi mdi-chevron-right align-middle pagination-right"></i><span
                                                class="visually-hidden">Previous</span></a></li>
                                    <li class="page-item"><a class="page-link active" href="#!">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#!">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#!">3</a></li>
                                    <li class="page-item"><a class="page-link" href="#!">4</a></li>
                                    <li class="page-item"><a class="page-link page-next" href="#!"><i
                                                class="mdi mdi-chevron-right pagination-right align-middle"></i><i
                                                class="mdi mdi-chevron-left pagination-left align-middle"></i><span
                                                class="visually-hidden">Next</span></a></li>
                                </ul>
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


    <?php if (isset($_SESSION['add-post-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: "success",
                    text: "<?= $_SESSION['add-post-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['add-post-success']); ?>

    <?php endif; ?>



    <?php if (isset($_SESSION['delete-post-success'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then(function (result) {

                    if (result.isConfirmed) {

                        Swal.fire(
                            "Deleted!",
                            "<?= $_SESSION['delete-post-success']; ?> ",
                            "success"
                        )

                            .then(function () {

                                window.location.href = deleteUrl;

                            });

                    }

                });

            });



        </script>

        
        <?php unset($_SESSION['delete-post-success']); ?>
    <?php endif; ?>

    
     <?php if (isset($_SESSION['edit-post-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: " success",
                    text: "<?= $_SESSION['edit-post-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['edit-post-success']); ?>
        
       
    <?php endif;   ?>

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
    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>



</html>