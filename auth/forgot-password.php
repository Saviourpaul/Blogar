<?php
$email = $_SESSION['forgot-password-data']['email'] ?? '';
unset($_SESSION['forgot-password-data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/appb183.css?v120">
</head>
<body class="nk-body">
    <div class="nk-app-root">
        <main class="nk-main overflow-hidden has-mask">
            <div class="section section-lg">
                <div class="section-content">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-xxl-5 col-xl-6 col-lg-7 col-md-9">
                                <div class="card card-blend card-blend-bottom">
                                    <div class="card-body">
                                        <div class="position-relative text-center mb-8">
                                            <h4 class="title text-gradient-heading fw-semibold fs-3">Forgot Password</h4>
                                            <p class="fs-8">Enter your email and we will send you a password reset link.</p>
                                        </div>
                                        <form action="forgot-password-logic" method="post">
                                            <div class="row gx-gs gy-5">
                                                <div class="col-12">
                                                    <label class="form-label" for="email">Email Address</label>
                                                    <div class="form-control-wrap">
                                                        <input type="email" id="email" class="form-control form-control-xl" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email" required>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" name="submit" class="btn btn-glow btn-lg btn-block">Send Reset Link</button>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="mt-6 text-content text-center">
                                            Remembered your password? <a class="link link-primary-light link-hover-primary" href="signin">Back to sign in</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/appb183.js?v120"></script>
    <script src="account/assets/js/sweetalert.js"></script>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            window.onload = function () {
                Swal.fire({
                    icon: "<?= $_SESSION['alert']['type'] ?>",
                    title: "Notification",
                    text: "<?= $_SESSION['alert']['message'] ?>",
                    timer: 2500,
                    showConfirmButton: false
                });
            };
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
</body>
</html>
