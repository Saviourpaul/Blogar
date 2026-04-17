<?php

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
<html lang="zxx" id="pageroot">


<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Build a stunning landing page for your AI-powered SaaS using the BrightSaaS template, featuring a modern dark-design and glowing light effect.">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="images/favicon.png">
    <title>Sign Up - URL Shortener | BrightSaaS - AI Startup and SaaS Software Website Template. </title>
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
                                            <h4 class="title text-gradient-heading fw-semibold w-max w-100 mx-auto fs-3 px-8">Get Started New!</h4>
                                            <p class="fs-8">Welcome! Please fill in the details to get started.</p>
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
                                        <form action="signup-logic" method="POST" enctype="multipart/form-data" >
                                            <div class="row gx-gs gy-5">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="your-name">First Name</label>
                                                        <div class="form-control-wrap">
                                                            <input type="text" id="your-name" class="form-control form-control-xl" name="firstname" value="<?=  $firstname ?>" placeholder=" firstname">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="your-name">last Name</label>
                                                        <div class="form-control-wrap">
                                                            <input type="text" id="your-name" class="form-control form-control-xl" name="lastname" value="<?=  $lastname ?>" placeholder="lastname">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="your-name">UserName</label>
                                                        <div class="form-control-wrap">
                                                            <input type="text" id="your-name" class="form-control form-control-xl" name="username" value="<?=  $username ?>" placeholder=" username">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="email-address">Email</label>
                                                        <div class="form-control-wrap">
                                                            <input type="email" id="email-address" class="form-control form-control-xl" name="email" value="<?= $email ?>" placeholder="Enter your email">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="subject">Password</label>
                                                        <div class="form-control-wrap">
                                                            <input type="password" id="subject" class="form-control form-control-xl" name="create_password" value="<?=  $create_password ?>" placeholder="Enter your password">
                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label" for="subject"> confirm Password</label>
                                                        <div class="form-control-wrap">
                                                            <input type="password" id="subject" class="form-control form-control-xl" name="confirm_password" value="<?=  $create_password ?>" placeholder="Enter your password">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="checkDefault">
                                                            <label class="form-check-label" for="checkDefault"> I agree to the <a href="shortener-ai-terms.html" class="link link-content link-hover-primary">Terms of Service</a> and <a href="shortener-ai-privacy.html" class="link link-content link-hover-primary">Privacy Policy</a>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit"  name="submit" class="btn btn-glow btn-lg btn-block">Signup</button>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="mt-6 text-content text-center"> Already have an account? <a class="link link-primary-light link-hover-primary" href="signin">Signin </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .row -->
                    </div><!-- .container -->
                </div><!-- .section-content -->
            </div><!-- .section -->
        </main>
        
    </div><!-- root -->
   
 
    <div class="nk-cursor js-cursor"></div>
    <!-- JavaScript -->
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



<script src="account/assets/js/sweetalert.js"></script>
    <script src="assets/js/appb183.js?v120"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9e9931705ad273c1',t:'MTc3NTczNDM2Ng=='};var a=document.createElement('script');a.src='cdn-cgi/challenge-platform/h/b/scripts/jsd/625261456364/maind41d.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>


</html>