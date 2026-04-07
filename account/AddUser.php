<?php $pageTitle = 'AddUser';
require 'includes/header.php';



// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    // Not logged in, redirect to login
    header("Location: auth/signin.php");
    exit();
}

//get back form data if there was  error
$firstname = $_SESSION['add-user-data']['firstname'] ?? null;
$lastname = $_SESSION['add-user-data']['lastname'] ?? null;
$username = $_SESSION['add-user-data']['username'] ?? null;
$email = $_SESSION['add-user-data']['email'] ?? null;
$create_password = $_SESSION['add-user-data']['create_password'] ?? null;
$confirm_password = $_SESSION['add-user-data']['confirm_password'] ?? null;

unset($_SESSION['add-user-data']);
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
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Personal Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">

                                       
                                        <form action="controller/add-user-logic.php" enctype="multipart/form-data"
                                            method="POST">
                                            <div class="col-lg-8">
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <label for="FirstName" class="form-label">First Name</label>
                                                        <input type="text" name="firstname" value="<?= $firstname ?>"
                                                            class="form-control" placeholder="Enter First Name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="LastName" class="form-label">Last Name</label>
                                                        <input type="text" name="lastname" value="<?= $lastname ?>"
                                                            class="form-control" placeholder="Enter Last Name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="Username" class="form-label">UserName </label>
                                                        <input type="text" name="username" value="<?= $username ?>"
                                                            class="form-control" placeholder="Enter First Name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="email" class="form-label">Email</label>
                                                        <input type="email" name="email" value="<?= $email ?>"
                                                            class="form-control" placeholder="Enter Email" id="email">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="password" class="form-label">Password</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i
                                                                    class="bi bi-lock"></i></span>
                                                            <input type="password" class="form-control"
                                                                id="confirm_password" name="create_password"
                                                                value="<?= $create_password ?>"
                                                                placeholder="Enter password">
                                                            <button class="btn btn-outline-secondary" type="button"
                                                                id="togglePasswords">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                        <div class="col-md-6">
                                                        <label for="password" class="form-label">confirm Password</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                            <input type="password" class="form-control" id="password" name="confirm_password" value="<?=  $create_password ?>"
                                                                placeholder="Enter password" >
                                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="role" class="form-label">Role *</label>
                                                        <select name="userrole" id="role" class="form-select" required>
                                                            <option selected disabled>Select Role</option>
                                                            <option value="0">Author</option>
                                                            <option value="1">Admin</option>
                                                        </select>
                                                    </div>
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
                                                            <h6>Upload your Image</h6>
                                                            <input type="file" class="d-none" id="addPatient"
                                                                name="avatar">
                                                        </label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <button type="submit" name="submit"
                                                            class="btn btn-secondary">Add
                                                            User</button>
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

        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->

    <?php if (isset($_SESSION['add-user'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: " Failed",
                    text: "<?= $_SESSION['add-user'] ?>",
                    confirmButtonColor: "#d33"
                });

            });

        </script>

        <?php unset($_SESSION['add-user']); ?>
        

    <?php endif; ?>

    

    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const icon = toggle.querySelector('i');

            toggle.addEventListener('click', () => {
                const isHidden = password.type === 'password';
                password.type = isHidden ? 'text' : 'password';
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        });
    </script>

    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('togglePasswords');
            const password = document.getElementById('confirm_password');
            const icon = toggle.querySelector('i');

            toggle.addEventListener('click', () => {
                const isHidden = password.type === 'password';
                password.type = isHidden ? 'text' : 'password';
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        });
    </script>
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