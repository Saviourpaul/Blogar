<?php
include  'partials/header.php';

if (isset($_GET['id'])) {
    $category_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM posts WHERE category_id=$category_id ORDER BY created_at DESC";
    $posts = mysqli_query($connection, $query);

    //fetch category from database using category_id
    $category_query = "SELECT * FROM categories WHERE id=$category_id";
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);
} else {
    header("Location: index.php");
    die();
}



?>



<!doctype html>
<html class="no-js" lang="en">



<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Archive || Blogar - Personal Blog Template</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png">

    <!-- CSS
    ============================================ -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/vendor/font-awesome.css">
    <link rel="stylesheet" href="assets/css/vendor/slick.css">
    <link rel="stylesheet" href="assets/css/vendor/slick-theme.css">
    <link rel="stylesheet" href="assets/css/vendor/base.css">
    <link rel="stylesheet" href="assets/css/plugins/plugins.css">
    <link rel="stylesheet" href="assets/css/style.css">

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
                        <a href="index.html">
                            <img class="dark-logo" src="assets/images/logo/logo-black.png" alt="Logo Images">
                            <img class="light-logo" src="assets/images/logo/logo-white2.png" alt="Logo Images">
                        </a>
                    </div>
                    <div class="mobile-close">
                        <div class="icon">
                            <i class="fal fa-times"></i>
                        </div>
                    </div>
                </div>
                <ul class="mainmenu">
                    <li class="menu-item-has-children"><a href="#">Home</a>
                        <ul class="axil-submenu">
                            <li><a href="index.html">Home Default</a></li>
                            <li><a href="home-creative-blog.html">Home Creative Blog</a></li>
                            <li><a href="home-seo-blog.html">Home Seo Blog</a></li>
                            <li><a href="home-tech-blog.html">Home Tech Blog</a></li>
                            <li><a href="home-lifestyle-blog.html">Home Lifestyle Blog</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#">Categories</a>
                        <ul class="axil-submenu">
                            <li><a href="post-details.html">Accessibility</a></li>
                            <li><a href="post-details.html">Android Dev</a></li>
                            <li><a href="post-details.html">Accessibility</a></li>
                            <li><a href="post-details.html">Android Dev</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#">Post Format</a>
                        <ul class="axil-submenu">
                            <li><a href="post-format-standard.html">Post Format Standard</a></li>
                            <li><a href="post-format-video.html">Post Format Video</a></li>
                            <li><a href="post-format-gallery.html">Post Format Gallery</a></li>
                            <li><a href="post-format-text.html">Post Format Text Only</a></li>
                            <li><a href="post-layout-1.html">Post Layout One</a></li>
                            <li><a href="post-layout-2.html">Post Layout Two</a></li>
                            <li><a href="post-layout-3.html">Post Layout Three</a></li>
                            <li><a href="post-layout-4.html">Post Layout Four</a></li>
                            <li><a href="post-layout-5.html">Post Layout Five</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#">Pages</a>
                        <ul class="axil-submenu">
                            <li><a href="post-list.html">Post List</a></li>
                            <li><a href="archive.html">Post Archive</a></li>
                            <li><a href="author.html">Author Page</a></li>
                            <li><a href="about.html">About Page</a></li>
                            <li><a href="maintenance.html">Maintenance</a></li>
                            <li><a href="contact.html">Contact Us</a></li>
                        </ul>
                    </li>
                    <li><a href="404.html">404 Page</a></li>
                    <li><a href="contact.html">Contact Us</a></li>
                </ul>
                <div class="buy-now-btn">
                    <a href="#">Buy Now <span class="badge">$15</span></a>
                </div>
            </div>
        </div>
        <!-- End Mobile Menu Area  -->



        <!-- Start Breadcrumb Area  -->
        <div class="axil-breadcrumb-area breadcrumb-style-1 bg-color-grey">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner">
                            <h1 class="page-title"><?= htmlspecialchars($category['title']) ?></h1>
                            <?php if (mysqli_num_rows($posts) == 0): ?>
                                <div class="alert__message error lg"
                                    style="display: flex; justify-content: center; align-items: center; height: 100px; background: brown; color: white;">
                                    <p>No post found for this category</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumb Area  -->
        <!-- Start Post List Wrapper  -->
        <div class="axil-post-list-area axil-section-gap bg-color-white">
            <div class="container">

    <div class="row">

        <!-- POSTS COLUMN -->
        <div class="col-lg-8 col-xl-8">

            <?php while ($post = mysqli_fetch_assoc($posts)) { 
                            $author_id = $post['author_id'];
            $author_query = "SELECT * FROM users WHERE id=$author_id";
            $author_result = mysqli_query($connection, $author_query);
            $author = mysqli_fetch_assoc($author_result);
        ?>

            

                <div class="content-block post-list-view mt--30">

                    <div class="post-thumbnail">
                        <a href="#">
                            <img src="account/uploads/<?= htmlspecialchars($post['thumbnail']) ?>">
                        </a>
                    </div>

                    <div class="post-content">
                        
                        

                        <p class="title">

                            <?= htmlspecialchars($post['body']) ?>
                        </p>
                      <div class="rounded-circle me-2">
                        <img src="Account/uploads/<?= $author['avatar'] ?>" alt="Author Images"
                             width="35" height="3">                
                                   <strong><?= htmlspecialchars("{$author['firstname']} {$author['lastname']}") ?></strong>
                                    </div>
                        <div class="post_author-info">
                             <small><?= date("m d, Y - H:i", strtotime($post['created_at'])) ?></small>
                            </div>

                    </div>
                    
                    
                    

                </div>
                
                

            <?php } ?>

        </div>
        


        <!-- SIDEBAR COLUMN -->
       <div class="col-lg-4 col-xl-4 mt_md--40 mt_sm--40">

    <div class="sidebar-inner">

        <div class="axil-single-widget widget widget_categories mb--30">

            <h5 class="widget-title">Categories</h5>

            <ul>

                <?php 
                $category_query = "SELECT * FROM categories ORDER BY title ASC";
                $category_result = mysqli_query($connection, $category_query);

                while ($category = mysqli_fetch_assoc($category_result)) : 
                ?>

                    <li class="cat-item">

                        <a href="category.php?id=<?= $category['id'] ?>" class="inner">

                            <div class="content">
                                <h6 class="title">
                                    <?= htmlspecialchars($category['title']) ?>
                                </h6>
                            </div>

                        </a>

                    </li>

                <?php endwhile; ?>

            </ul>

        </div>

    </div>

</div>

    </div>

    

     <?php require 'partials/footer.php';?>
        
      

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


<!-- Mirrored from new.axilthemes.com/demo/template/blogar/archive.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 19 Feb 2026 19:55:24 GMT -->

</html>