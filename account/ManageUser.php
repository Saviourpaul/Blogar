<?php $pageTitle ='ManageUser';
require 'includes/header.php';


if (!isset($_SESSION['user-id'])) {
    header("Location: signin");
    exit();
}




$current_admin_id = $_SESSION['user-id'];
$query = "SELECT * FROM users WHERE NOT id= $current_admin_id ";
$users = mysqli_query($connection, $query);




$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$offset = ($current_page - 1) * $items_per_page;

$total_query = mysqli_query($connection, "SELECT COUNT(*) as total FROM users");
$total_rows = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_rows / $items_per_page);

$query = "SELECT * FROM users ORDER BY id DESC LIMIT $items_per_page OFFSET $offset";
$users = mysqli_query($connection, $query);

$start_item = $offset + 1;
$end_item = min($offset + $items_per_page, $total_rows);
if ($total_rows == 0) $start_item = 0;
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


                    <div class="card">
                        <div class="card-header flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap flex-lg-nowrap">
                                <form class="app-search">
                                    <div class="position-relative">
                                        <input type="text" class="form-control bg-body-tertiary min-w-60"
                                            placeholder="Search user...">
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
                                <a href="AddUser.php" class="btn btn-secondary"><i data-eva="plus-circle-outline"
                                        class="size-4"></i> New User</a>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            <div class="table-responsive text-nowrap">
                                 <?php if (mysqli_num_rows(result: $users) > 0): ?>
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>

                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>View</th>
                                            <th>Delete</th>
                                            <th>Admin</th>


                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($users)): ?>


                                            <tr>
                                                <td>
                                                    <a 
                                                        class="d-flex align-items-center gap-2 text-body">
                                                        <span class="avatar avatar-sm avatar-circle overflow-hidden">
                                                            <img src="account/uploads/<?= $user['avatar'] ?>" alt="Avatar Image"
                                                                class="size-7">
                                                        </span>
                                                        <p class="fw-semibold mb-0">
                                                            <?= $user['firstname'] . ' ' . $user['lastname'] ?>
                                                        </p>
                                                    </a>
                                                </td>

                                                <td><?= $user['username'] ?></td>
                                                <td>
                                                     
                                                    <a href="UserProfile?id=<?= $user['id'] ?>"
                                                    class="btn sm"><button id="blockui-trigger-10" type="button"
                                                        class="btn btn-sm btn-secondary"
                                                          class="btn btn-sm-label-view btn-icon">
                                                        <i class="sm bi bi-eye"></i>
                                                        </button>
                                                    </a>
                                                </td>
                                                

                                                <td>
                                                    <a href="delete-user?id=<?= $user['id'] ?>"
                                                        class="btn sm"><button type="button"
                                                            class="btn btn-sm btn-label-danger btn-icon "><i
                                                                data-eva="trash-2-outline"></i></button>
                                                    </a>
                                                </td>
                                                <td><?= $user['is_admin'] ? 'Yes' : 'No' ?>

                                                </td>
                                            </tr>
                                        <?php endwhile ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <div class="alert_message error container">
                                    <p>No users found</p>
                                </div>
                            <?php endif ?>

                                

                            </div>

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
  
    <?php if (isset($_SESSION['add-user-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: " success",
                    text: "<?= $_SESSION['add-user-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['add-user-success']); ?>


    <?php endif; ?>



<?php if (isset($_SESSION['delete-user-error'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function(){

    Swal.fire({
        icon: "error",
        title: "Error",
        text: "<?= $_SESSION['delete-user-error']; ?>"
    });

});
</script>
<?php unset($_SESSION['delete-user-error']); ?>
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