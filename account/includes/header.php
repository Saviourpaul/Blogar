<?php

include 'config/database.php';

if (!isset($_SESSION['user-id'])) {
    $_SESSION['alert'] = [
        "type" => "warning",
        "message" => "You have to login first"
    ];
    header("Location: signin.php");
    exit();
}

// Fetch logged-in user
$loggedInId = $_SESSION['user-id'];

$stmt = $connection->prepare("
    SELECT id, firstname, lastname, email, avatar, is_admin
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $loggedInId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    
    session_destroy();
    header("Location: signin.php");
    exit();
}
function getRelativeTime($datetime) {
    date_default_timezone_set('Africa/Lagos'); // Force local timezone
    
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $seconds = $now->getTimestamp() - $ago->getTimestamp();

    if ($seconds < 60) {
        return "Just now";
    }

    if ($diff->y > 0) return $diff->y . " year" . ($diff->y > 1 ? "s" : "") . " ago";
    if ($diff->m > 0) return $diff->m . " month" . ($diff->m > 1 ? "s" : "") . " ago";
    if ($diff->d > 0) return ($diff->d == 1) ? "Yesterday" : $diff->d . " days ago";
    if ($diff->h > 0) return $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
    if ($diff->i > 0) return $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
    
    return "Just now";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Default Title' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Admin & Dashboard Template" name="description">
    <meta content="Codebucks" name="author">
    
    <!-- layout setup -->
    <!-- <script type="module" src="assets/js/layout-setup.js"></script> -->
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo-sm.png">
      <!-- select2 -->
    <link href="assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css">
     <!-- slick-carousel css -->
    <link rel="stylesheet" href="assets/libs/slick-carousel/slick/slick-theme.css">
    <link rel="stylesheet" href="assets/libs/slick-carousel/slick/slick.css">


    <!-- Simplebar Css -->
    <link rel="stylesheet" href="assets/libs/simplebar/simplebar.min.css">
    
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css">
    
    <!--icons css-->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css">

</head>


<header id="page-topbar">
    <div class="navbar-header">

        <!-- Start Navbar-Brand -->
        <div class="navbar-logo-box">
            <a href="home" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="assets/images/logo.png" alt="logo-sm-dark" height="70">
                </span>
                <span class="logo-lg">
                    <img src="assets/images/logo.png" alt="logo-dark" height="70">
                </span>
            </a>
            
            <button type="button" class="btn btn-icon top-icon sidebar-btn" id="sidebar-btn"
                aria-label="Toggle navigation"><i class="mdi mdi-menu-open align-middle fs-17"></i></button>
            <button type="button" class="btn btn-icon top-icon sidebar-horizontal-btn d-none"
                aria-label="Toggle navigation"><i class="mdi mdi-menu align-middle fs-17"></i></button>
        </div>

        <!-- Start menu -->
        <div class="d-flex justify-content-between menu-sm px-4 ms-auto">
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown d-none d-lg-block">

                </div>

            </div>
            <div class="d-flex align-items-center gap-3">
                <!--Start App Search-->
                <form class="app-search d-none d-lg-block me-2">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Search...">
                        <i data-eva="search-outline" class="align-middle"></i>
                    </div>
                </form>

                <!-- Start Notifications -->
                <?php if (isset($_SESSION['user-id'])): ?>
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn btn-icon top-icon" id="page-header-notifications-dropdown"
                            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <i class="mdi mdi-bell-ring-outline fs-17"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-md dropdown-menu-end dropdown-menu-animated p-0 "
                            aria-labelledby="page-header-notifications-dropdown">
                            <div class="p-4 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0"> <i class="mdi mdi-bell-ring-outline me-1 fs-15"></i> Notifications
                                        </h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="#!" class="badge bg-info-subtle text-info"> 8+</a>
                                    </div>
                                </div>
                            </div>
                            <div data-simplebar style="max-height: 230px;">
                                <a href="#!" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar avatar-xs avatar-label-secondary me-3">
                                            <span class="rounded fs-16">
                                                <i class="mdi mdi-file-document-outline"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">New report has been recived</h6>
                                            <p class="mb-0 fs-12 text-muted"><i class="mdi mdi-clock-outline"></i> 3 min ago
                                            </p>
                                        </div>
                                        <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                    </div>
                                </a>
                                <a href="#!" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar avatar-xs avatar-label-success me-3">
                                            <span class="rounded fs-16">
                                                <i class="mdi mdi-cart-variant"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">Last order was completed</h6>
                                            <p class="mb-0 fs-12 text-muted"><i class="mdi mdi-clock-outline"></i> 1 hour
                                                ago</p>
                                        </div>
                                        <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                    </div>
                                </a>
                                <a href="#!" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar avatar-xs avatar-label-danger me-3">
                                            <span class="rounded fs-16">
                                                <i class="mdi mdi-account-group"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">Completed meeting canceled</h6>
                                            <p class="mb-0 fs-12 text-muted"><i class="mdi mdi-clock-outline"></i> 5 hour
                                                ago</p>
                                        </div>
                                        <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                    </div>
                                </a>
                                <a href="#!" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar avatar-xs avatar-label-warning me-3">
                                            <span class="rounded fs-16">
                                                <i class="mdi mdi-send-outline"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">New feedback received</h6>
                                            <p class="mb-0 fs-12 text-muted"><i class="mdi mdi-clock-outline"></i> 6 hour
                                                ago</p>
                                        </div>
                                        <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                    </div>
                                </a>
                                <a href="#!" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar avatar-xs avatar-label-secondary me-3">
                                            <span class="rounded fs-16">
                                                <i class="mdi mdi-download-box"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">New update was available</h6>
                                            <p class="mb-0 fs-12 text-muted"><i class="mdi mdi-clock-outline"></i> 1 day ago
                                            </p>
                                        </div>
                                        <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                    </div>
                                </a>
                                <a href="#!" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar avatar-xs avatar-label-info me-3">
                                            <span class="rounded fs-16">
                                                <i class="mdi mdi-hexagram-outline"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">Your password was changed</h6>
                                            <p class="mb-0 fs-12 text-muted"><i class="mdi mdi-clock-outline"></i> 2 day ago
                                            </p>
                                        </div>
                                        <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2 border-top">
                                <div class="d-grid">
                                    <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                                        <i class="mdi mdi-arrow-right-circle me-1"></i> View More..
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Start Profile -->
                    <div class="dropdown d-inline-block ps-3 ms-2 border-start admin-user-info">
                        <button type="button" aria-label="profile" class="btn btn-sm p-0" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="avatar avatar-xs p-1 d-inline-block">
                                <img src="uploads/<?= htmlspecialchars($user['avatar'] ) ?>"
                                    alt="Header Avatar" class="img-fluid" height="50">
                            </span>
                            <span
                                class="d-none d-xl-inline-block ms-1 fw-semibold fs-14 admin-name"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></span>
                            <i class="mdi mdi-chevron-down align-middle fs-16 d-none d-xl-inline-block"></i>
                        </button>
                        <div
                            class="dropdown-menu dropdown-menu-lg dropdown-menu-end dropdown-menu-animated overflow-hidden py-0">
                            <div class="card mb-0">
                                <div class="card-header">
                                    <div class="rich-list-item w-100 p-0">
                                        <div class="rich-list-prepend">
                                            <span class="rounded avatar-sm p-1 bg-body d-flex">
                                                <img src="uploads/<?= htmlspecialchars($user['avatar'] ) ?>"
                                                    alt="Header Avatar" class="img-fluid">
                                            </span>
                                        </div>
                                        <div class="rich-list-content">
                                            <h3 class="rich-list-title fs-13 mb-1">
                                                <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h3>
                                            <span class="rich-list-subtitle"><?= htmlspecialchars($user['email']) ?></span>
                                        </div>
                                        <div class="rich-list-append"><span
                                                class="badge badge-label-secondary fs-6">6+</span></div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="grid-nav grid-nav-flush grid-nav-action grid-nav-no-rounded">
                                        <div class="grid-nav-row">
                                            <a href="UpdateUser.php?id=<?= $user['id'] ?>" class="grid-nav-item">
                                                <div class="grid-nav-icon"><i class="far fa-address-card"></i></div>
                                                <span class="grid-nav-content">Profile</span>
                                            </a>
                                            <a href="#!" class="grid-nav-item">
                                                <div class="grid-nav-icon"><i class="far fa-comments"></i></div>
                                                <span class="grid-nav-content">Messages</span>
                                            </a>
                                            <a href="pages-profile.html" class="grid-nav-item">
                                                <div class="grid-nav-icon"><i class="far fa-clone"></i></div>
                                                <span class="grid-nav-content">Activities</span>
                                            </a>
                                        </div>
                                        
                                </div>
                            </div>
                            <div class="card-footer card-footer-bordered rounded-0 border-top text-end  "><a
                                    href="auth/logout.php" class="btn btn-label-danger logout-btn">logout</a></div>
                                    <?php else : ?>
                        </div>
                        <?php endif ?>
                        
                    </div>
                    
                </div>
            </div>
        </div>
        <!-- End menu -->
    </div>
</header>
<script>

document.querySelectorAll(".logout-btn").forEach(function(button){

button.addEventListener("click", function(e){

e.preventDefault();

let deleteUrl = this.getAttribute("href");

Swal.fire({
title: "Are you sure?",
text: "You want to logout !",
icon: "warning",
showCancelButton: true,
confirmButtonColor: "#d33",
cancelButtonColor: "#3085d6",
confirmButtonText: "Yes, logout !"
})

.then(function(result){

if(result.isConfirmed){

Swal.fire(
"Logout!",
"user  has been logout  successfully.",
"success"
)

.then(function(){

window.location.href = deleteUrl;

});

}

});

});

});

</script>
        <script src="assets/js/sweetalert.js"></script>
