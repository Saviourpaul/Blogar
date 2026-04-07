<?php
session_start();

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



    // Generate CSRF token if it doesn’t exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];



    $post_id = (int) $_GET['id'];

    $stmt = $connection->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $comments = $res->fetch_all(MYSQLI_ASSOC);

    // Build tree
    $commentTree = [];
    foreach ($comments as $c) {
        $commentTree[$c['parent_id']][] = $c;
    }
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
    <link rel="stylesheet" href="assets/css/comment.css">


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
                                <?php
                                $userLiked = false;

                                if (isset($_SESSION['user_id'])) {
                                    $uid = $_SESSION['user_id'];
                                    $pid = $post['id'];

                                    $stmt = $conn->prepare("SELECT 1 FROM likes WHERE user_id=? AND post_id=?");
                                    $stmt->bind_param("ii", $uid, $pid);
                                    $stmt->execute();

                                    $res = $stmt->get_result();
                                    $userLiked = $res->num_rows > 0;
                                }
                                ?>

                                <div class="post-like">
                                    <a href="javascript:void(0);" onclick="toggleLike(<?= $post['id'] ?>)">
                                        <i id="like-icon-<?= $post['id'] ?>"
                                            class="<?= $userLiked ? 'fas' : 'fal' ?> fa-thumbs-up"></i>

                                        <span id="like-count-<?= $post['id'] ?>">
                                            <?= $post['likes_count'] ?? 0 ?>
                                        </span>
                                    </a>
                                </div>
                                <script>
                                    function toggleLike(postId) {
                                        fetch('../actions/likes.php', {
                                            method: 'POST',
                                            body: new URLSearchParams({ post_id: postId })
                                        })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.error) {
                                                    alert(data.error);
                                                    return;
                                                }


                                                document.getElementById(`like-count-${postId}`).innerText = data.count;

                                                const icon = document.getElementById(`like-icon-${postId}`);

                                                if (data.status === 'liked') {
                                                    icon.classList.remove('fal');
                                                    icon.classList.add('fas');
                                                } else {
                                                    icon.classList.remove('fas');
                                                    icon.classList.add('fal');
                                                }
                                            })
                                            .catch(err => {
                                                console.error("Like error:", err);
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


                        <?php
                        function renderComments($tree, $parent_id = 0, $level = 0)
                        {
                            if (!isset($tree[$parent_id]))
                                return;

                            echo '<ul class="comment-list">';
                            foreach ($tree[$parent_id] as $c) {
                                $id = $c['id'];
                                $name = htmlspecialchars($c['name']);
                                $message = nl2br(htmlspecialchars($c['message']));
                                $date = date('M d, Y \a\t g:i a', strtotime($c['created_at']));

                                // Instagram-style indentation for replies
                                $indent = $level * 50; // px, replies indented more
                        
                                echo "<li class='comment' style='margin-left: {$indent}px'>";
                                echo "  <div class='comment-card ig-style'>";
                                echo "      <div class='comment-header'>";
                                echo "          <div class='avatar'><img src='assets/images/post-images/author/author-b2.png' alt='Avatar'></div>";
                                echo "          <div class='comment-meta'>";
                                echo "              <span class='commenter-name'>$name</span>";
                                echo "              <span class='comment-date'>$date</span>";
                                echo "          </div>";
                                echo "      </div>";
                                echo "      <div class='comment-body'><p>$message</p></div>";
                                echo "      <div class='comment-footer'>";
                                echo "          <button class='reply-btn' onclick=\"setReplyId($id, '$name')\">Reply</button>";
                                echo "          <button class='like-btn'>Like</button>";
                                echo "      </div>";
                                echo "  </div>";

                                renderComments($tree, $id, $level + 1);
                                echo "</li>";
                            }
                            echo '</ul>';
                        }
                        ?>

                       


                        <div class="axil-comment-area">

                            <div class="axil-total-comment-post">
                                <div class="title">
                                    <h4 class="mb--0"><?= count($comments); ?> Comments</h4>
                                </div>

                                <div class="add-comment-button cerchio">
                                    <a class="axil-button button-rounded" href="#comment-form">
                                        <span>Add Your Comment</span>
                                    </a>
                                </div>
                            </div>

                            <div class="comment-respond" id="comment-form">
                                <h4 class="title" id="reply-title">Post a comment</h4>

                                <p id="replying-to-text" style="display:none; color: #ff3a59; cursor:pointer;"
                                    onclick="cancelReply()">
                                    Cancel Reply ✖
                                </p>

                                <form action="public/actions/comment.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="parent_id" id="parent_id" value="0">

                                    <p class="comment-notes">
                                        <span>Your email address will not be published.</span> Required fields are
                                        marked <span class="required">*</span>
                                    </p>

                                    <div class="row row--10">
                                        <div class="col-lg-4 col-md-4 col-12">
                                            <div class="form-group">
                                                <label>Your Name *</label>
                                                <input name="name" type="text" maxlength="100" required>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Leave a Reply *</label>
                                                <textarea name="message" rows="4" maxlength="1000" required></textarea>
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="form-submit cerchio">
                                                <button type="submit" name="submit" class="axil-button button-rounded">
                                                    Post Comment
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <script>
                                function setReplyId(id, name) {
                                    document.getElementById('parent_id').value = id;
                                    const text = document.getElementById('replying-to-text');
                                    text.style.display = 'block';
                                    text.innerText = "Replying to " + name + " (Cancel ✖)";
                                }

                                function cancelReply() {
                                    document.getElementById('parent_id').value = 0;
                                    const text = document.getElementById('replying-to-text');
                                    text.style.display = 'none';
                                }
                            </script>
                        </div>
                    </div>


                </div>
            </div>
            <div class="axil-comment-area mt--40">
                <ul class="comment-list">
                    <?php renderComments($commentTree); ?>
                </ul>
            </div>
        </div>
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
                                    <img class="dark-logo" src="assets/images/logo/logo-black.png" alt="Logo Images">
                                    <img class="light-logo" src="assets/images/logo/logo-white2.png" alt="Logo Images">
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

<script>
    function setReplyId(id, name) {
        document.getElementById('parent_id').value = id;
        const text = document.getElementById('replying-to-text');
        text.style.display = 'block';
        text.innerText = "Replying to " + name + " (Cancel ✖)";
        // Smooth scroll
        document.getElementById('comment-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function cancelReply() {
        document.getElementById('parent_id').value = 0;
        const text = document.getElementById('replying-to-text');
        text.style.display = 'none';
    }
</script>

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