<?php
require 'partials/header.php';



// Fetch featured post from database
$featured_query = "SELECT * FROM posts WHERE is_featured=1 LIMIT 1";
$featured_result = mysqli_query($connection, $featured_query);
$featured = mysqli_fetch_assoc($featured_result);

if ($featured) {
    $author_id = $featured['author_id'];
    $author_query = "SELECT * FROM users WHERE id=" . intval($author_id);
    $author_result = mysqli_query($connection, $author_query);
    $author = mysqli_fetch_assoc($author_result);

    $category_id = $featured['category_id'];
    $category_query = "SELECT * FROM categories WHERE id=" . intval($category_id);
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);
}

$query = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 9";
$posts = mysqli_query($connection, $query);


?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Home Default || Blogar - Personal Blog Template</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/vendor/font-awesome.css">
    <link rel="stylesheet" href="assets/css/vendor/slick.css">
    <link rel="stylesheet" href="assets/css/vendor/slick-theme.css">
    <link rel="stylesheet" href="assets/css/vendor/base.css">
    <link rel="stylesheet" href="assets/css/plugins/plugins.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- CSS
    ============================================ -->

    <!-- Bootstrap CSS -->

</head>

<body>
    <div class="main-wrapper">
        <div class="mouse-cursor cursor-outer"></div>
        <div class="mouse-cursor cursor-inner"></div>



        <!-- Start Header -->

        <!-- Start Header -->

        <!-- Start Mobile Menu Area  -->
        <div class="popup-mobilemenu-area">
            <div class="inner">
                <div class="mobile-menu-top">
                    <div class="logo">
                        <a href="index.php">
                            <img class="dark-logo" src="assets/images/logo/logo.png" alt="Logo Images">
                            <img class="light-logo" src="assets/images/logo/logo.png" alt="Logo Images">
                        </a>
                    </div>
                    <div class="mobile-close">
                        <div class="icon">
                            <i class="fal fa-times"></i>
                        </div>
                    </div>
                </div>
                <ul class="mainmenu">
                    <li class="menu-item-has-children"><a href="index.php">Home</a>

                    </li>
                    <li class="menu-item-has-children"><a href="#">Categories</a>
                        <ul class="axil-submenu">
                            <li><a href="post-details.php">Accessibility</a></li>
                            <li><a href="post-details.php">Android Dev</a></li>
                            <li><a href="post-details.php">Accessibility</a></li>
                            <li><a href="post-details.php">Android Dev</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#">Post Format</a>
                        <ul class="axil-submenu">
                            <li><a href="post-format-standard.php">Post Format Standard</a></li>
                            <li><a href="post-format-video.php">Post Format Video</a></li>
                            <li><a href="post-format-gallery.php">Post Format Gallery</a></li>
                            <li><a href="post-format-text.php">Post Format Text Only</a></li>
                            <li><a href="post-layout-1.php">Post Layout One</a></li>
                            <li><a href="post-layout-2.php">Post Layout Two</a></li>
                            <li><a href="post-layout-3.php">Post Layout Three</a></li>
                            <li><a href="post-layout-4.php">Post Layout Four</a></li>
                            <li><a href="post-layout-5.php">Post Layout Five</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#">Pages</a>
                        <ul class="axil-submenu">
                            <li><a href="post-list.php">Post List</a></li>
                            <li><a href="archive.php">Post Archive</a></li>
                            <li><a href="author.php">Author Page</a></li>
                            <li><a href="about.php">About Page</a></li>
                            <li><a href="maintenance.php">Maintenance</a></li>
                            <li><a href="contact.php">Contact Us</a></li>
                        </ul>
                    </li>
                    <li><a href="404.php">404 Page</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
                <div class="buy-now-btn">
                    <a href="#">Buy Now <span class="badge">$15</span></a>
                </div>
            </div>
        </div>
        <!-- End Mobile Menu Area  -->


        <?php if ($featured): ?>
            <!-- Start Banner Area -->
            <h1 class="d-none">Home Default Blog</h1>
            <div class="slider-area bg-color-grey">
                <div class="axil-slide slider-style-1">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="slider-activation axil-slick-arrow">
                                    <!-- Start Single Slide  -->
                                    <div class="content-block">
                                        <!-- Start Post Thumbnail  -->
                                        <div class="post_thumbnail">
                                            <a href="post?id=<?= $featured['id'] ?>">
                                                
                                                <img src="account/uploads/<?= htmlspecialchars($featured['thumbnail']) ?>"
                                                    alt="Post Images">
                                            </a>
                                        </div>
                                        <!-- Start Post Content  -->
                                        <div class="post-content">
                                            <div class="post-cat">
                                                <div class="post-cat-list">
                                                    <a href="<?= ROOT_URL ?>category?id=<?= $featured['category_id'] ?>"
                                                        class="hover-flip-item"><?= htmlspecialchars($category['title']) ?></a>
                                                </div>
                                            </div>

                                            <h2 class="title"><?= htmlspecialchars($featured['title']) ?></a>
                                            </h2>
                                            <h5 class="title"> <?= substr(htmlspecialchars($featured['body']), 0, 300) ?>...</a>
                                            </h5>
                                            <!-- Post Meta  -->
                                            <div class="post-meta-wrapper with-button">
                                                <div class="post-meta">
                                                    <div class="rounded-circle me-2" >
                                                        <img src="Account/uploads/<?= $author['avatar'] ?>"alt="Author Images" width="35" height="35"  >
                                                    </div>
                                                    <div class="content">
                                                        <h6 class="post-author-name">
                                                            <?= htmlspecialchars("{$author['firstname']} {$author['lastname']}") ?>
                                                        </h6></a></h6>

                                                        <ul class="post-meta-list">
                                                            <li><?= date("m d, Y - H:i", strtotime($featured['created_at'])) ?>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <ul class="social-share-transparent justify-content-end">
                                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                    <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                    <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                </ul>
                                                <div class="read-more-button cerchio">
                                                    <a class="axil-button button-rounded hover-flip-item-wrapper"
                                                        href="post-details.php">
                                                        <span class="hover-flip-item">
                                                            <span data-text="Read Post">Read Post</span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- End Banner Area -->

            <!-- Start Featured Area  -->
            <div class="axil-featured-post axil-section-gap bg-color-grey">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="section-title">
                                <h2 class="title">Most Popular Posts.</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row g-4"> <!-- g-4 = gap between cards -->
                        <?php while ($post = mysqli_fetch_assoc($posts)): ?>

                            <?php
                            // Fetch category
                            $category_query = "SELECT * FROM categories WHERE id=" . intval($post['category_id']);
                            $category_result = mysqli_query($connection, $category_query);
                            $category = mysqli_fetch_assoc($category_result);

                            // Fetch author
                            $author_query = "SELECT * FROM users WHERE id=" . intval($post['author_id']);
                            $author_result = mysqli_query($connection, $author_query);
                            $author = mysqli_fetch_assoc($author_result);
                            ?>

                            <div class="col-lg-6 col-xl-6 col-md-12 col-12">
                                <div class="card h-100">
                                    <a href="post?id=<?= $post['id'] ?>">
                                        <img src="Account/uploads/<?= htmlspecialchars($post['thumbnail']) ?>"
                                            class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>">
                                    </a>
                                    <div class="card-body">
                                        <a href="<?= ROOT_URL ?>category?id=<?= $post['category_id'] ?>"
                                            class="badge bg-primary mb-2">
                                            <?= htmlspecialchars($category['title']) ?>
                                        </a>
                                        <h5 class="post-cat-list">
                                            <a
                                                href="post"><?= htmlspecialchars($post['title']) ?></a>
                                        </h5>
                                        <h5 class="title"><a href="post?id=<?= $post['id'] ?>">
                                                <?= substr(htmlspecialchars($post['body']), 0, 150) ?>...</a>
                                        </h5>
                                    </div>
                                    <div class="card-footer d-flex align-items-center">
                                        <img src="Account/uploads/<?=  htmlspecialchars($author['avatar']) ?>"
                                            class="rounded-circle me-2" width="35" height="35" alt="">
                                        <div>
                                            <small><?= htmlspecialchars("{$author['firstname']} {$author['lastname']}") ?></small><br>
                                            <small
                                                class="text-muted"><?= date("M d, Y", strtotime($post['created_at'])) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    </div>
                </div>

            </div>
            <div class="axil-trending-post-area axil-section-gap bg-color-white">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="section-title">
                                <h2 class="title"> Popular categories</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Start Axil Tab Button  -->
                            <?php
                            $category_query = "SELECT * FROM categories ORDER BY title ASC";
                            $category_result = mysqli_query($connection, $category_query);
                            ?>

                            <ul class="axil-tab-button nav nav-tabs mt--20" role="tablist">
                                <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link"
                                            href="<?= ROOT_URL ?>/category?id=<?= $category['id'] ?>" role="tab">
                                            <?= htmlspecialchars($category['title']) ?>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                            <!-- End Axil Tab Button  -->

                            <!-- Start Axil Tab Content  -->
                            <div class="tab-content">

                                <!-- Single Tab Content  -->
                                <div class="row trend-tab-content tab-pane fade show active" id="trendone"
                                    role="tabpanel" aria-labelledby="trend-one">
                                    <div class="col-lg-8">
                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">01</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">How a developer and
                                                            designer duo at Deutsche Bank keep remote collaboration
                                                            alive</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Jone Doe">Jone Doe</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-01.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list is-active">
                                            <div class="post-inner">
                                                <span class="post-order-list">02</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="BOOKS">BOOKS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">The underrated design
                                                            book
                                                            that transformed the way I work</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Jane Afroj">Jane
                                                                                Afroj</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="/post-details.php">
                                                    <img src="assets/images/post-images/trend-post-02.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">03</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="PROCESS">PROCESS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">Here’s what you should
                                                            (and shouldn’t) do when giving formal feedback</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Israt Ara">Israt Ara</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-03.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">04</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">6 ways you can make
                                                            your design more inclusive and equitable</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Ismat Jahan">Ismat
                                                                                Jahan</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-04.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->
                                    </div>
                                </div>
                                <!-- Single Tab Content  -->

                                <!-- Single Tab Content  -->
                                <div class="row trend-tab-content tab-pane fade" id="trendtwo" role="tabpanel"
                                    aria-labelledby="trend-two">
                                    <div class="col-lg-8">

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">01</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">How a developer and
                                                            designer duo at Deutsche Bank keep remote collaboration
                                                            alive</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Jakowar">Jakowar</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-01.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control is-active">
                                            <div class="post-inner">
                                                <span class="post-order-list">02</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="BOOKS">BOOKS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">The underrated design
                                                            book
                                                            that transformed the way I work</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Jasika">Jasika</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-02.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">03</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="PROCESS">PROCESS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">Here’s what you should
                                                            (and shouldn’t) do when giving formal feedback</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Israt Ara">Israt Ara</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-03.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">04</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">How a developer and
                                                            designer duo at Deutsche Bank keep remote collaboration
                                                            alive</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="John Jane">John Jane</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-04.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                    </div>
                                </div>
                                <!-- Single Tab Content  -->

                                <!-- Single Tab Content  -->
                                <div class="row trend-tab-content tab-pane fade" id="trendthree" role="tabpanel"
                                    aria-labelledby="trend-two">
                                    <div class="col-lg-8">

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">01</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">How a developer and
                                                            designer duo at Deutsche Bank keep remote collaboration
                                                            alive</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Najmul">Najmul</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-01.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control is-active">
                                            <div class="post-inner">
                                                <span class="post-order-list">02</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="BOOKS">BOOKS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">The underrated design
                                                            book
                                                            that transformed the way I work</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Kanak Lota">Kanak
                                                                                Lota</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-02.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">03</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="PROCESS">PROCESS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">Here’s what you should
                                                            (and shouldn’t) do when giving formal feedback</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Nowsin Afroj">Nowsin
                                                                                Afroj</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-03.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">04</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">How a developer and
                                                            designer duo at Deutsche Bank keep remote collaboration
                                                            alive</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Ismat Jahan">Ismat
                                                                                Jahan</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-04.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                    </div>
                                </div>
                                <!-- Single Tab Content  -->

                                <!-- Single Tab Content  -->
                                <div class="row trend-tab-content tab-pane fade" id="trendfour" role="tabpanel"
                                    aria-labelledby="trend-two">
                                    <div class="col-lg-8">

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">01</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">How a developer and
                                                            designer duo at Deutsche Bank keep remote collaboration
                                                            alive</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Ismat Jahan">Ismat
                                                                                Jahan</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-01.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control is-active">
                                            <div class="post-inner">
                                                <span class="post-order-list">02</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="BOOKS">BOOKS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">The underrated design
                                                            book
                                                            that transformed the way I work</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Jasika">Jasika</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-02.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">03</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="PROCESS">PROCESS</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">Here’s what you should
                                                            (and shouldn’t) do when giving formal feedback</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Najmul Alom">Najmul
                                                                                Alom</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-03.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->

                                        <!-- Start Single Post  -->
                                        <div class="content-block trend-post post-order-list axil-control">
                                            <div class="post-inner">
                                                <span class="post-order-list">04</span>
                                                <div class="post-content">
                                                    <div class="post-cat">
                                                        <div class="post-cat-list">
                                                            <a class="hover-flip-item-wrapper" href="#">
                                                                <span class="hover-flip-item">
                                                                    <span data-text="CASE STUDY">CASE STUDY</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <h3 class="title"><a href="post-details.php">How a developer and
                                                            designer duo at Deutsche Bank keep remote collaboration
                                                            alive</a></h3>
                                                    <div class="post-meta-wrapper">
                                                        <div class="post-meta">
                                                            <div class="content">
                                                                <h6 class="post-author-name">
                                                                    <a class="hover-flip-item-wrapper"
                                                                        href="author.php">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Jakarark">Jakarark</span>
                                                                        </span>
                                                                    </a>
                                                                </h6>
                                                                <ul class="post-meta-list">
                                                                    <li>Feb 17, 2019</li>
                                                                    <li>3 min read</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <ul class="social-share-transparent justify-content-end">
                                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="post-thumbnail">
                                                <a href="post-details.php">
                                                    <img src="assets/images/post-images/trend-post-04.jpg"
                                                        alt="Post Images">
                                                </a>
                                            </div>
                                        </div>
                                        <!-- End Single Post  -->
                                    </div>
                                </div>
                                <!-- Single Tab Content  -->
                            </div>
                            <!-- End Axil Tab Content  -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Trending Post Area  -->

            <!-- Start Post Grid Area  -->
            
            <!-- End Post Grid Area  -->

            <!-- Start Post List Wrapper  -->
            
            <!-- End Post List Wrapper  -->

            <!-- Start Video Area  -->
            <!--div class="axil-video-post-area axil-section-gap bg-color-black">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="section-title">
                                <h2 class="title">Featured Video</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-xl-6 col-lg-6 col-md-12 col-md-6 col-12">
                            <div class="content-block post-default image-rounded mt--30">
                                <div class="post-thumbnail">
                                    <a href="post-details.php">
                                        <img src="assets/images/post-images/post-dark-01.jpg" alt="Post Images">
                                    </a>
                                    <a class="video-popup position-top-center" href="post-details.php"><span
                                            class="play-icon"></span></a>
                                </div>
                                <div class="post-content">
                                    <div class="post-cat">
                                        <div class="post-cat-list">
                                            <a class="hover-flip-item-wrapper" href="#">
                                                <span class="hover-flip-item">
                                                    <span data-text="CAREERS">CAREERS</span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <h3 class="title"><a href="post-details.php">Security isn’t just a technology
                                            problem
                                            it’s about design, too</a></h3>
                                    <div class="post-meta-wrapper">
                                        <div class="post-meta">
                                            <div class="content">
                                                <h6 class="post-author-name">
                                                    <a class="hover-flip-item-wrapper" href="author.php">
                                                        <span class="hover-flip-item">
                                                            <span data-text="Ismat Jahan">Ismat Jahan</span>
                                                        </span>
                                                    </a>
                                                </h6>
                                                <ul class="post-meta-list">
                                                    <li>Feb 17, 2019</li>
                                                    <li>3 min read</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <ul class="social-share-transparent justify-content-end">
                                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                            <li><a href="#"><i class="fas fa-link"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-12 col-md-6 col-12">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="content-block post-default image-rounded mt--30">
                                        <div class="post-thumbnail">
                                            <a href="post-details.php">
                                                <img src="assets/images/post-images/post-dark-04.jpg" alt="Post Images">
                                            </a>
                                            <a class="video-popup size-medium position-top-center"
                                                href="post-details.php"><span class="play-icon"></span></a>
                                        </div>
                                        <div class="post-content">
                                            <div class="post-cat">
                                                <div class="post-cat-list">
                                                    <a class="hover-flip-item-wrapper" href="#">
                                                        <span class="hover-flip-item">
                                                            <span data-text="DESIGN">DESIGN</span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                            <h5 class="title"><a href="post-details.php">Get Ready To Up Your Creative
                                                    Game With The </a></h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="content-block post-default  image-rounded mt--30">
                                        <div class="post-thumbnail">
                                            <a href="post-details.php">
                                                <img src="assets/images/post-images/post-dark-03.jpg" alt="Post Images">
                                            </a>
                                            <a class="video-popup size-medium position-top-center"
                                                href="post-details.php"><span class="play-icon"></span></a>
                                        </div>
                                        <div class="post-content">
                                            <div class="post-cat">
                                                <div class="post-cat-list">
                                                    <a class="hover-flip-item-wrapper" href="#">
                                                        <span class="hover-flip-item">
                                                            <span data-text="LEADERSHIP">LEADERSHIP</span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                            <h5 class="title"><a href="post-details.php">Traditional design won’t save
                                                    us in the COVID-19 era</a></h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="content-block post-default image-rounded mt--30">
                                        <div class="post-thumbnail">
                                            <a href="post-details.php">
                                                <img src="assets/images/post-images/post-dark-04.jpg" alt="Post Images">
                                            </a>
                                            <a class="video-popup size-medium position-top-center"
                                                href="post-details.php"><span class="play-icon"></span></a>
                                        </div>
                                        <div class="post-content">
                                            <div class="post-cat">
                                                <div class="post-cat-list">
                                                    <a class="hover-flip-item-wrapper" href="#">
                                                        <span class="hover-flip-item">
                                                            <span data-text="PRODUCT UPDATES">PRODUCT UPDATES</span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                            <h5 class="title"><a href="post-details.php">New: Freehand Templates, built
                                                    for the whole team</a></h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                    <div class="content-block post-default image-rounded mt--30">
                                        <div class="post-thumbnail">
                                            <a href="post-details.php">
                                                <img src="assets/images/post-images/post-dark-05.jpg" alt="Post Images">
                                            </a>
                                            <a class="video-popup size-medium position-top-center"
                                                href="post-details.php"><span class="play-icon"></span></a>
                                        </div>
                                        <div class="post-content">
                                            <div class="post-cat">
                                                <div class="post-cat-list">
                                                    <a class="hover-flip-item-wrapper" href="#">
                                                        <span class="hover-flip-item">
                                                            <span data-text="COLLABORATION">COLLABORATION</span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                            <h5 class="title"><a href="post-details.php">The 1 tool that helps remote
                                                    teams
                                                    collaborate better</a></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div-->
            <!-- End Video Area  -->

            <!-- Start Instagram Area  -->
           
            <!-- End Instagram Area  -->

          <?php require 'partials/footer.php';?>
            <!-- End Footer Area  -->

            <!-- Start Back To Top  -->
            <a id="backto-top"></a>
            <!-- End Back To Top  -->

        </div>

        <!-- JS
============================================ -->
        <!-- Modernizer JS -->
        <script src="assets/js/vendor/modernizr.min.js"></script>
        <!-- jQuery JS -->
        <script src="assets/js/vendor/jquery.js"></script>
        <!-- Bootstrap JS -->
        <script src="assets/js/vendor/bootstrap.min.js"></script>
        <script src="assets/js/vendor/slick.min.js"></script>
        <script src="assets/js/vendor/tweenmax.min.js"></script>
        <script src="assets/js/vendor/js.cookie.js"></script>
        <script src="assets/js/vendor/jquery.style.switcher.js"></script>


        <!-- Main JS -->
        <script src="assets/js/main.js"></script>

</body>



</html>