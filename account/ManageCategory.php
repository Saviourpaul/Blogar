<?php $pageTitle = 'ManageCategory';
require 'includes/header.php';



if (!isset($_SESSION['user-id'])) {
    header("Location: signin");
    exit();
}

$stmt = "SELECT * FROM categories ORDER BY title ASC";
$categories = mysqli_query($connection, $stmt);

if (!$categories) {
    die(mysqli_error($connection));
}

$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1)
    $current_page = 1;

$offset = ($current_page - 1) * $items_per_page;

$total_query = mysqli_query($connection, "SELECT COUNT(*) as total FROM categories");
$total_rows = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_rows / $items_per_page);

$query = "SELECT * FROM categories ORDER BY title ASC LIMIT $items_per_page OFFSET $offset";
$categories = mysqli_query($connection, $query);

$start_item = $offset + 1;
$end_item = min($offset + $items_per_page, $total_rows);
if ($total_rows == 0)
    $start_item = 0;


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
                                <a href="addCategory" class="btn btn-secondary"><i data-eva="plus-circle-outline"
                                        class="size-4"></i> Add Category </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <?php if (mysqli_num_rows(result: $categories) > 0): ?>
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Edit</th>
                                                <th>Delete</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($category = mysqli_fetch_assoc($categories)): ?>

                                                <tr>
                                                    <td><?= $category['title'] ?></a></td>

                                                    <td>
                                                        <a href="EditCategory?id=<?= $category['id'] ?>"
                                                            class="btn btn-sm btn-label-primary btn-icon"><i
                                                                data-eva="edit-2-outline"></i></a>

                                                    </td>
                                                    <td>

                                                        <a href="delete-category?id=<?= $category['id'] ?>"
                                                            class="btn btn-sm btn-label-danger btn-icon delete-category-btn"><i
                                                                data-eva="trash-2-outline"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endwhile ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="alert_message error"><?= "No categories found" ?></div>
                                <?php endif ?>
                            </div>

                            <?php if ($total_pages > 1): ?>
                                <div class="text-center mt-4 mb-5">
                                    <ul class="pagination justify-content-center pagination-rounded">

                                        <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                            <a href="<?= ($current_page <= 1) ? 'javascript:void(0);' : '?page=' . ($current_page - 1) ?>"
                                                class="page-link">
                                                <i class="mdi mdi-chevron-left"></i>
                                            </a>
                                        </li>

                                        <?php
                                        $visible_pages = 2;

                                        for ($i = 1; $i <= $total_pages; $i++):
                                            if ($i == 1 || $i == $total_pages || ($i >= $current_page - $visible_pages && $i <= $current_page + $visible_pages)):
                                                ?>
                                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                                    <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                                                </li>
                                            <?php
                                            elseif ($i == $current_page - $visible_pages - 1 || $i == $current_page + $visible_pages + 1):
                                                ?>
                                                <li class="page-item disabled">
                                                    <a href="javascript:void(0);" class="page-link">...</a>
                                                </li>
                                            <?php
                                            endif;
                                        endfor;
                                        ?>

                                        <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a href="<?= ($current_page >= $total_pages) ? 'javascript:void(0);' : '?page=' . ($current_page + 1) ?>"
                                                class="page-link">
                                                <i class="mdi mdi-chevron-right"></i>
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            <?php endif; ?>
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


    <script>

        document.querySelectorAll(".delete-category-btn").forEach(function (button) {

            button.addEventListener("click", function (e) {

                e.preventDefault();

                let deleteUrl = this.getAttribute("href");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                })

                    .then(function (result) {

                        if (result.isConfirmed) {

                            Swal.fire(
                                "Deleted!",
                                "category has been deleted successfully.",
                                "success"
                            )

                                .then(function () {

                                    window.location.href = deleteUrl;

                                });

                        }

                    });

            });

        });

    </script>
    <?php if (isset($_SESSION['add-category-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: " success",
                    text: "<?= $_SESSION['add-category-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['add-category-success']); ?>


    <?php endif; ?>

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