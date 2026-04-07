<?php
require '../config/database.php';


//get back form data if there was registration error
$firstname = $_SESSION['signup-data']['firstname'] ?? null;
$lastname = $_SESSION['signup-data']['lastname'] ?? null;
$username = $_SESSION['signup-data']['username'] ?? null;
$email = $_SESSION['signup-data']['email'] ?? null;
$create_password = $_SESSION['signup-data']['create_password'] ?? null;
$confirm_password = $_SESSION['signup-data']['confirm_password'] ?? null;

unset($_SESSION['signup-data']);



?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <title>Sign Up | IdeaHub </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Admin & Dashboard Template" name="description">
    <meta content="Codebucks" name="author">
    
    <!-- layout setup -->
    <!-- <script type="module" src="../assets/js/layout-setup.js"></script> -->
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="../assets/images/logo-sm.png">
    <!-- Simplebar Css -->
    <link rel="stylesheet" href="../assets/libs/simplebar/simplebar.min.css">
    
    <!-- Bootstrap Css -->
    <link href="../assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css">
    
    <!--icons css-->
    <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css">
    
    <!-- App Css-->
    <link href="../assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css">

</head>

<body>
<div class="position-relative min-vh-100">
    <div class="row gx-0">
        <div class="col-xl-5">
            <div class="row justify-content-center align-items-center p-10 min-vh-100 bg-body-secondary position-relative">
                <div class="col-md-7 col-lg-6 col-xl-8 col-xxl-7">
                    <a href="home" class="text-nowrap d-block w-100">
                        <img src="../assets/images/logo.png" class="dark-logo" height="80"  alt="Logo-Dark">
                    </a>
                    <h3 class="mb-3 mt-8">Sign Up</h3>
                    
                    <p class="text-muted mb-8">Sign up to explore your powerful dashboard.</p>
                    <form action="signup-logic.php" method="POST" enctype="multipart/form-data">
                      
                      
                        <div class="mb-5">
                            <label for="firstname" class="form-label">firstname</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?=  $firstname ?>" placeholder="first name">
                        </div>
                        <div class="mb-5">
                            <label for="last name" class="form-label">last name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" value="<?=  $lastname ?>" placeholder=" last name">
                        </div>
                        <div class="mb-5">
                            <label for="username" class="form-label">username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?=  $username ?>" placeholder="username">
                        </div>
                        <div class="mb-5">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="Email"  name="email" value="<?=   $email ?>" placeholder="email">
                        </div>
                        <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="create_password" value="<?=  $create_password ?>"
                                   placeholder="Enter password" >
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswords">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                        <div class="mb-3">
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
                    
                   
                    
                        <div class="mt-6 mb-8 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="customControlInline">
                                <label class="form-check-label" for="customControlInline">Remember me</label>
                            </div>
                            <a href="auth-reset-password.html" class="text-muted"><i class="mdi mdi-lock me-1"></i> Forgot your password?</a>
                        </div>
                        <button type="submit" name="submit" class="btn btn-secondary w-100">Sign Up</button>
                        <div class="mt-5">
                            <p class="mb-0">Already have an Account ? <a href="signin.php" class="fw-medium text-secondary"> Sign In </a> </p>
                        </div>
                       
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-7 d-none d-md-block">
        <div class="vh-100 d-flex flex-column justify-content-center align-items-center"
                style="
                background-image: url('../assets/images/auth/premium_photo-1770177668980-c85d505acbcc.avif');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;">
               
                <div class="text-center z-index-2">
                    <p class="display-4 text fw-normal mb-6" style="color:white;">
                        <strong>Welcome to IdeaHub</strong>
                         

                    </p>
                    
                </div>

            </div>
       
    </div>
</div>

<?php if(isset($_SESSION['signup-errors'])): ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

Swal.fire({
icon: "error",
title: " Failed",
text: "<?= $_SESSION['signup-errors'] ?>",
confirmButtonColor: "#d33"
});

});

</script>

<?php unset($_SESSION['signup-errors']); ?>

<?php endif; ?>

 

<script>
        // Toggle password visibility
            document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const icon = toggle.querySelector('i');

            toggle.addEventListener('click', () => {
                const isHidden = password.type === 'password' ;
                password.type = isHidden ? 'text' : 'password' ;
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
                const isHidden = password.type === 'password' ;
                password.type = isHidden ? 'text' : 'password' ;
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        });
    </script>



<script src="../assets/js/sweetalert.js"></script>



<!-- Bootstrap bundle js -->
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Layouts main js -->
<script src="../assets/libs/jquery/jquery.min.js"></script>

<!-- Metimenu js -->
<script src="../assets/libs/metismenu/metisMenu.min.js"></script>

<!-- simplebar js -->
<script src="../assets/libs/simplebar/simplebar.min.js"></script>

<script src="../assets/libs/eva-icons/eva.min.js"></script>

<!-- Scroll Top init -->
<script src="../assets/js/scroll-top.init.js"></script>
<!-- App js -->
<script src="../assets/js/app.js"></script>

</body>


</html>