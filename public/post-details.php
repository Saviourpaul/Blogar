<?php
include 'partials/header.php';


if (isset($_GET['id'])) {



    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM posts WHERE id=$id";
    $result = mysqli_query($connection, $query);

    $post = mysqli_fetch_assoc($result);
    // Fetch author from database using author_id
    $author_id = $post['author_id'];
    $author_query = "SELECT * FROM users WHERE id=$author_id";
    $author_result = mysqli_query($connection, $author_query);
    $author = mysqli_fetch_assoc($author_result);
    // Fetch category from database using category_id
    $category_id = $post['category_id'];
    $category_query = "SELECT * FROM categories WHERE id=$category_id";
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);


    


    

}





?>

<!doctype html>
<html class="no-js" lang="en">


<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Post Details || Blogar - Personal Blog Template</title>
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
                        <a href="index.php">
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
                            <li><a href="index.php">Home Default</a></li>
                            <li><a href="home-creative-blog.php">Home Creative Blog</a></li>
                            <li><a href="home-seo-blog.php">Home Seo Blog</a></li>
                            <li><a href="home-tech-blog.php">Home Tech Blog</a></li>
                            <li><a href="home-lifestyle-blog.php">Home Lifestyle Blog</a></li>
                        </ul>
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



        <!-- Start Banner Area -->
        <div class="banner banner-single-post post-formate post-standard alignwide">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- Start Single Slide  -->
                        <div class="content-block">
                            <!-- Start Post Thumbnail  -->
                            <div class="post-thumbnail">
                                <img src="account/uploads/<?= htmlspecialchars($post['thumbnail']) ?>"
                                    alt="Post Images">
                            </div>
                            <!-- End Post Thumbnail  -->
                            <!-- Start Post Content  -->
                            <div class="post-content">
                                <div class="post-cat">
                                    <div class="post-cat-list">
                                        <a class="hover-flip-item-wrapper" href="#">
                                            <span class="hover-flip-item">
                                                <span data-text=""><?= $post['category_id'] ?></span>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <h1 style="color:black" class="title"><?= htmlspecialchars($post['title']) ?></h1>
                                <!-- Post Meta  -->
                                <div class="post-meta-wrapper">
                                    <div class="post-meta">
                                        <div class="rounded-circle me-2">
                                            <img src="account/uploads/<?= htmlspecialchars($author['avatar']) ?>"
                                                alt="Author Images" height="35" width="35">
                                        </div>
                                        <div class="content">
                                            <h5><?= htmlspecialchars(string: "{$author['firstname']} {$author['lastname']}") ?>
                                            </h5>

                                            <ul class="post-meta-list">
                                                <li>Feb 17, 2019</li>
                                                <li>300k Views</li>
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
                            <!-- End Post Content  -->
                        </div>
                        <!-- End Single Slide  -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Banner Area -->

        <!-- Start Post Single Wrapper  -->
        <div class="post-single-wrapper axil-section-gap bg-color-white">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="axil-post-details">
                            <figure class="wp-block-image">
                                <img src="account/uploads/<?= htmlspecialchars($post['thumbnail']) ?>"
                                    alt="Post Images">
                                <!--figcaption>The Apple Design Award trophy, created by the Apple Design team, is a symbol
                                    of achievement and excellence.</figcaption>
                            </figure-->


                                <div class="axil-post-details">
                                  <p><?= htmlspecialchars($post['body']) ?></p>
                                <h5>by: <?= htmlspecialchars("{$author['firstname']} {$author['lastname']}") ?></h5>
                                <small><?= date("m d, Y - H:i", strtotime($post['created_at'])) ?></small>
                               
                                </div>
                                 
                                <div class="social-share-block">
                                   <div class="post-like"> 
                                        <a href="javascript:void(0);" onclick="toggleLike(<?= $post['id'] ?>)">
                                            <i class="fal fa-thumbs-up"></i>
                                            <span id="like-count-<?= $post['id'] ?>">
                                                <?= $post['likes_count'] ?? 0 ?>
                                            </span>
                                        </a>
                                    </div>
                                    <script>
                                        function toggleLike(postId) {
                                                        fetch('actions/likes.php', {
                                                            method: 'POST',
                                                            body: new URLSearchParams({ post_id: postId })
                                                        })
                                                        .then(res => res.json())
                                                        .then(data => {
                                                            if (data.error) {
                                                                alert("Login required");
                                                                return;
                                                            }

                                                            // update UI instantly
                                                            const countSpan = document.getElementById(`like-count-${postId}`);

                                                            if (data.status === 'liked') {
                                                                countSpan.innerText = parseInt(countSpan.innerText) + 1;
                                                            } else {
                                                                countSpan.innerText = parseInt(countSpan.innerText) - 1;
                                                            }
                                                        });
                                                    }
                                    </script>
                                    <ul class="social-icon icon-rounded-transparent md-size">
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                        <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                        <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                    </ul>
                                </div>

                                <!-- Start Author  -->
                                
                                <!-- End Author  -->

                                <!-- Start Comment Form Area  -->
                                <div class="axil-comment-area">
                                    <div class="axil-total-comment-post">
                                        <div class="title">
                                            <h4 class="mb--0">30+ Comments</h4>
                                        </div>
                                        <div class="add-comment-button cerchio">
                                            <a class="axil-button button-rounded" href="post-details.php"
                                                tabindex="0"><span>Add Your Comment</span></a>
                                        </div>
                                    </div>

                                    <!-- Start Comment Respond  -->
                                    <div class="comment-respond">
                                        <h4 class="title">Post a comment</h4>
                                        <form action="#">
                                            <p class="comment-notes"><span id="email-notes">Your email address will not
                                                    be
                                                    published.</span> Required fields are marked <span
                                                    class="required">*</span></p>
                                            <div class="row row--10">
                                                <div class="col-lg-4 col-md-4 col-12">
                                                    <div class="form-group">
                                                        <label>Your Name</label>
                                                        <input id="name" type="text">
                                                    </div>
                                                </div>
                                                
                                                
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Leave a Reply</label>
                                                        <textarea name="message"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <p class="comment-form-cookies-consent">
                                                        <input id="wp-comment-cookies-consent"
                                                            name="wp-comment-cookies-consent" type="checkbox"
                                                            value="yes">
                                                        <label for="wp-comment-cookies-consent">Save my name, email, and
                                                            website in this browser for the next time I comment.</label>
                                                    </p>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-submit cerchio">
                                                        <input name="submit" type="submit" id="submit"
                                                            class="axil-button button-rounded" value="Post Comment">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- End Comment Respond  -->

                                    <!-- Start Comment Area  -->
                                    <div class="axil-comment-area">
                                        <h4 class="title">2 comments</h4>
                                        <ul class="comment-list">
                                            <!-- Start Single Comment  -->
                                            <li class="comment">
                                                <div class="comment-body">
                                                    <div class="single-comment">
                                                        <div class="comment-img">
                                                            <img src="assets/images/post-images/author/author-b2.png"
                                                                alt="Author Images">
                                                        </div>
                                                        <div class="comment-inner">
                                                            <h6 class="commenter">
                                                                <a class="hover-flip-item-wrapper" href="#">
                                                                    <span class="hover-flip-item">
                                                                        <span data-text="Cameron Williamson">Cameron
                                                                            Williamson</span>
                                                                    </span>
                                                                </a>
                                                            </h6>
                                                            <div class="comment-meta">
                                                                <div class="time-spent">Nov 23, 2018 at 12:23 pm</div>
                                                                <div class="reply-edit">
                                                                    <div class="reply">
                                                                        <a class="comment-reply-link hover-flip-item-wrapper"
                                                                            href="#">
                                                                            <span class="hover-flip-item">
                                                                                <span data-text="Reply">Reply</span>
                                                                            </span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="comment-text">
                                                                <p class="b2">Duis hendrerit velit scelerisque felis
                                                                    tempus, id porta
                                                                    libero venenatis. Nulla facilisi. Phasellus viverra
                                                                    magna commodo dui lacinia tempus. Donec malesuada
                                                                    nunc
                                                                    non dui posuere, fringilla vestibulum urna mollis.
                                                                    Integer condimentum ac sapien quis maximus. </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <ul class="children">
                                                    <!-- Start Single Comment  -->
                                                    <li class="comment">
                                                        <div class="comment-body">
                                                            <div class="single-comment">
                                                                <div class="comment-img">
                                                                    <img src="assets/images/post-images/author/author-b3.png"
                                                                        alt="Author Images">
                                                                </div>
                                                                <div class="comment-inner">
                                                                    <h6 class="commenter">
                                                                        <a class="hover-flip-item-wrapper" href="#">
                                                                            <span class="hover-flip-item">
                                                                                <span data-text="Rahabi Khan">Rahabi
                                                                                    Khan</span>
                                                                            </span>
                                                                        </a>
                                                                    </h6>
                                                                    <div class="comment-meta">
                                                                        <div class="time-spent">Nov 23, 2018 at 12:23 pm
                                                                        </div>
                                                                        <div class="reply-edit">
                                                                            <div class="reply">
                                                                                <a class="comment-reply-link hover-flip-item-wrapper"
                                                                                    href="#">
                                                                                    <span class="hover-flip-item">
                                                                                        <span
                                                                                            data-text="Reply">Reply</span>
                                                                                    </span>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comment-text">
                                                                        <p class="b2">Pellentesque habitant morbi
                                                                            tristique senectus et netus et malesuada
                                                                            fames ac turpis egestas. Suspendisse
                                                                            lobortis cursus lacinia. Vestibulum vitae
                                                                            leo id diam pellentesque ornare.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <!-- End Single Comment  -->
                                                </ul>
                                            </li>
                                            <!-- End Single Comment  -->

                                            <!-- Start Single Comment  -->
                                            <li class="comment">
                                                <div class="comment-body">
                                                    <div class="single-comment">
                                                        <div class="comment-img">
                                                            <img src="assets/images/post-images/author/author-b2.png"
                                                                alt="Author Images">
                                                        </div>
                                                        <div class="comment-inner">
                                                            <h6 class="commenter">
                                                                <a class="hover-flip-item-wrapper" href="#">
                                                                    <span class="hover-flip-item">
                                                                        <span data-text="Rahabi Khan">Rahabi Khan</span>
                                                                    </span>
                                                                </a>
                                                            </h6>
                                                            <div class="comment-meta">
                                                                <div class="time-spent">Nov 23, 2018 at 12:23 pm</div>
                                                                <div class="reply-edit">
                                                                    <div class="reply">
                                                                        <a class="comment-reply-link hover-flip-item-wrapper"
                                                                            href="#">
                                                                            <span class="hover-flip-item">
                                                                                <span data-text="Reply">Reply</span>
                                                                            </span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="comment-text">
                                                                <p class="b2">Duis hendrerit velit scelerisque felis
                                                                    tempus, id porta
                                                                    libero venenatis. Nulla facilisi. Phasellus viverra
                                                                    magna commodo dui lacinia tempus. Donec malesuada
                                                                    nunc
                                                                    non dui posuere, fringilla vestibulum urna mollis.
                                                                    Integer condimentum ac sapien quis maximus. </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <!-- End Single Comment  -->
                                        </ul>
                                    </div>
                                    <!-- End Comment Area  -->

                                </div>
                                <!-- End Comment Form Area  -->


                        </div>
                    </div>
                    <div class="col-lg-4">
                        <!-- Start Sidebar Area  -->
                        <div class="sidebar-inner">
                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_categories mb--30">
                                <ul>
                                    <li class="cat-item">
                                        <a href="#" class="inner">
                                            <div class="thumbnail">
                                                <img src="assets/images/post-images/category-image-01.jpg" alt="">
                                            </div>
                                            <div class="content">
                                                <h5 class="title">Tech</h5>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="cat-item">
                                        <a href="#" class="inner">
                                            <div class="thumbnail">
                                                <img src="assets/images/post-images/category-image-02.jpg" alt="">
                                            </div>
                                            <div class="content">
                                                <h5 class="title">Style</h5>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="cat-item">
                                        <a href="#" class="inner">
                                            <div class="thumbnail">
                                                <img src="assets/images/post-images/category-image-03.jpg" alt="">
                                            </div>
                                            <div class="content">
                                                <h5 class="title">Travel</h5>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="cat-item">
                                        <a href="#" class="inner">
                                            <div class="thumbnail">
                                                <img src="assets/images/post-images/category-image-04.jpg" alt="">
                                            </div>
                                            <div class="content">
                                                <h5 class="title">Food</h5>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_search mb--30">
                                <h5 class="widget-title">Search</h5>
                                <form action="#">
                                    <div class="axil-search form-group">
                                        <button type="submit" class="search-button"><i
                                                class="fal fa-search"></i></button>
                                        <input type="text" class="form-control" placeholder="Search">
                                    </div>
                                </form>
                            </div>
                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_postlist mb--30">
                                <h5 class="widget-title">Popular on Blogar</h5>
                                <!-- Start Post List  -->
                                <div class="post-medium-block">

                                    <!-- Start Single Post  -->
                                    <div class="content-block post-medium mb--20">
                                        <div class="post-thumbnail">
                                            <a href="post-details.php">
                                                <img src="assets/images/small-images/blog-sm-01.jpg" alt="Post Images">
                                            </a>
                                        </div>
                                        <div class="post-content">
                                            <h6 class="title"><a href="post-details.php">The underrated design book that
                                                    transformed the way I
                                                    work </a></h6>
                                            <div class="post-meta">
                                                <ul class="post-meta-list">
                                                    <li>Feb 17, 2019</li>
                                                    <li>300k Views</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Single Post  -->

                                    <!-- Start Single Post  -->
                                    <div class="content-block post-medium mb--20">
                                        <div class="post-thumbnail">
                                            <a href="post-details.php">
                                                <img src="assets/images/small-images/blog-sm-02.jpg" alt="Post Images">
                                            </a>
                                        </div>
                                        <div class="post-content">
                                            <h6 class="title"><a href="post-details.php">Here’s what you should (and
                                                    shouldn’t) do when</a>
                                            </h6>
                                            <div class="post-meta">
                                                <ul class="post-meta-list">
                                                    <li>Feb 17, 2019</li>
                                                    <li>300k Views</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Single Post  -->

                                    <!-- Start Single Post  -->
                                    <div class="content-block post-medium">
                                        <div class="post-thumbnail">
                                            <a href="post-details.php">
                                                <img src="assets/images/small-images/blog-sm-03.jpg" alt="Post Images">
                                            </a>
                                        </div>
                                        <div class="post-content">
                                            <h6 class="title"><a href="post-details.php">How a developer and designer
                                                    duo at Deutsche Bank keep
                                                    remote</a></h6>
                                            <div class="post-meta">
                                                <ul class="post-meta-list">
                                                    <li>Feb 17, 2019</li>
                                                    <li>300k Views</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Single Post  -->

                                </div>
                                <!-- End Post List  -->

                            </div>
                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_newsletter mb--30">
                                <!-- Start Post List  -->
                                <div class="newsletter-inner text-center">
                                    <h4 class="title mb--15">Never Miss A Post!</h4>
                                    <p class="b2 mb--30">Sign up for free and be the first to <br /> get notified about
                                        updates.</p>
                                    <form action="#">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Your Email ">
                                        </div>
                                        <div class="form-submit">
                                            <button
                                                class="cerchio axil-button button-rounded"><span>Subscribe</span></button>
                                        </div>
                                    </form>
                                </div>
                                <!-- End Post List  -->
                            </div>
                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_ads mb--30">
                                <!-- Start Post List  -->
                                <div class="thumbnail">
                                    <a href="#">
                                        <img src="assets/images/post-single/ads-01.jpg" alt="Ads Images">
                                    </a>
                                </div>
                                <!-- End Post List  -->
                            </div>
                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_social mb--30">
                                <h5 class="widget-title">Stay In Touch</h5>
                                <!-- Start Post List  -->
                                <ul class="social-icon md-size justify-content-center">
                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-slack"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                </ul>
                                <!-- End Post List  -->
                            </div>
                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_instagram mb--30">
                                <h5 class="widget-title">Instagram</h5>
                                <!-- Start Post List  -->
                                <ul class="instagram-post-list-wrapper">
                                    <li class="instagram-post-list">
                                        <a href="#">
                                            <img src="assets/images/small-images/instagram-01.jpg"
                                                alt="Instagram Images">
                                        </a>
                                    </li>
                                    <li class="instagram-post-list">
                                        <a href="#">
                                            <img src="assets/images/small-images/instagram-02.jpg"
                                                alt="Instagram Images">
                                        </a>
                                    </li>
                                    <li class="instagram-post-list">
                                        <a href="#">
                                            <img src="assets/images/small-images/instagram-03.jpg"
                                                alt="Instagram Images">
                                        </a>
                                    </li>
                                    <li class="instagram-post-list">
                                        <a href="#">
                                            <img src="assets/images/small-images/instagram-04.jpg"
                                                alt="Instagram Images">
                                        </a>
                                    </li>
                                    <li class="instagram-post-list">
                                        <a href="#">
                                            <img src="assets/images/small-images/instagram-05.jpg"
                                                alt="Instagram Images">
                                        </a>
                                    </li>
                                    <li class="instagram-post-list">
                                        <a href="#">
                                            <img src="assets/images/small-images/instagram-06.jpg"
                                                alt="Instagram Images">
                                        </a>
                                    </li>
                                </ul>
                                <!-- End Post List  -->
                            </div>
                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->
                            <div class="axil-single-widget widget widget_archive mb--30">
                                <h5 class="widget-title">Archives</h5>
                                <!-- Start Post List  -->
                                <ul>
                                    <li><a href="#">January 2020</a></li>
                                    <li><a href="#">February 2020</a></li>
                                    <li><a href="#">March 2020</a></li>
                                    <li><a href="#">April 2020</a></li>
                                </ul>
                                <!-- End Post List  -->
                            </div>
                            <!-- End Single Widget  -->


                            <!-- Start Single Widget  -->

                            <!-- End Single Widget  -->

                            <!-- Start Single Widget  -->

                            <!-- End Single Widget  -->

                            <!--div class="axil-banner">
                                <div class="thumbnail">
                                    <a href="#">
                                        <img class="w-100" src="assets/images/add-banner/banner-02.png" alt="Banner Images">
                                    </a>
                                </div>
                            </div-->



                        </div>
                        <!-- End Sidebar Area  -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Post Single Wrapper  -->

        <!-- Start More Stories Area  -->
        <div class="axil-more-stories-area axil-section-gap bg-color-grey">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2 class="title">More Stories</h2>
                        </div>
                    </div>
                </div>
                <div class="row">

                    <!-- Start Stories Post  -->
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <!-- Start Post List  -->
                        <div class="post-stories content-block mt--30">
                            <div class="post-thumbnail">
                                <a href="post-details.php">
                                    <img src="assets/images/post-single/stories-01.jpg" alt="Post Images">
                                </a>
                            </div>
                            <div class="post-content">
                                <div class="post-cat">
                                    <div class="post-cat-list">
                                        <a href="#">LEADERSHIP</a>
                                    </div>
                                </div>
                                <h5 class="title"><a href="post-details.php">Microsoft and Bridgestone launch real-time
                                        tire</a></h5>
                            </div>
                        </div>
                        <!-- End Post List  -->
                    </div>
                    <!-- Start Stories Post  -->

                    <!-- Start Stories Post  -->
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <!-- Start Post List  -->
                        <div class="post-stories content-block mt--30">
                            <div class="post-thumbnail">
                                <a href="post-details.php">
                                    <img src="assets/images/post-single/stories-02.jpg" alt="Post Images">
                                </a>
                            </div>
                            <div class="post-content">
                                <div class="post-cat">
                                    <div class="post-cat-list">
                                        <a href="#">DESIGN</a>
                                    </div>
                                </div>
                                <h5 class="title"><a href="post-details.php">Microsoft and Bridgestone launch real-time
                                        tire</a></h5>
                            </div>
                        </div>
                        <!-- End Post List  -->
                    </div>
                    <!-- Start Stories Post  -->

                    <!-- Start Stories Post  -->
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <!-- Start Post List  -->
                        <div class="post-stories content-block mt--30">
                            <div class="post-thumbnail">
                                <a href="post-details.php">
                                    <img src="assets/images/post-single/stories-03.jpg" alt="Post Images">
                                </a>
                            </div>
                            <div class="post-content">
                                <div class="post-cat">
                                    <div class="post-cat-list">
                                        <a href="#">PRODUCT UPDATES</a>
                                    </div>
                                </div>
                                <h5 class="title"><a href="post-details.php">Microsoft and Bridgestone launch real-time
                                        tire</a></h5>
                            </div>
                        </div>
                        <!-- End Post List  -->
                    </div>
                    <!-- Start Stories Post  -->

                    <!-- Start Stories Post  -->
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <!-- Start Post List  -->
                        <div class="post-stories content-block mt--30">
                            <div class="post-thumbnail">
                                <a href="post-details.php">
                                    <img src="assets/images/post-single/stories-04.jpg" alt="Post Images">
                                </a>
                            </div>
                            <div class="post-content">
                                <div class="post-cat">
                                    <div class="post-cat-list">
                                        <a href="#">COLLABORATION</a>
                                    </div>
                                </div>
                                <h5 class="title"><a href="post-details.php">Microsoft and Bridgestone launch real-time
                                        tire</a></h5>
                            </div>
                        </div>
                        <!-- End Post List  -->
                    </div>
                    <!-- Start Stories Post  -->
                </div>
            </div>
        </div>
        <!-- End More Stories Area  -->

        <!-- Start Instagram Area  -->

        <!-- End Instagram Area  -->

        <!-- Start Footer Area  -->
        <div class="axil-footer-area axil-footer-style-1 bg-color-white">
            <!-- Start Footer Top Area  -->
            <div class="footer-top">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Start Post List  -->
                            <div class="inner d-flex align-items-center flex-wrap">
                                <h5 class="follow-title mb--0 mr--20">Follow Us</h5>
                                <ul class="social-icon color-tertiary md-size justify-content-start">
                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                            <!-- End Post List  -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Footer Top Area  -->

            <!-- Start Copyright Area  -->
            <div class="copyright-area">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-9 col-md-12">
                            <div class="copyright-left">
                                <div class="logo">
                                    <a href="index.php">
                                        <img class="dark-logo" src="assets/images/logo/logo-black.png"
                                            alt="Logo Images">
                                        <img class="light-logo" src="assets/images/logo/logo-white2.png"
                                            alt="Logo Images">
                                    </a>
                                </div>
                                <ul class="mainmenu justify-content-start">
                                    <li>
                                        <a class="hover-flip-item-wrapper" href="#">
                                            <span class="hover-flip-item">
                                                <span data-text="Contact Us">Contact Us</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="hover-flip-item-wrapper" href="#">
                                            <span class="hover-flip-item">
                                                <span data-text="Terms of Use">Terms of Use</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="hover-flip-item-wrapper" href="#">
                                            <span class="hover-flip-item">
                                                <span data-text="AdChoices">AdChoices</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="hover-flip-item-wrapper" href="#">
                                            <span class="hover-flip-item">
                                                <span data-text="Advertise with Us">Advertise with Us</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="hover-flip-item-wrapper" href="#">
                                            <span class="hover-flip-item">
                                                <span data-text="Blogar Store">Blogar Store</span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-12">
                            <div class="copyright-right text-start text-lg-end mt_md--20 mt_sm--20">
                                <p class="b3">All Rights Reserved © 2024</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Copyright Area  -->
        </div>
        <!-- End Footer Area  -->

        <!-- Start Back To Top  -->
        <a id="backto-top"></a>
        <!-- End Back To Top  -->

    </div>


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