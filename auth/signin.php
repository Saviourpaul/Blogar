<?php


$username_email = $_SESSION['signin-data']['username_email'] ?? null;
$password = $_SESSION['signin-data']['password'] ?? null;

unset($_SESSION['signin-data']);


?>

<!DOCTYPE html>
<html lang="zxx" id="pageroot">


<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sign in to IdeaHub to manage your profile, ideas, and community activity.">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="assets/images/Ideahub.png">
    <title>Sign In | IdeaHub</title>
    <link rel="stylesheet" href="assets/css/appb183.css?v120">
</head>

<body class="nk-body">
    <div class="nk-app-root">
       
        <main class="nk-main overflow-hidden has-mask">
            <div class="bg-mask-wraper" data-gsap-in='{"opacity": 0}' data-gsap-delay="4" data-gsap-mobile-delay="0">
                <div class="bg-mask bg-mask-middle bg-pattern-grid-curbed bottom-0 container-xl h-450px mb-n45p mb-sm-n30p mb-md-n25p mb-lg-n15p mb-xl-n10p top-auto translate-middle-x"></div>
                <div class="bg-mask bg-mask-middle bg-glow-g bottom-0 container-xl h-450px mb-n45p mb-sm-n30p mb-md-n25p mb-lg-n15p mb-xl-n10p top-auto translate-middle-x"></div>
            </div>
            <div class="section section-lg">
                <div class="section-content">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-10">
                                <div class="card card-blend card-blend-bottom" data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay="3.4">
                                    <div class="card-body">
                                        <div class="position-relative text-center mb-10">
                                            <h4 class="title text-gradient-heading fw-semibold w-max w-100 mx-auto fs-3 px-8">Login</h4>
                                            <p class="fs-8">Welcome back! Please sign in to continue</p>
                                        </div>
                                        <div class="row g-gs">
                                            <div class="col-sm-6">
                                                <button class="btn border-lighter bg-transparent btn-lg btn-block">
                                                    <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M18.8 10.2083C18.8 9.55831 18.7417 8.93331 18.6333 8.33331H10V11.8833H14.9333C14.7167 13.025 14.0667 13.9916 13.0917 14.6416V16.95H16.0667C17.8 15.35 18.8 13 18.8 10.2083Z" fill="#4285F4" />
                                                        <path d="M9.99974 19.1667C12.4747 19.1667 14.5497 18.35 16.0664 16.95L13.0914 14.6417C12.2747 15.1917 11.2331 15.525 9.99974 15.525C7.61641 15.525 5.59141 13.9167 4.86641 11.75H1.81641V14.1167C3.32474 17.1083 6.41641 19.1667 9.99974 19.1667Z" fill="#34A853" />
                                                        <path d="M4.86732 11.7417C4.68398 11.1917 4.57565 10.6083 4.57565 10C4.57565 9.39166 4.68398 8.80833 4.86732 8.25833V5.89166H1.81732C1.19232 7.125 0.833984 8.51666 0.833984 10C0.833984 11.4833 1.19232 12.875 1.81732 14.1083L4.19232 12.2583L4.86732 11.7417Z" fill="#FBBC05" />
                                                        <path d="M9.99974 4.48331C11.3497 4.48331 12.5497 4.94998 13.5081 5.84998L16.1331 3.22498C14.5414 1.74165 12.4747 0.833313 9.99974 0.833313C6.41641 0.833313 3.32474 2.89165 1.81641 5.89165L4.86641 8.25831C5.59141 6.09165 7.61641 4.48331 9.99974 4.48331Z" fill="#EA4335" />
                                                    </svg>
                                                    <span class="fw-normal text-white">Continue With Google</span>
                                                </button>
                                            </div>
                                            <div class="col-sm-6">
                                                <button class="btn border-lighter bg-transparent btn-lg btn-block">
                                                    <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip0_2062_5690)">
                                                            <path d="M8.35 19.9C3.6 19.05 0 14.95 0 10C0 4.5 4.5 0 10 0C15.5 0 20 4.5 20 10C20 14.95 16.4 19.05 11.65 19.9L11.1 19.45H8.9L8.35 19.9Z" fill="url(#paint0_linear_2062_5690)" />
                                                            <path d="M13.9004 12.8L14.3504 10H11.7004V8.05002C11.7004 7.25002 12.0004 6.65002 13.2004 6.65002H14.5004V4.10002C13.8004 4.00002 13.0004 3.90002 12.3004 3.90002C10.0004 3.90002 8.40039 5.30002 8.40039 7.80002V10H5.90039V12.8H8.40039V19.85C8.95039 19.95 9.50039 20 10.0504 20C10.6004 20 11.1504 19.95 11.7004 19.85V12.8H13.9004Z" fill="white" />
                                                        </g>
                                                        <defs>
                                                            <linearGradient id="paint0_linear_2062_5690" x1="10.0005" y1="19.3045" x2="10.0005" y2="-0.00368389" gradientUnits="userSpaceOnUse">
                                                                <stop stop-color="#0062E0" />
                                                                <stop offset="1" stop-color="#19AFFF" />
                                                            </linearGradient>
                                                            <clipPath id="clip0_2062_5690">
                                                                <rect width="20" height="20" fill="white" />
                                                            </clipPath>
                                                        </defs>
                                                    </svg>
                                                    <span class="fw-normal text-white">Continue With Facebook</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="or-sap my-6">
                                            <span>or</span>
                                        </div>
                                        <form action="signin-logic" method="post" class="contact-form">
                                            <div class="row gx-gs gy-5">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="email-address">Email</label>
                                                        <div class="form-control-wrap">
                                                            <input type="text" id="email-address" class="form-control form-control-xl" name="username_email" value="<?=  $username_email ?>" placeholder="Enter your email or Username">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="subject">Password</label>
                                                        <div class="form-control-wrap">
                                                            <input type="password" id="subject" class="form-control form-control-xl" name="password" value="<?=  $password ?>" placeholder="Enter your password">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="checkDefault">
                                                            <label class="form-check-label" for="checkDefault"> Remember me </label>
                                                        </div>
                                                        <a href="forgot-password" class="link link-primary link-hover-title">Forget Password?</a>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" name="submit" class="btn btn-glow btn-lg btn-block">signin</button>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="mt-6 text-content text-center"> Don't have an account? <a class="link link-primary-light link-hover-primary" href="signup">signup now</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .col -->
                        </div><!-- .row -->
                    </div><!-- .container -->
                </div><!-- .section-content -->
            </div><!-- .section -->
        </main>
     
    </div><!-- root -->
    
    
    <div class="nk-cursor js-cursor"></div>
    <!-- JavaScript -->
    <script src="assets/js/appb183.js?v120"></script>
    
<?php if (isset($_SESSION['signup-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: " success",
                    text: "<?= $_SESSION['signup-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['signup-success']); ?>


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

<?php if(isset($_SESSION['alert'])): ?>

<script>
window.onload = function(){

Swal.fire({
    icon: "<?= $_SESSION['alert']['type'] ?>",
    title: "notification",
    text: "<?= $_SESSION['alert']['message'] ?>",
    timer: 2000,
    showConfirmButton: false
}).then(() => {

<?php if($_SESSION['alert']['type'] === 'success'): ?>

window.location.href = "dashboard";

<?php endif; ?>

});

};
</script>

<?php unset($_SESSION['alert']); endif; ?>



<script src="account/assets/js/sweetalert.js"></script>
    </body>
</html>
