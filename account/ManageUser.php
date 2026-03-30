<?php require 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    // Not logged in, redirect to login
    header("Location: auth/signin.php");
    exit();
}




$current_admin_id = $_SESSION['user-id'];
$query = "SELECT * FROM users WHERE NOT id= $current_admin_id ";
$users = mysqli_query($connection, $query);






?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <title>Patient list | Aquiry Admin &amp; Dashboard Template </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Admin & Dashboard Template" name="description">
    <meta content="Codebucks" name="author">

    <!-- layout setup -->
    <!-- <script type="module" src="assets/js/layout-setup.js"></script> -->

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo-sm.png">
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
                                            <th>Edit</th>
                                            <th>Delete</th>
                                            <th>Admin</th>


                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($users)): ?>


                                            <tr>
                                                <td>
                                                    <a href="UserProfile.php?id=<?= $user['id'] ?>"
                                                        class="d-flex align-items-center gap-2 text-body">
                                                        <span class="avatar avatar-sm avatar-circle overflow-hidden">
                                                            <img src="./uploads/<?= $user['avatar'] ?>" alt="Avatar Image"
                                                                class="size-7">
                                                        </span>
                                                        <p class="fw-semibold mb-0">
                                                            <?= $user['firstname'] . ' ' . $user['lastname'] ?>
                                                        </p>
                                                    </a>
                                                </td>

                                                <td><?= $user['username'] ?></td>
                                                <td>
                                                    <button 
                                                        class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal"
                                                        data-id="<?= $user['id'] ?>"
                                                        data-firstname="<?= htmlspecialchars($user['firstname']) ?>"
                                                        data-lastname="<?= htmlspecialchars($user['lastname']) ?>"
                                                        data-username="<?= htmlspecialchars($user['username']) ?>"
                                                        data-is_admin="<?= htmlspecialchars($user['is_admin']) ?>">

                                                        <i class="sm bi bi-pencil"></i>
                                                        </button>
                                                </td>

                                                <td>
                                                    <a href="controller/delete-user.php?id=<?= $user['id'] ?>"
                                                        class="btn sm"><button type="button"
                                                            class="btn btn-sm btn-label-danger btn-icon "><i
                                                                data-eva="edit-2-outline"></i></button>
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
            <div class="modal fade" id="editUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <form action="controller/update-user.php" enctype="multipart/form-data" method="POST">

                            <div class="modal-body">

                                <input type="hidden"  name="id" >

                                <div class="mb-3">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" id="edit-firstname" name="firstname">
                                </div>

                                <div class="mb-3">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" id="edit-lastname" name="lastname">

                                </div>

                                <div class="mb-3">
                                    <label>Username</label>
                                    <input type="text" class="form-control" id="edit-username" name="username">

                                </div>
                                <div class="md-3">
                                    <label for="role" class="form-label">Role *</label>
                                    <select name="userrole" id="edit-role class="form-select">
                                        <option selected disabled>Select Role</option>
                                        <option value="0" >Author</option>
                                        <option value="1">Admin</option>
                                    </select>
                                </div>


                            </div>

                            <div class="modal-footer">
                                <button type="submit" name="submit" class="btn btn-primary">Update</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

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
    <script>
document.addEventListener("DOMContentLoaded", function(){

    const modal = document.getElementById('editUserModal');

    modal.addEventListener('show.bs.modal', function(event){

        const button = event.relatedTarget;

        // Extract data from button
        const id = button.getAttribute('data-id');
        const firstname = button.getAttribute('data-firstname');
        const lastname = button.getAttribute('data-lastname');
        const username = button.getAttribute('data-username');
        const role = button.getAttribute('data-is_admin');

        // Inject into modal
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-firstname').value = firstname;
        document.getElementById('edit-lastname').value = lastname;
        document.getElementById('edit-username').value = username;
        document.getElementById('edit-role').value = role;

    });

});
</script>
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