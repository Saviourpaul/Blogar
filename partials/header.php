<?php
require 'config/database.php';



$user = null;

if (isset($_SESSION['user-id'])) {

    $stmt = $connection->prepare("
        SELECT firstname, lastname, email, avatar
        FROM users
        WHERE id = ?
    ");

    $stmt->bind_param("i", $_SESSION['user-id']);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}


?>




<header class="header axil-header  header-light header-sticky ">
    <div class="header-wrap">
        <div class="row justify-content-between align-items-center">
            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-3 col-12">
                <div class="logo">
                    <a href="home">
                        <img class="dark-logo" src="assets/images/logo/logo-black.png" alt="Blogar logo" height="200" width="90">
                        <img class="light-logo" src="assets/images/logo/logo-black.png" alt="Blogar logo" height="200" width="90">
                    </a>
                </div>
            </div>

            <div class="col-xl-6 d-none d-xl-block">
                <div class="mainmenu-wrapper">
                    <nav class="mainmenu-nav">
                        <!-- Start Mainmanu Nav -->
                        <ul class="mainmenu">
                            <li class="menu-item-has-children"><a href="home">Home</a>

                            </li>

                            <li class="menu-item-has-children"><a href="post">Posts</a>

                            </li>

                            <li class="menu-item-has-children megamenu-wrapper"><a href="about">About Us</a>

                            </li>

                            <li class="menu-item-has-children"><a href="#">lifestyle</a></li>
                            <li class="menu-item-has-children"><a href="author">Authors</a>
                            </li>
                            <li><a href="contact">Contact Us</a></li>
                        </ul>
                        <!-- End Mainmanu Nav -->
                    </nav>
                </div>
            </div>

            <div class="col-xl-3 col-lg-8 col-md-8 col-sm-9 col-12">
                <div class="header-search text-end d-flex align-items-center">
                    <form class="header-search-form d-sm-block d-none">
                        <div class="axil-search form-group">
                            <button type="submit" class="search-button"><i class="fal fa-search"></i></button>
                            <input type="text" class="form-control" placeholder="Search">
                        </div>
                    </form>
                    <div class="mobile-search-wrapper d-sm-none d-block">
                        <button class="search-button-toggle"><i class="fal fa-search"></i></button>
                        <form class="header-search-form">
                            <div class="axil-search form-group">
                                <button type="submit" class="search-button"><i class="fal fa-search"></i></button>
                                <input type="text" class="form-control" placeholder="Search">
                            </div>
                        </form>
                    </div>

                    <?php if ($user): ?>

                        <div class="profile-dropdown">

                            <button class="avatar-toggle">

                                <img src="<?= ROOT_URL ?>Account/images/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
                                    class="avatar-img">

                            </button>

                            <div class="dropdown-panel">

                                <!-- USER HEADER -->
                                <div class="dropdown-header">

                                    <img src="<?= ROOT_URL ?>Account/images/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
                                        class="avatar-large">

                                    <div class="user-info">

                                        <h4>
                                            <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
                                        </h4>

                                        <p><?= htmlspecialchars($user['email']) ?></p>

                                    </div>

                                </div>

                                <!-- ACTION GRID -->
                                <div class="dropdown-actions">

                                    <a href="<?= ROOT_URL ?>Account/dashboard.php" class="action-card">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Dashboard</span>
                                    </a>

                                    <a href="<?= ROOT_URL ?>profile.php" class="action-card">
                                        <i class="fas fa-user"></i>
                                        <span>Profile</span>
                                    </a>

                                    <a href="<?= ROOT_URL ?>Account/auth/logout.php" class="action-card logout">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>

                                </div>

                            </div>

                        <?php else: ?>
                            
                            <a href="account/auth/signin.php" class="signin-btn">
                                Sign In
                            </a>
                        <?php endif; ?>

                        <!-- Start Hamburger Menu  -->
                        <div class="hamburger-menu d-block d-xl-none">
                            <div class="hamburger-inner">
                                <div class="icon"><i class="fal fa-bars"></i></div>
                            </div>
                        </div>
                        <!-- End Hamburger Menu  -->
                    </div>
                </div>
            </div>
        </div>
</header>
<style>
    .profile-dropdown {
        position: relative;
        display: inline-block;
    }

    /* avatar */

    .avatar-img {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
        transition: transform .2s;
    }

    .avatar-img:hover {
        transform: scale(1.05);
    }

    /* dropdown panel */

    .dropdown-panel {
        position: absolute;
        top: 55px;
        right: 0;
        width: 260px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        padding: 15px;
        display: none;
        z-index: 999;
        transition: all .25s ease;
    }


    .dropdown-panel.active {
        display: block;
    }

    .dropdown-panel.active {
        display: block;
    }

    /* header */

    .dropdown-header {
        display: flex;
        align-items: center;

        gap: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 12px;
        margin-bottom: 12px;
    }

    .avatar-large {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }

    .user-info h4 {
        font-size: 14px;
        margin: 0;
    }

    .user-info p {
        font-size: 12px;
        color: #777;
        margin: 0;
    }

    /* actions grid */

    .dropdown-actions {
        display: flex;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px;
        border-radius: 10px;
        text-decoration: none;
        background: #f8f9fa;
        color: #333;
        font-size: 13px;
        transition: all .2s;
    }

    .action-card i {
        font-size: 16px;
        margin-bottom: 4px;
    }

    .action-card:hover {
        background: #eef2ff;
        transform: translateY(-2px);
    }

    .logout {
        color: #e63946;
    }

    /* signin button */

    .signin-btn {
        padding: 7px 16px;
        background: #2563eb;
        color: white;
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none;
        transition: .2s;
    }

    .signin-btn:hover {
        background: #1d4ed8;
    }

    @media (max-width: 992px) {

        .dropdown-panel {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
        }

    }
</style>


<script>
    document.addEventListener("DOMContentLoaded", function () {

        const toggle = document.querySelector(".avatar-toggle");
        const dropdown = document.querySelector(".dropdown-panel");

        if (toggle) {

            toggle.addEventListener("click", function (e) {

                e.preventDefault();
                dropdown.classList.toggle("active");

            });

            document.addEventListener("click", function (e) {

                if (!e.target.closest(".profile-dropdown")) {
                    dropdown.classList.remove("active");
                }

            });

        }

    });
</script>