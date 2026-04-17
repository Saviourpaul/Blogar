<?php $pageTitle = 'Page Profile';
require 'includes/header.php';



if (!isset($_GET['id'])) {
    die("Invalid author");
}

$author_id = (int) $_GET['id'];

$query = "SELECT id, username, avatar, bio, created_at 
          FROM users 
          WHERE id = $author_id LIMIT 1";

$author_result = mysqli_query($connection, $query);
$author = mysqli_fetch_assoc($author_result);

if (!$author) {
    die("Author not found");
}

$query_posts = "
SELECT 
    p.*, 
    COUNT(c.id) AS comment_count
FROM posts p
LEFT JOIN comments c ON p.id = c.post_id
WHERE p.author_id = $author_id
GROUP BY p.id
ORDER BY p.created_at DESC
";

$posts = mysqli_query($connection, $query_posts);

$query_comments = "
SELECT 
    c.*, 
    p.title AS post_title
FROM comments c
JOIN posts p ON c.post_id = p.id
WHERE c.user_id = $author_id
ORDER BY c.created_at DESC
";

$comments = mysqli_query($connection, $query_comments);
?>


<body>
    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- Start topbar -->

        <!-- End topbar -->
        <!-- ========== Left Sidebar Start ========== -->
        <div class="sidebar-left">

            <div class="sidebar-slide h-100" data-simplebar>

                <!--- Sidebar-menu -->
                <?php require 'includes/sidebar.php'; ?>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ========== Left Sidebar Start ========== -->

        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Profile</h4>
                                <nav aria-label="breadcrumb" class="page-title-right">
                                    <ol class="breadcrumb border-0">
                                        <li class="breadcrumb-item">
                                            <a href="#!">
                                                <i class="mdi mdi-home-outline fs-18 lh-1"></i>
                                                <span class="visually-hidden">Home</span>
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item"><a href="#!">Pages</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-xl-4 col-xxl-3">
                            <div class="card">
                                <div class="card-body profile-info">
                                    <div class="bg-primary-subtle h-24 rounded position-relative overflow-hidden">
                                        <img src="assets/images/pages/profile-bg.png"
                                            class="img-fluid w-100 h-100 object-fit-cover" alt="Background">
                                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-40">
                                        </div>
                                    </div>
                                    <div
                                        class="d-flex align-items-center justify-content-between px-4 profile-img mb-5 position-relative">
                                        <div class="avatar avatar-xl avatar-border shadow-lg">
                                            <img src="assets/images/users/avatar-9.png" alt="Avatar Image"
                                                class="avatar-lg">
                                        </div>
                                        <span
                                            class="badge bg-body-secondary border text-body px-4 rounded-2 shadow-lg"><img
                                                src="assets/images/pages/active.png" alt="Active Icon" width="14"
                                                class="me-2 align-middle">Active</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-6">
                                        <div>
                                           
                                            <h6 class="mb-1 fs-14 ">
                                                <?= htmlspecialchars($author['username']) ?>></h6>
                                            <p class="text-muted mb-0"><?= htmlspecialchars($author['username']) ?></p>
                                            <small>Joined: <?= date("M Y", strtotime($author['created_at'])) ?></small>
                                        </div>
                                        <button type="button" class="btn btn-outline-light btn-icon"
                                            aria-label="bookmark"><i data-eva="bookmark-outline"
                                                class="text-muted"></i></button>
                                    </div>
                                    <div class="d-flex gap-4 mb-5">
                                        <div class="w-33 d-flex gap-3">
                                            <i data-eva="briefcase" class="size-4 text-muted icon-info"></i>
                                            <div>
                                                <h4 class="mb-0 fs-18 fw-medium">120</h4>
                                                <span class="text-muted">post</span>
                                            </div>
                                        </div>

                                        <div class="w-33 d-flex gap-3">
                                            <i data-eva="person-add" class="size-4 text-muted icon-info"></i>
                                            <div>
                                                <h4 class="mb-0 fs-18 fw-medium">24k</h4>
                                                <span class="text-muted">Followers</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="#!" class="btn btn-light w-100"><i
                                                class="mdi mdi-plus me-1"></i>Follow</a>
                                        <a href="pages-contact.html" class="btn btn-primary w-100"><i
                                                class="mdi mdi-email-outline me-1"></i>Contact Us</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-5">
                                       
                                        <div class="mb-4 d-flex align-items-center gap-2">
                                            <div class="avatar-2xs avatar avatar-border">
                                                <i data-eva="globe" class="size-4"></i>
                                            </div>
                                            <h6 class="mb-0">Social</h6>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="https://www.facebook.com/" target="_blank"
                                                class="btn btn-primary btn-icon" aria-label="Facebook">
                                                <i class="mdi mdi-facebook fs-18"></i>
                                            </a>
                                            <a href="https://www.twitter.com/" target="_blank"
                                                class="btn btn-success btn-icon" aria-label="Twitter">
                                                <i class="mdi mdi-twitter fs-18"></i>
                                            </a>
                                            <a href="https://www.linkedin.com/" target="_blank"
                                                class="btn btn-info btn-icon" aria-label="Linkedin">
                                                <i class="mdi mdi-linkedin fs-18"></i>
                                            </a>
                                            <a href="https://www.instagram.com/" target="_blank"
                                                class="btn btn-pink btn-icon" aria-label="Instagram">
                                                <i class="mdi mdi-instagram fs-18"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-xxl-9">
                            <div class="row">
                                <div class="col-xxl-4">
                                    <div class="card card-h-100">
                                        <div class="card-header">
                                            <h5 class="card-title">Bio</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-2">
                                                Hi, I'm <span class="fw-semibold text-body">Charlie Stone</span>, a
                                                Mobile Application Developer.
                                            </p>
                                            
                                            <p class="text-muted mb-2">
                                                Passionate about coding and technology, I enjoy learning new programming
                                                languages.
                                            </p>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xxl-4">
                                    <div class="card card-h-100">
                                        <div class="card-header">
                                            <h5 class="card-title">Progress</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-5">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-2 fw-medium">
                                                    <span class="text-muted">Languages &amp; Tools : </span>
                                                    <span class="fw-semibold">74%</span>
                                                </div>
                                                <div class="progress custome-progress progress-md">
                                                    <div class="progress-bar bg-primary" style="width: 74%"></div>
                                                </div>
                                            </div>
                                            <div
                                                class="mb-3 fw-medium d-flex justify-content-between align-items-center">
                                                <span><i data-eva="checkmark-outline" class="size-4 me-1"></i>Flutter
                                                    Development</span>
                                                <span class="text-primary fs-14">85%</span>
                                            </div>
                                            <div
                                                class="mb-3 fw-medium d-flex justify-content-between align-items-center">
                                                <span><i data-eva="checkmark-outline" class="size-4 me-1"></i>React
                                                    Native Apps</span>
                                                <span class="text-primary fs-14">75%</span>
                                            </div>
                                            <div
                                                class="mb-3 fw-medium d-flex justify-content-between align-items-center">
                                                <span><i data-eva="checkmark-outline" class="size-4 me-1"></i>Android
                                                    Studio IDE</span>
                                                <span class="text-primary fs-14">90%</span>
                                            </div>
                                            <div
                                                class="mb-3 fw-medium d-flex justify-content-between align-items-center">
                                                <span><i data-eva="checkmark-outline" class="size-4 me-1"></i>Figma
                                                    Design Tool</span>
                                                <span class="text-primary fs-14">80%</span>
                                            </div>
                                            <div class="fw-medium d-flex justify-content-between align-items-center">
                                                <span><i data-eva="checkmark-outline" class="size-4 me-1"></i>Swift iOS
                                                    Apps</span>
                                                <span class="text-primary fs-14">70%</span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Projects</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="rich-list rich-list-bordered rich-list-action px-4 mx-n4"
                                                data-simplebar style="max-height: 210px;">
                                                <a href="#!" class="rich-list-item">
                                                    <div class="rich-list-content">
                                                        <h6 class="rich-list-title">Portfolio Website</h6>
                                                        <span class="rich-list-subtitle max-w-60">A personal website
                                                            showcasing projects and skills using Vue.js and
                                                            Bootstrap.</span>
                                                    </div>
                                                    <div class="avatar-group avatar-group-sm">
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-13.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-14.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle avatar-dark">
                                                            <span>3+</span>
                                                        </div>
                                                    </div>
                                                </a>
                                                <a href="#!" class="rich-list-item">
                                                    <div class="rich-list-content">
                                                        <h6 class="rich-list-title">Task Manager App</h6>
                                                        <span class="rich-list-subtitle max-w-60">Productivity app with
                                                            task tracking, deadlines, and notifications.</span>
                                                    </div>
                                                    <div class="avatar-group avatar-group-sm">
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-20.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle avatar-dark">
                                                            <span>2+</span>
                                                        </div>
                                                    </div>
                                                </a>
                                                <a href="#!" class="rich-list-item">
                                                    <div class="rich-list-content">
                                                        <h6 class="rich-list-title">E-commerce Platform</h6>
                                                        <span class="rich-list-subtitle max-w-60">Online shopping
                                                            platform with payment integration and user
                                                            authentication.</span>
                                                    </div>
                                                    <div class="avatar-group avatar-group-sm">
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-15.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-22.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle avatar-dark">
                                                            <span>4+</span>
                                                        </div>
                                                    </div>
                                                </a>
                                                <a href="#!" class="rich-list-item">
                                                    <div class="rich-list-content">
                                                        <h6 class="rich-list-title">Chat Application</h6>
                                                        <span class="rich-list-subtitle max-w-60">Real-time chat app
                                                            supporting multiple rooms and user authentication.</span>
                                                    </div>
                                                    <div class="avatar-group avatar-group-sm">
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-23.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle avatar-dark">
                                                            <span>2+</span>
                                                        </div>
                                                    </div>
                                                </a>
                                                <a href="#!" class="rich-list-item">
                                                    <div class="rich-list-content">
                                                        <h6 class="rich-list-title">Fitness Tracker App</h6>
                                                        <span class="rich-list-subtitle max-w-60">Mobile app to track
                                                            workouts, nutrition, and progress with social sharing
                                                            features.</span>
                                                    </div>
                                                    <div class="avatar-group avatar-group-sm">
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-18.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle avatar-dark">
                                                            <span>3+</span>
                                                        </div>
                                                    </div>
                                                </a>
                                                <a href="#!" class="rich-list-item">
                                                    <div class="rich-list-content">
                                                        <h6 class="rich-list-title">Project Management Tool</h6>
                                                        <span class="rich-list-subtitle max-w-60">Web and mobile app for
                                                            task management, team collaboration, and timeline
                                                            tracking.</span>
                                                    </div>
                                                    <div class="avatar-group avatar-group-sm">
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-19.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-17.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle avatar-dark">
                                                            <span>5+</span>
                                                        </div>
                                                    </div>
                                                </a>
                                                <a href="#!" class="rich-list-item">
                                                    <div class="rich-list-content">
                                                        <h6 class="rich-list-title">Travel Booking App</h6>
                                                        <span class="rich-list-subtitle max-w-60">Platform for booking
                                                            flights, hotels, and experiences with secure
                                                            payments.</span>
                                                    </div>
                                                    <div class="avatar-group avatar-group-sm">
                                                        <div class="avatar avatar-circle">
                                                            <img src="assets/images/users/avatar-20.png"
                                                                alt="Avatar Image" class="img-fluid">
                                                        </div>
                                                        <div class="avatar avatar-circle avatar-dark">
                                                            <span>6+</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="nav nav-pills" id="nav2-tab" role="tablist">
                                                <a class="nav-item nav-link active" id="nav2-activity-tab"
                                                    data-bs-toggle="tab" href="#nav2-activity" aria-selected="true"
                                                    role="tab">Activity</a>
                                                <a class="nav-item nav-link" id="nav2-teams-tab" data-bs-toggle="tab"
                                                    href="#nav2-teams" aria-selected="false" role="tab">post</a>
                                                <a class="nav-item nav-link" id="nav2-projects-tab" data-bs-toggle="tab"
                                                    href="#nav2-projects" aria-selected="false" role="tab">Projects</a>
                                                <!-- <a class="nav-item nav-link" id="nav2-schedule-tab" data-bs-toggle="tab" href="#nav2-schedule" aria-selected="false" role="tab">Schedule</a> -->
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="tab-content" id="nav2-tabContent">
                                                <div class="tab-pane fade show active" id="nav2-activity"
                                                    role="tabpanel" aria-labelledby="nav2-activity-tab">
                                                    <div class="timeline timeline-timed timeline-icon">
                                                        <div class="timeline-item">
                                                            <span class="timeline-time">08:45</span>
                                                            <div class="timeline-pin">
                                                                <i data-eva="message-square-outline" class="size-4"></i>
                                                            </div>
                                                            <div class="timeline-content">
                                                                <h6 class="rich-list-title mb-1">Commented on a Post
                                                                    <span class="ms-1 text-info">#Design</span>
                                                                </h6>
                                                                <span class="rich-list-paragraph">
                                                                    Shared feedback on the new <strong>UI design
                                                                        proposal</strong> posted in the
                                                                    <a href="#">design team channel</a>.
                                                                </span>
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="nav2-teams" role="tabpanel"
                                                    aria-labelledby="nav2-teams-tab">
                                                    <?php while ($post = mysqli_fetch_assoc($posts)): ?>
                                                    <div class="row g-4">
                                                        <div class="col-xl-8">
                                                            <div class="card border mb-0">
                                                                <div class="card-body">
                                                                    <div class="tab-content p-4">
                                                                        <div class="tab-pane active" id="all-post"
                                                                            role="tabpanel">
                                                                            <div>
                                                                                <div class="row justify-content-center">
                                                                                    <div class="col-xl-8">
                                                                                        <div>


                                                                                            <hr class="mb-4">

                                                                                            <div>
                                                                                                <div
                                                                                                    class="post-card mb-5">
                                                                                                    <h5>
                                                                                                        <a href="postOverview.php?id=13"
                                                                                                            class="text-dark">
                                                                                                             <?= htmlspecialchars($post['title']) ?>
                                                                                                        </a>
                                                                                                    </h5>

                                                                                                    <p
                                                                                                        class="text-muted small">
                                                                                                        <i
                                                                                                            class="mdi mdi-calendar"></i>
                                                                                                        1 hour ago
                                                                                                    </p>

                                                                                                    <a href="postOverview.php?id=13"
                                                                                                        class="position-relative mb-3">
                                                                                                        <img src="uploads/post_69d6a33b91b6b9.18892595.png"
                                                                                                            class="img-fluid rounded img-thumbnail"
                                                                                                            alt="Post Image">
                                                                                                    </a>

                                                                                                    <ul
                                                                                                        class="list-inline border-bottom pb-2 font-size: 0.9rem;">
                                                                                                        <li
                                                                                                            class="list-inline-item me-3">
                                                                                                            <span
                                                                                                                class="badge bg-soft-primary text-primary">
                                                                                                                <i
                                                                                                                    class="bx bx-purchase-tag-alt me-1"></i>
                                                                                                                sports
                                                                                                            </span>
                                                                                                        </li>

                                                                                                        <li class="list-inline-item me-3"
                                                                                                            onclick="handleInteraction(13, 'like')"
                                                                                                            style="cursor:pointer">
                                                                                                            <i
                                                                                                                class="mdi mdi-thumb-up-outline text-success me-1"></i>
                                                                                                            <span
                                                                                                                id="likes-13">0</span>

                                                                                                        </li>
                                                                                                        <li class="list-inline-item me-3"
                                                                                                            onclick="handleInteraction(13, 'dislike')"
                                                                                                            style="cursor:pointer">
                                                                                                            <i
                                                                                                                class="mdi mdi-thumb-down-outline text-danger me-1"></i>
                                                                                                            <span
                                                                                                                id="dislikes-13">0</span>
                                                                                                        </li>

                                                                                                        <li
                                                                                                            class="list-inline-item me-3">
                                                                                                            <a href="postOverview.php?id=13#comments"
                                                                                                                class="text-muted">
                                                                                                                <i
                                                                                                                    class="bx bx-comment-dots me-1"></i>
                                                                                                                0
                                                                                                                comments
                                                                                                            </a>
                                                                                                        </li>

                                                                                                        <li class="list-inline-item me-19"
                                                                                                            onclick="copyShareLink(13)"
                                                                                                            style="cursor:pointer">
                                                                                                            <i
                                                                                                                class="mdi mdi-share text-primary me-1"></i>
                                                                                                            <span
                                                                                                                id="shares-13">0</span>
                                                                                                        </li>
                                                                                                    </ul>

                                                                                                    <p
                                                                                                        class="text-secondary">
                                                                                                        A global talent
                                                                                                        pool is a broad
                                                                                                        collection of
                                                                                                        candidates from
                                                                                                        around the world
                                                                                                        that a company
                                                                                                        can potentially
                                                                                                        hire. On the
                                                                                                        other hand, a
                                                                                                        global talent...
                                                                                                    </p>

                                                                                                    <div class="mb-4">
                                                                                                        <a href="postOverview.php?id=13"
                                                                                                            class="fw-bold text-primary">
                                                                                                            Read more →
                                                                                                        </a>
                                                                                                    </div>
                                                                                                </div>



                                                                                            </div>



                                                                                            <hr class="my-5">

                                                                                            <div class="text-center">
                                                                                                <ul
                                                                                                    class="pagination justify-content-center pagination-rounded">
                                                                                                    <li
                                                                                                        class="page-item disabled">
                                                                                                        <a href="javascript: void(0);"
                                                                                                            class="page-link"><i
                                                                                                                class="mdi mdi-chevron-left"></i></a>
                                                                                                    </li>
                                                                                                    <li
                                                                                                        class="page-item">
                                                                                                        <a href="javascript: void(0);"
                                                                                                            class="page-link">1</a>
                                                                                                    </li>
                                                                                                    <li
                                                                                                        class="page-item active">
                                                                                                        <a href="javascript: void(0);"
                                                                                                            class="page-link">2</a>
                                                                                                    </li>
                                                                                                    <li
                                                                                                        class="page-item">
                                                                                                        <a href="javascript: void(0);"
                                                                                                            class="page-link">3</a>
                                                                                                    </li>
                                                                                                    <li
                                                                                                        class="page-item">
                                                                                                        <a href="javascript: void(0);"
                                                                                                            class="page-link">...</a>
                                                                                                    </li>
                                                                                                    <li
                                                                                                        class="page-item">
                                                                                                        <a href="javascript: void(0);"
                                                                                                            class="page-link">10</a>
                                                                                                    </li>
                                                                                                    <li
                                                                                                        class="page-item">
                                                                                                        <a href="javascript: void(0);"
                                                                                                            class="page-link"><i
                                                                                                                class="mdi mdi-chevron-right"></i></a>
                                                                                                    </li>
                                                                                                </ul>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>


                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <?php endwhile; ?>
                                                </div>




                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- container-fluid -->
            </div><!-- End Page-content -->

            <!-- Begin Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © Aquiry.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="http://codebucks.in/"
                                    target="_blank" class="text-muted">Codebucks</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- END Footer -->
            <!-- Begin scroll top -->
            <div class="progress-wrap" id="progress-scroll">
                <svg class="progress-circle" width="100%" height="100%" viewBox="-1 -1 102 102">
                    <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
                </svg>
            </div>
            <!-- END scroll top -->
        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->



    <!-- Rightbar Sidebar -->


    <!-- Switcher -->

    <!-- Bootstrap bundle js -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Layouts main js -->
    <script src="assets/libs/jquery/jquery.min.js"></script>

    <!-- Metimenu js -->
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>

    <!-- simplebar js -->
    <script src="assets/libs/simplebar/simplebar.min.js"></script>

    <script src="assets/libs/eva-icons/eva.min.js"></script>

    <!-- Scroll Top init -->
    <script src="assets/js/scroll-top.init.js"></script>
    <!-- select2 -->
    <script src="assets/libs/select2/js/select2.min.js"></script>

    <!-- select2 js -->
    <script src="assets/libs/fullcalendar/index.global.min.js"></script>

    <script src="assets/js/pages/profile.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>


<!-- Mirrored from codebucks.in/aquiry/html/ltr/pages-profile.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 04 Dec 2025 11:48:58 GMT -->

</html>