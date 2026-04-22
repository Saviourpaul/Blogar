<?php $pageTitle = 'ManagePost';
require 'includes/header.php';
require_once 'includes/helpers.php';

ensurePostMediaSchema($connection);


if (!isset($_SESSION['user-id'])) {
    header("Location: signin");
    exit();
}

$postSettingResult = mysqli_query($connection, "SELECT enable_post FROM settings WHERE id = 1 LIMIT 1");
$is_create_post_enabled = true;

if ($postSettingResult && mysqli_num_rows($postSettingResult) > 0) {
    $postSetting = mysqli_fetch_assoc($postSettingResult);
    $is_create_post_enabled = !isset($postSetting['enable_post']) || (int) $postSetting['enable_post'] === 1;
}

$is_delete_post_enabled = isSettingEnabled($connection, 'enable_delete_post', false);

$current_user_id = $_SESSION['user-id'];
$query = "SELECT * FROM posts WHERE author_id=$current_user_id ORDER BY id DESC";
$posts = mysqli_query($connection, $query);


$current_user_id = isset($_SESSION['user-id']) ? (int)$_SESSION['user-id'] : 0; 
$is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;

$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($current_page < 1) {
    $current_page = 1;
}

$offset = ($current_page - 1) * $items_per_page;

$where_clause = "";
if (!$is_admin) {
    $where_clause = "WHERE posts.author_id = $current_user_id"; 
}

$total_query = "SELECT COUNT(posts.id) as total FROM posts $where_clause";
$total_result = mysqli_query($connection, $total_query);

if (!$total_result) {
    die("Error in Total Query: " . mysqli_error($connection));
}

$total_rows_data = mysqli_fetch_assoc($total_result);
$total_rows = $total_rows_data['total'];
$total_pages = ceil($total_rows / $items_per_page);

$query = "
    SELECT posts.*, categories.title AS category_title 
    FROM posts 
    LEFT JOIN categories ON posts.category_id = categories.id 
    $where_clause
    ORDER BY posts.id DESC 
    LIMIT $items_per_page OFFSET $offset
";

$posts = mysqli_query($connection, $query);

if (!$posts) {
    die("Error in Main Query: " . mysqli_error($connection));
}

$start_item = $offset + 1;
$end_item = min($offset + $items_per_page, $total_rows);
if ($total_rows == 0) {
    $start_item = 0;
}
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
                                <?php if ($is_create_post_enabled): ?>
                                    <a href="CreatePost" class="btn btn-secondary"><i data-eva="plus-circle-outline"
                                            class="size-4"></i> New Post</a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary" disabled title="Create post is disabled in settings">
                                        <i data-eva="plus-circle-outline" class="size-4"></i> New Post Disabled
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <?php if (mysqli_num_rows($posts) > 0): ?>
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Format</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($post = mysqli_fetch_assoc($posts)): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($post['title']) ?></td>
                                                    <td><?= htmlspecialchars($post['category_title']) ?></td>
                                                    <td>
                                                        <?php $managePostMedia = getPostMediaDetails($post); ?>
                                                        <span class="badge rounded-pill bg-light text-dark border">
                                                            <?= htmlspecialchars($managePostMedia['is_video'] ? $managePostMedia['video_provider_label'] : 'Image', ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="UpdatePost?id=<?= $post['id'] ?>" class="btn sm">
                                                            <button type="button" class="btn btn-sm btn-label-primary btn-icon">
                                                                <i data-eva="edit-2-outline"></i>
                                                            </button>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if ($is_delete_post_enabled): ?>
                                                            <a href="delete-post?id=<?= $post['id'] ?>" class="btn sm">
                                                                <button type="button" class="btn btn-sm btn-label-danger btn-icon">
                                                                    <i data-eva="trash-2-outline"></i>
                                                                </button>
                                                            </a>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-label-danger btn-icon" disabled
                                                                title="Delete post is disabled in settings">
                                                                <i data-eva="trash-2-outline"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile ?>
                                        </tbody>
                                    </table>

                                    <div class="d-flex align-items-center gap-4 justify-content-between mt-4 flex-wrap">
                                        <div>
                                            <p class="mb-0 text-muted">Showing
                                                <span class="fw-semibold text-body"><?= $start_item ?></span> -
                                                <span class="fw-semibold text-body"><?= $end_item ?></span> 
                                            </p>
                                        </div>

                                        <ul class="pagination pagination-arrow mb-0 ms-auto">
                                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link page-prev" href="?page=<?= $current_page - 1 ?>">
                                                    <i class="mdi mdi-chevron-left align-middle pagination-left"></i>
                                                    <span class="visually-hidden">Previous</span>
                                                </a>
                                            </li>

                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                                <a class="page-link page-next" href="?page=<?= $current_page + 1 ?>">
                                                    <i class="mdi mdi-chevron-right pagination-right align-middle"></i>
                                                    <span class="visually-hidden">Next</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                <?php else: ?>
                                    <div class="alert_message error mt-3">
                                        <p>No post found.</p>
                                    </div>
                                <?php endif; ?>
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

    <?php if (isset($_SESSION['add-post'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: "Failed",
                    text: "<?= $_SESSION['add-post'] ?>",
                    confirmButtonColor: "#d33"
                });

            });

        </script>

        <?php unset($_SESSION['add-post']); ?>

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


    <?php endif; ?>

    <?php if (isset($_SESSION['delete-post'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: "Failed",
                    text: "<?= $_SESSION['delete-post'] ?>",
                    confirmButtonColor: "#d33"
                });

            });

        </script>

        <?php unset($_SESSION['delete-post']); ?>

    <?php endif; ?>

    <script src="account/assets/js/sweetalert.js"></script>

    <!-- Bootstrap bundle js -->
    <script src="account/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Layouts main js -->
    <script src="account/assets/libs/jquery/jquery.min.js"></script>

    <!-- Metimenu js -->
    <script src="account/assets/libs/metismenu/metisMenu.min.js"></script>

    <!-- simplebar js -->
    <script src="account/assets/libs/simplebar/simplebar.min.js"></script>

    <script src="account/assets/libs/eva-icons/eva.min.js"></script>

    <!-- Scroll Top init -->
    <script src="account/assets/js/scroll-top.init.js"></script>
    <!-- App js -->
    <script src="account/assets/js/app.js"></script>

</body>



</html>
