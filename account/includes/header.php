<?php

require_once __DIR__ . '/helpers.php';


if (!isset($_SESSION['user-id'])) {
    $_SESSION['alert'] = [
        "type" => "warning",
        "message" => "You have to login first"
    ];
    header("Location: signin");
    exit();
}

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
    header("Location: signin");
    exit();
}

$headerNotifications = getUserNotifications($connection, $loggedInId, 8);
$unreadNotificationCount = getUnreadNotificationCount($connection, $loggedInId);

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
   <!-- Home Page Example -->
<meta property="og:title" content="ideaHub — Where Ideas Meet Execution" />
<meta property="og:description" content="Post your idea free. Connect with developers, founders, and investors. The focused marketplace for raw ideas." />
<meta property="og:image" content="https://ideahub.com/og-image.png" />
<meta property="og:url" content="https://ideahub.live" />
<meta property="og:type" content="website" />

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="ideaHub — Where Ideas Meet Execution" />
<meta name="twitter:description" content="Post your idea free. Connect with developers, founders, and investors " />
<meta name="twitter:image" content="https://ideahub.com/og-image.png" />
    <!-- layout setup -->
    <!-- <script type="module" src="account/assets/js/layout-setup.js"></script> -->
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="account/assets/images/Ideahub-sm.png">
      <!-- select2 -->
    <link href="account/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css">
     <!-- slick-carousel css -->
    <link rel="stylesheet" href="account/assets/libs/slick-carousel/slick/slick-theme.css">
    <link rel="stylesheet" href="account/assets/libs/slick-carousel/slick/slick.css">


    <!-- Simplebar Css -->
    <link rel="stylesheet" href="account/assets/libs/simplebar/simplebar.min.css">
    
    <!-- Bootstrap Css -->
    <link href="account/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css">
    
    <!--icons css-->
    <link href="account/assets/css/icons.min.css" rel="stylesheet" type="text/css">
    
    <!-- App Css-->
    <link href="account/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css">
    <style>
        :root {
            --feed-border: rgba(15, 23, 42, 0.08);
            --feed-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
            --feed-shadow-hover: 0 30px 62px rgba(15, 23, 42, 0.12);
            --feed-muted: #64748b;
            --feed-heading: #0f172a;
            --feed-accent: #0d6efd;
        }

        .modern-feed-shell {
            border: 1px solid var(--feed-border);
            border-radius: 1.75rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .modern-feed-shell .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            padding: 1.25rem 1.5rem 0.75rem;
        }

        .modern-feed-shell .card-body {
            padding: 0;
        }

        .modern-feed-shell .nav.nav-lines .nav-link {
            border-bottom-width: 2px;
            color: var(--feed-muted);
            font-weight: 700;
            padding-left: 0;
            padding-right: 1.25rem;
        }

        .modern-feed-shell .nav.nav-lines .nav-link.active {
            color: var(--feed-heading);
        }

        .modern-post-card {
            position: relative;
            border: 1px solid var(--feed-border);
            border-radius: 1.5rem;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: var(--feed-shadow);
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .modern-post-card:hover {
            border-color: rgba(13, 110, 253, 0.18);
            box-shadow: var(--feed-shadow-hover);
            transform: translateY(-4px);
        }

        .modern-post-card__body {
            padding: 1.35rem;
        }

        .modern-post-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .modern-post-card__author {
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 0;
            color: inherit;
            text-decoration: none;
        }

        .modern-post-card__avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(13, 110, 253, 0.12);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            flex-shrink: 0;
        }

        .modern-post-card__avatar img,
        .modern-post-card__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modern-post-card__avatar .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modern-post-card__author-name {
            display: block;
            color: var(--feed-heading);
            font-weight: 700;
            line-height: 1.1;
        }

        .modern-post-card__author-meta {
            display: block;
            margin-top: 0.18rem;
            color: var(--feed-muted);
            font-size: 0.82rem;
        }

        .modern-post-card__time {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.04);
            color: var(--feed-muted);
            font-size: 0.82rem;
            white-space: nowrap;
        }

        .modern-post-card__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-bottom: 0.95rem;
        }

        .modern-post-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 0.95rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            background: #ffffff;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
        }

        .modern-post-chip--category {
            border-color: rgba(13, 110, 253, 0.14);
            background: rgba(13, 110, 253, 0.08);
            color: var(--feed-accent);
        }

        .modern-post-card__title {
            margin: 0 0 0.75rem;
            color: var(--feed-heading);
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            line-height: 1.3;
        }

        .modern-post-card__title a {
            color: inherit;
            text-decoration: none;
        }

        .modern-post-card__title a:hover {
            color: var(--feed-accent);
        }

        .modern-post-card__excerpt {
            margin-bottom: 1.2rem;
            color: #526071;
            line-height: 1.75;
        }

        .modern-post-card__media {
            position: relative;
            display: block;
            margin-bottom: 1.25rem;
            border-radius: 1.25rem;
            overflow: hidden;
            background: #e2e8f0;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
        }

        .modern-post-card__media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0) 55%, rgba(15, 23, 42, 0.34) 100%);
            opacity: 0.9;
            transition: opacity 0.25s ease;
        }

        .modern-post-card__image {
            display: block;
            aspect-ratio: 16 / 9;
            transition: transform 0.35s ease;
        }

        .modern-post-card:hover .modern-post-card__image {
            transform: scale(1.03);
        }

        .modern-post-card__media-badge {
            position: absolute;
            left: 1rem;
            bottom: 1rem;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 0.95rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            color: var(--feed-heading);
            font-weight: 700;
            backdrop-filter: blur(8px);
        }

        .modern-post-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .modern-post-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .modern-post-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            background: #ffffff;
            color: #475569;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        button.modern-post-action {
            appearance: none;
            cursor: pointer;
        }

        .modern-post-action:hover {
            border-color: rgba(13, 110, 253, 0.2);
            background: #f8fbff;
            color: var(--feed-heading);
            transform: translateY(-1px);
        }

        .modern-post-readmore {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--feed-accent);
            font-weight: 700;
            text-decoration: none;
        }

        .modern-post-readmore:hover {
            color: #0a58ca;
        }

        @media (max-width: 575.98px) {
            .modern-feed-shell .card-header {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .modern-post-card__body {
                padding: 1rem;
            }

            .modern-post-card__title {
                font-size: 1.12rem;
            }

            .modern-post-card__actions {
                width: 100%;
            }

            .modern-post-action {
                justify-content: center;
                width: calc(50% - 0.3rem);
            }

            .modern-post-readmore {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

</head>


<header id="page-topbar">
    <div class="navbar-header">

        <!-- Start Navbar-Brand -->
        <div class="navbar-logo-box">
            <a href="dashboard" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="account/assets/images/Ideahub-sm.png" alt="logo-sm-dark" height="70">
                </span>
                <span class="logo-lg">
                    <img src="account/assets/images/ideahub.png" alt="logo-dark" height="70">
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
                <form class="app-search d-none d-lg-block me-2" action="" method="GET">
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
                            <?php if ($unreadNotificationCount > 0): ?>
                                <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">
                                    <?= $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount ?>
                                </span>
                            <?php endif; ?>
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
                                        <a href="#!" class="badge bg-info-subtle text-info"><?= $unreadNotificationCount ?> new</a>
                                    </div>
                                </div>
                            </div>
                            <div data-simplebar style="max-height: 230px;">
                                <?php if (!empty($headerNotifications)): ?>
                                    <?php foreach ($headerNotifications as $notification): ?>
                                        <?php $notificationMeta = getNotificationMeta($notification['type'] ?? ''); ?>
                                        <a href="<?= htmlspecialchars($notification['link'] ?: '#'); ?>" class="text-reset notification-item" data-notification-id="<?= (int) $notification['id']; ?>">
                                            <div class="d-flex">
                                                <div class="avatar avatar-xs <?= htmlspecialchars($notificationMeta['avatar']); ?> me-3">
                                                    <span class="rounded fs-16">
                                                        <i class="mdi <?= htmlspecialchars($notificationMeta['icon']); ?>"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="mb-1 d-flex align-items-center gap-2">
                                                        <span><?= htmlspecialchars($notification['title']); ?></span>
                                                        <?php if (empty($notification['is_read'])): ?>
                                                            <span class="badge bg-danger-subtle text-danger">New</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <p class="mb-1 fs-12 text-muted"><?= htmlspecialchars($notification['message']); ?></p>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        <i class="mdi mdi-clock-outline"></i>
                                                        <?= getRelativeTime(is_numeric($notification['created_value']) ? date('Y-m-d H:i:s', (int) $notification['created_value']) : $notification['created_value']); ?>
                                                    </p>
                                                </div>
                                                <i class="mdi mdi-chevron-right align-middle ms-2"></i>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="px-4 py-4 text-center text-muted">
                                        <i class="mdi mdi-bell-outline fs-24 d-block mb-2"></i>
                                        No notifications yet.
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-2 border-top">
                                <div class="d-grid">
                                    <a class="btn btn-sm btn-link font-size-14 text-center" href="notifications">
                                        <i class="mdi mdi-arrow-right-circle me-1"></i> View all notifications
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
                                <img src="account/uploads/<?= htmlspecialchars($user['avatar'] ) ?>"
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
                                                <img src="account/uploads/<?= htmlspecialchars($user['avatar'] ) ?>"
                                                    alt="Header Avatar" class="img-fluid">
                                            </span>
                                        </div>
                                        <div class="rich-list-content">
                                            <h3 class="rich-list-title fs-13 mb-1">
                                                <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h3>
                                            <span class="rich-list-subtitle"><?= htmlspecialchars($user['email']) ?></span>
                                        </div>
                                        <div class="rich-list-append"><span
                                                class="badge badge-label-secondary fs-6"></span></div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="grid-nav grid-nav-flush grid-nav-action grid-nav-no-rounded">
                                        <div class="grid-nav-row">
                                            <a href="UpdateUser?id=<?= $user['id'] ?>" class="grid-nav-item">
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
                                    href="logout" class="btn btn-label-danger logout-btn">logout</a></div>
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

const notificationDropdown = document.getElementById('page-header-notifications-dropdown');
if (notificationDropdown) {
    notificationDropdown.addEventListener('shown.bs.dropdown', function () {
        fetch('notifications-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const badge = notificationDropdown.parentElement.querySelector('.topbar-badge');
                    if (badge) {
                        badge.remove();
                    }

                    const newIndicator = notificationDropdown.parentElement.querySelector('.badge.bg-info-subtle');
                    if (newIndicator) {
                        newIndicator.textContent = '0 new';
                    }

                    document.querySelectorAll('.notification-item .badge.bg-danger-subtle').forEach(function (item) {
                        item.remove();
                    });
                }
            })
            .catch(error => {
                console.error('Failed to mark notifications as read:', error);
            });
    });
}

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
        <script src="account/assets/js/sweetalert.js"></script>
