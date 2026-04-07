<?php $pageTitle = 'all Post';
require 'includes/header.php';


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

$query = "
    SELECT 
        p.id, 
        p.title, 
        p.body, 
        p.thumbnail, 
        p.created_at,
        p.author_id,
        c.title AS category_title,
        u.username AS author_name,
        
        -- Get counts using subqueries (avoids join 'explosions')
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'like') AS likes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'dislike') AS dislikes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'share') AS share_count

    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC 
    LIMIT 9
";

$posts = mysqli_query($connection, $query);

if (!$posts) {
    error_log("Query Error: " . mysqli_error($connection));
    die("A database error occurred. Please try again later.");
}

?>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- Start topbar -->

        <!-- End topbar -->
        <!-- ========== Left Sidebar Start ========== -->
        <?= include 'includes/sidebar.php' ?>
        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ========== Left Sidebar Start ========== -->
        <div class="sidebar-left horizontal-sidebar">


        </div>
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
                                <h4 class="mb-sm-0">Post Overview</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                     <?php if ($featured): ?>
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card sticky-top">
                                <div class="card-body">
                                    <div class="slider slider-for">
                                        <div class="mx-0">
                                            <div class="card mb-0 h-104 bg-light bg-opacity-60">
                                                <img 
                                                    src="uploads/<?= htmlspecialchars($featured['thumbnail']) ?>" style="height:fit-content;!important;"
                                                    class="card-img img-fluid w-100 mx-auto my-auto object-fit-cover"
                                                    alt="Image">
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    
                                    <div class="row gx-6">
                                        <div class="col-xl-8">
                                            <div class="d-flex gap-1 align-items-center mb-3">
                                                
                                                
                                                <a href="#!" class="text-danger"><i class="mdi mdi-heart fs-16"></i></a>
                                                <span
                                                    class="fw-medium"><?= htmlspecialchars($category['title']) ?></span>
                                            </div>
                                            
                                            <h2 class="mb-2 fw-medium"><?= htmlspecialchars($featured['title']) ?></h2>
                                            <h6 class="mb-2">Post Description :</h6>
                                            <h4 class="text-muted mb-5"><?= substr(htmlspecialchars($featured['body']), 0, 200) ?>...</h4>
                                             <div class="mb-10">
                                                                    <a href="postOverview.php?id=<?= $featured['id'] ?>" class="text-primary">Read more <i class="mdi mdi-arrow-right"></i></a>
                                                                </div>
                                            
                                            <div class="d-flex flex-wrap gap-3 mb-6">
                                                <div class="avatar avatar-md avatar-circle overflow-hidden">
                                                    <img src="uploads/<?= htmlspecialchars($author['avatar']) ?>" alt="Avatar Image"
                                                        class="avatar-xs">
                                                </div>
                                                <div class="mt-1">
                                                    <a href="#!" class="text-body fw-semibold mb-1 d-block"><?= htmlspecialchars("{$author['firstname']} {$author['lastname']}") ?><span class="text-muted fw-normal fs-12"> - <?= getRelativeTime($featured['created_at']) ?></span></a>
                                                    
                                                    <div class="d-flex gap-5">
                                                        <li class="list-inline-item me-3" onclick="handleInteraction(<?= $post['id'] ?>, 'like')" style="cursor:pointer"> 
                                                                    <i class="mdi mdi-thumb-up-outline text-success me-1"></i>
                                                                    <span id="likes-<?= $post['id'] ?>"><?= $post['likes'] ?? 0 ?></span>
                                                                </li>

                                                                <li class="list-inline-item me-3" onclick="handleInteraction(<?= $post['id'] ?>, 'dislike')" style="cursor:pointer">
                                                                    <i class="mdi mdi-thumb-down-outline text-danger me-1"></i>
                                                                    <span id="dislikes-<?= $post['id'] ?>"><?= $post['dislikes'] ?? 0 ?></span>
                                                                </li>
                                                         </div>
                                                </div>
                                                
                                                
                                               
                                            </div>
                                             <?php endif; ?>
                                            

                                        </div>
                                         <div class="col-xl-4">
                                            
                                            <div>
                                            <p class="text-muted">Tags</p>
                                             <?php
                                            $category_query = "SELECT * FROM categories ORDER BY title ASC";
                                            $category_result = mysqli_query($connection, $category_query);
                                            ?>

                                            <div class="d-flex flex-wrap gap-2 widget-tag">
                                                <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                                                <div><a href="javascript: void(0);" class="badge bg-light font-size-12"> <?= htmlspecialchars($category['title']) ?></a></div>
                                                <div></div>
                                                <div></div>
                                                <div></div>
                                                <div></div>
                                                <div></div>
                                                <div></div>
                                            </div>
                                             <?php endwhile; ?>
                                        </div>
                                              
                                               
         
                                            </div>
                                        </div>
                                        
                                       
                                    </div>
                                </div>
                            </div>
                       
                        
                        
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <div class="nav nav-lines mb-0" id="card-tab-1" role="tablist">
                                        <a class="nav-item nav-link active" id="card-details-tab" data-bs-toggle="tab"
                                            href="#card-details" aria-selected="true" role="tab">
                                            All Post
                                        </a>
                                        
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content p-4">
                                        <div class="tab-pane active" id="all-post" role="tabpanel">
                                            <div>
                                                <div class="row justify-content-center">
                                                    <div class="col-xl-8">
                                                        <div>
                                                            <!--div class="row align-items-center">
                                                                
                                    
                                                                <div class="col-8">
                                                                    <div>
                                                                        <ul class="nav nav-pills justify-content-end">
                                                                            <li class="nav-item">
                                                                                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">View :</a>
                                                                            </li>
                                                                            <li class="nav-item" data-bs-placement="top" title="List">
                                                                                <a class="nav-link active" href="blog-list.html">
                                                                                    <i class="mdi mdi-format-list-bulleted"></i>
                                                                                </a>
                                                                            </li>
                                                                            <li class="nav-item" data-bs-placement="top" title="Grid">
                                                                                <a class="nav-link" href="blog-grid.html">
                                                                                    <i class="mdi mdi-view-grid-outline"></i>
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div-->
                                                            <!-- end row -->

                                                            <hr class="mb-4">

                                                            <div>
                                                                
                                                                    
                                                             <?php if (isset($posts) && mysqli_num_rows($posts) > 0) : ?>
                                                                <?php while ($post = mysqli_fetch_assoc($posts)) : ?>
                                                                    <div class="post-card mb-5">
                                                                        <h5>
                                                                            <a href="postOverview.php?id=<?= $post['id'] ?>" class="text-dark">
                                                                                <?= htmlspecialchars($post['title']) ?>
                                                                            </a>
                                                                        </h5>

                                                                        <p class="text-muted small">
                                                                            <i class="mdi mdi-calendar"></i> 
                                                                            <?= getRelativeTime($post['created_at']) ?>
                                                                        </p>

                                                                        <div class="position-relative mb-3">
                                                                            <img src="uploads/<?= htmlspecialchars($post['thumbnail']) ?>" class="img-fluid rounded img-thumbnail" alt="Post Image">
                                                                        </div>

                                                                        <ul class="list-inline border-bottom pb-2 font-size: 0.9rem;">
                                                                            <li class="list-inline-item me-3">
                                                                                <span class="badge bg-soft-primary text-primary">
                                                                                    <i class="bx bx-purchase-tag-alt me-1"></i>
                                                                                    <?= htmlspecialchars($post['category_title']) ?>
                                                                                </span>
                                                                            </li>

                                                                            <li class="list-inline-item me-3" onclick="handleInteraction(<?= $post['id'] ?>, 'like')" style="cursor:pointer"> 
                                                                                <i class="mdi mdi-thumb-up-outline text-success me-1"></i>
                                                                                <span id="likes-<?= $post['id'] ?>"><?= $post['likes'] ?? 0 ?></span>
                                                                            </li>

                                                                            <li class="list-inline-item me-3" onclick="handleInteraction(<?= $post['id'] ?>, 'dislike')" style="cursor:pointer">
                                                                                <i class="mdi mdi-thumb-down-outline text-danger me-1"></i>
                                                                                <span id="dislikes-<?= $post['id'] ?>"><?= $post['dislikes'] ?? 0 ?></span>
                                                                            </li>

                                                                            <li class="list-inline-item me-3">
                                                                                <a href="postOverview.php?id=<?= $post['id'] ?>#comments" class="text-muted">
                                                                                    <i class="bx bx-comment-dots me-1"></i>
                                                                                    <?= $post['comment_count'] ?? 0 ?> comments
                                                                                </a>
                                                                            </li>

                                                                            <li class="list-inline-item" onclick="copyShareLink(<?= $post['id'] ?>)" style="cursor:pointer">
                                                                                <i class="mdi mdi-share text-primary me-1"></i>
                                                                                <span id="shares-<?= $post['id'] ?>"><?= $post['share_count'] ?? 0 ?> shares </span>
                                                                            </li>
                                                                        </ul>

                                                                        <p class="text-secondary">
                                                                            <?= substr(strip_tags($post['body']), 0, 150) ?>...
                                                                        </p>

                                                                        <div class="mb-4">
                                                                            <a href="postOverview.php?id=<?= $post['id'] ?>" class="fw-bold text-primary">
                                                                                Read more →
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                <?php endwhile; ?>
                                                            <?php else : ?>
                                                                <div class="alert alert-info">No posts found in this category.</div>
                                                            <?php endif; ?>
                                                                                                                        
                                                            </div>
                                                               
                                                           

                                                            <hr class="my-5">

                                                            <div class="text-center">
                                                                <ul class="pagination justify-content-center pagination-rounded">
                                                                    <li class="page-item disabled">
                                                                        <a href="javascript: void(0);" class="page-link"><i class="mdi mdi-chevron-left"></i></a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a href="javascript: void(0);" class="page-link">1</a>
                                                                    </li>
                                                                    <li class="page-item active">
                                                                        <a href="javascript: void(0);" class="page-link">2</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a href="javascript: void(0);" class="page-link">3</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a href="javascript: void(0);" class="page-link">...</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a href="javascript: void(0);" class="page-link">10</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a href="javascript: void(0);" class="page-link"><i class="mdi mdi-chevron-right"></i></a>
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
                </div><!-- container-fluid -->
            </div><!-- End Page-content -->

          <?php include 'includes/footer.php' ?> 
            <!-- END scroll top -->
        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->
<script>
async function handleInteraction(postId, action) {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('action', action);

    try {
        const response = await fetch('controller/process_interaction.php', { method: 'POST', body: formData });
        const res = await response.json();
        
        if (res.status === 'success') {
            document.getElementById(`likes-${postId}`).innerText = res.data.likes;
            document.getElementById(`dislikes-${postId}`).innerText = res.data.dislikes;
            document.getElementById(`shares-${postId}`).innerText = res.data.shares;
        } else {
            alert(res.message);
        }
    } catch (err) { console.error("Interaction failed", err); }
}

function copyShareLink(postId) {
    const url = window.location.origin + '/post.php?id=' + postId ;
    navigator.clipboard.writeText(url).then(() => {
        alert("Link copied to clipboard!");
        handleInteraction(postId, 'share'); // Track the share
    });
}
</script>

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
    <!-- touchspin js -->
    <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>

    <!-- slick-carousel js -->
    <script src="assets/libs/slick-carousel/slick/slick.min.js"></script>

    <!-- Product overview init js -->
    <script src="assets/js/apps/product-overview.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>


</html>