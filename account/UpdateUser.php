    <?php $pageTitle = 'UpdateUser';
    include 'includes/header.php'; 


$id = $_SESSION['user-id'] ?? null; 

if ($id) {
    $stmt = $connection->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    
    $stmt->bind_param("i", $id);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }
    
    $stmt->close();
} else {
    die("Unauthorized access.");
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

                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Profile setting</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">

                                       
                                        <form action="update-user-logic" enctype="multipart/form-data"
                                            method="POST">
                                            <div class="col-lg-8">
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                        <label for="FirstName" class="form-label">First Name</label>
                                                        <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>"
                                                            class="form-control" placeholder="Enter First Name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="LastName" class="form-label">Last Name</label>
                                                        <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>"
                                                            class="form-control" placeholder="Enter Last Name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="Username" class="form-label">UserName </label>
                                                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                                                            class="form-control" placeholder="Enter First Name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="email" class="form-label">Email</label>
                                                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                                                            class="form-control" placeholder="Enter Email" id="email" readonly>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="birthday" class="form-label">Birthday</label>
                                                        <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday']) ?>"
                                                            class="form-control">
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <label for="phone" class="form-label">Phone</label>
                                                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                                                            class="form-control" placeholder="Enter Phone Number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="bio" class="form-label">Bio</label>
                                                        <textarea name="bio" class="form-control" placeholder="Enter Bio"><?= htmlspecialchars($user['bio']) ?></textarea>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <label for="address1" class="form-label">Address 1</label>
                                                        <input type="text" name="address1" value="<?= htmlspecialchars($user['address1']) ?>"
                                                            class="form-control" placeholder="Enter Address">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="address2" class="form-label">Address 2</label>
                                                        <input type="text" name="address2" value="<?= htmlspecialchars($user['address2']) ?>"
                                                            class="form-control" placeholder="Enter Address">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="zip_code" class="form-label">Zip Code</label>
                                                        <input type="text" name="zip_code" value="<?= htmlspecialchars($user['zip_code']) ?>"
                                                            class="form-control" placeholder="Enter Zip Code">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="country" class="form-label">Country</label>
                                                        <input type="text" name="country" value="<?= htmlspecialchars($user['country']) ?>"
                                                            class="form-control" placeholder="Enter Country">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="role" class="form-label">Gender *</label>
                                                        <select name="gender" id="role" class="form-select" required>
                                                            <option selected disabled>Select Gender</option>
                                                            <option value="0" <?= $user['gender'] === '0' ? 'selected' : '' ?>>Male</option>
                                                            <option value="1" <?= $user['gender'] === '1' ? 'selected' : '' ?>>Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="avatar" class="form-label">Avatar</label>
                                                        <input type="file" name="avatar" class="form-control">
                                                    </div> 
                                                    <div class="col-md-6">
                                                        <label for="current_password" class="form-label">Current Password</label>
                                                        <input type="password" name="current_password" id="password" class="form-control" placeholder="Enter Current Password">
                                                        <span id="togglePassword" style="cursor: pointer; position: absolute; right: 10px; top: 35px;">
                                                            <i class="bi bi-eye"></i>
                                                        </span>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="new_password" class="form-label">New Password</label>
                                                        <input type="password" name="create_password" id="create_password" class="form-control" placeholder="Enter New Password">
                                                        <span id="togglePasswords" style="cursor: pointer; position: absolute; right: 10px; top: 35px;">
                                                            <i class="bi bi-eye"></i>
                                                        </span>
                                                    </div> 
                                                    <div class="col-md-6">
                                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm New Password">
                                                        <span id="toggleConfirmPassword" style="cursor: pointer; position: absolute; right: 10px; top: 35px;">
                                                            <i class="bi bi-eye"></i>
                                                        </span> 
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-8 mt-4">

                                                        <button type="submit" name="submit"
                                                            class="btn btn-secondary">Update
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

            <!-- END Footer -->
            <!-- Begin scroll top -->

            <!-- END scroll top -->
        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->

    <?php if (isset($_SESSION['update-user'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: " Failed",
                    text: "<?= $_SESSION['update-user'] ?>",
                    confirmButtonColor: "#d33"
                });

            });

        </script>

        <?php unset($_SESSION['update-user']); ?>
        

    <?php endif; ?>
    <?php if (isset($_SESSION['update-user'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: " Failed",
                    text: "<?= $_SESSION['update-user'] ?>",
                    confirmButtonColor: "#d33"
                });

            });

        </script>

        <?php unset($_SESSION['update-user']); ?>
        

    <?php endif; ?>

    <?php if (isset($_SESSION['update-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "<?= $_SESSION['update-success'] ?>",
                    confirmButtonColor: "#28a745"
                });

            });

        </script>

        <?php unset($_SESSION['update-success']); ?>
        

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
    <!-- Bootstrap datepicker -->
    <script src="account/assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <!-- select2 -->
    <script src="account/assets/libs/select2/js/select2.min.js"></script>

    <!-- Init js -->
    <script src="account/assets/js/apps/add-patient-init.js"></script>

    <!-- App js -->
    <script src="account/assets/js/app.js"></script>

</body>



</html>