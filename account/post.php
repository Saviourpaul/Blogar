<?php $pageTitle = 'all Post';
require 'includes/header.php';

$current_user_id = isset($_SESSION['user-id']) ? (int) $_SESSION['user-id'] : 0;

$featured_query = "
    SELECT
        p.*,
        c.title AS category_title,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'like') AS likes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'dislike') AS dislikes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'share') AS share_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
        EXISTS(SELECT 1 FROM post_interactions WHERE post_id = p.id AND user_id = $current_user_id AND interaction_type = 'like') AS user_liked,
        EXISTS(SELECT 1 FROM post_interactions WHERE post_id = p.id AND user_id = $current_user_id AND interaction_type = 'dislike') AS user_disliked
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = 1
    LIMIT 1
";
$featured_result = mysqli_query($connection, $featured_query);
$featured = mysqli_fetch_assoc($featured_result);

if ($featured) {
    $author_id = $featured['author_id'];
    $author_query = "SELECT * FROM users WHERE id=" . intval($author_id);
    $author_result = mysqli_query($connection, $author_query);
    $author = mysqli_fetch_assoc($author_result);

    $featured_tagline = mb_strimwidth(strip_tags($featured['body']), 0, 150, '...');
}

$categories_result = mysqli_query($connection, "SELECT id, title FROM categories ORDER BY title ASC");
$all_categories = [];

if ($categories_result) {
    while ($category_item = mysqli_fetch_assoc($categories_result)) {
        $all_categories[] = $category_item;
    }
}

$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$offset = ($current_page - 1) * $items_per_page;

$total_query = "SELECT COUNT(*) as total FROM posts";
$total_result = mysqli_query($connection, $total_query);
$total_rows = (int) (mysqli_fetch_assoc($total_result)['total'] ?? 0);
$total_pages = (int) ceil($total_rows / $items_per_page);

$query = "
    SELECT
        posts.*,
        categories.title AS category_title,
        users.username AS author_name,
        users.firstname AS author_firstname,
        users.lastname AS author_lastname,
        users.avatar AS author_avatar,
        (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) AS comment_count,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = posts.id AND interaction_type = 'like') AS likes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = posts.id AND interaction_type = 'dislike') AS dislikes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = posts.id AND interaction_type = 'share') AS share_count,
        EXISTS(SELECT 1 FROM post_interactions WHERE post_id = posts.id AND user_id = $current_user_id AND interaction_type = 'like') AS user_liked,
        EXISTS(SELECT 1 FROM post_interactions WHERE post_id = posts.id AND user_id = $current_user_id AND interaction_type = 'dislike') AS user_disliked
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    LEFT JOIN users ON posts.author_id = users.id
    ORDER BY posts.created_at DESC
    LIMIT $items_per_page OFFSET $offset
";
$posts = mysqli_query($connection, $query);

if (!$posts) {
    error_log("Query Error: " . mysqli_error($connection));
    die("A database error occurred. Please try again later.");
}
?>

<body>
    <div id="layout-wrapper">
        <?= include 'includes/sidebar.php' ?>
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <div class="sidebar-left horizontal-sidebar"></div>
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Post Overview</h4>
                            </div>
                        </div>
                    </div>

                    <?php if ($featured): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm overflow-hidden modern-feed-shell">
                                    <div class="card-body p-0">
                                        <div class="row g-0">
                                            <div class="col-xl-5">
                                                <a href="postOverview?id=<?= (int) $featured['id'] ?>" class="d-block h-100 position-relative">
                                                    <img
                                                        src="account/uploads/<?= htmlspecialchars($featured['thumbnail']) ?>"
                                                        class="w-100 h-100 object-fit-cover"
                                                        style="min-height: 320px;"
                                                        alt="<?= htmlspecialchars($featured['title']) ?>">
                                                    <div class="position-absolute top-0 start-0 p-3">
                                                        <span class="badge rounded-pill bg-dark-subtle text-dark text-uppercase px-3 py-2">Featured Story</span>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-xl-7">
                                                <div class="p-4 p-xl-5 h-100 d-flex flex-column" style="background: linear-gradient(135deg, rgba(245,247,250,1) 0%, rgba(255,255,255,1) 100%);">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                                        <a href="categoryPost?id=<?= (int) $featured['category_id'] ?>" class="badge bg-soft-primary text-primary px-3 py-2">
                                                            <i class="bx bx-purchase-tag-alt me-1"></i><?= htmlspecialchars($featured['category_title'] ?? 'General') ?>
                                                        </a>
                                                        <span class="badge bg-soft-dark text-dark px-3 py-2"><?= htmlspecialchars(getRelativeTime($featured['created_at'])) ?></span>
                                                    </div>

                                                    <h2 class="fw-semibold mb-3" style="line-height: 1.2;"><?= htmlspecialchars($featured['title']) ?></h2>
                                                    <p class="text-muted fs-5 mb-4"><?= htmlspecialchars($featured_tagline) ?></p>

                                                    <a href="UserProfile?id=<?= (int) $featured['author_id'] ?>" class="d-inline-flex align-items-center gap-3 flex-wrap mb-4 text-decoration-none">
                                                        <div class="avatar avatar-md avatar-circle overflow-hidden">
                                                            <img src="account/uploads/<?= htmlspecialchars($author['avatar']) ?>" alt="Avatar Image" class="avatar-xs">
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-body"><?= htmlspecialchars("{$author['firstname']} {$author['lastname']}") ?></div>
                                                            <div class="text-muted small">View author profile</div>
                                                        </div>
                                                    </a>

                                                    <div class="row g-3 mb-4">
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="rounded-3 border bg-white p-3 h-100">
                                                                <div class="text-muted small mb-1">Likes</div>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <i id="like-icon-<?= (int) $featured['id'] ?>" class="mdi <?= !empty($featured['user_liked']) ? 'mdi-thumb-up text-success' : 'mdi-thumb-up-outline text-muted' ?>"></i>
                                                                    <span id="likes-<?= (int) $featured['id'] ?>" class="fw-semibold <?= !empty($featured['user_liked']) ? 'text-success' : 'text-muted' ?>"><?= (int) $featured['likes'] ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="rounded-3 border bg-white p-3 h-100">
                                                                <div class="text-muted small mb-1">Dislikes</div>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <i id="dislike-icon-<?= (int) $featured['id'] ?>" class="mdi <?= !empty($featured['user_disliked']) ? 'mdi-thumb-down text-danger' : 'mdi-thumb-down-outline text-muted' ?>"></i>
                                                                    <span id="dislikes-<?= (int) $featured['id'] ?>" class="fw-semibold <?= !empty($featured['user_disliked']) ? 'text-danger' : 'text-muted' ?>"><?= (int) $featured['dislikes'] ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="rounded-3 border bg-white p-3 h-100">
                                                                <div class="text-muted small mb-1">Comments</div>
                                                                <div class="fw-semibold text-body"><?= (int) $featured['comment_count'] ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="rounded-3 border bg-white p-3 h-100">
                                                                <div class="text-muted small mb-1">Shares</div>
                                                                <div class="fw-semibold text-body"><span id="shares-<?= (int) $featured['id'] ?>"><?= (int) $featured['share_count'] ?></span></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-2 mb-4">
                                                        <button type="button" class="btn btn-outline-success rounded-pill px-3" onclick="handleInteraction(<?= (int) $featured['id'] ?>, 'like')">
                                                            <i class="mdi mdi-thumb-up-outline me-1"></i>Like
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger rounded-pill px-3" onclick="handleInteraction(<?= (int) $featured['id'] ?>, 'dislike')">
                                                            <i class="mdi mdi-thumb-down-outline me-1"></i>Dislike
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick='openShareOptions(<?= (int) $featured['id'] ?>, <?= json_encode($featured["title"]) ?>)'>
                                                            <i class="mdi mdi-share-variant-outline me-1"></i>Share
                                                        </button>
                                                        <a href="postOverview?id=<?= (int) $featured['id'] ?>" class="btn btn-secondary rounded-pill px-4">
                                                            Read Story <i class="mdi mdi-arrow-right ms-1"></i>
                                                        </a>
                                                    </div>

                                                    <div class="mt-auto pt-2">
                                                        <div class="text-uppercase small fw-semibold text-muted mb-2">Browse Categories</div>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <?php foreach ($all_categories as $category_item): ?>
                                                                <a href="categoryPost?id=<?= (int) $category_item['id'] ?>" class="badge bg-light text-dark border px-3 py-2">
                                                                    <?= htmlspecialchars($category_item['title']) ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">No featured post available.</div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card modern-feed-shell">
                                <div class="card-header pb-0">
                                    <div class="nav nav-lines mb-0" id="card-tab-1" role="tablist">
                                        <a class="nav-item nav-link active" id="card-details-tab" data-bs-toggle="tab"
                                            href="#all-post" aria-selected="true" role="tab">
                                            All Post
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content p-4 p-lg-5">
                                        <div class="tab-pane active" id="all-post" role="tabpanel">
                                            <div class="row justify-content-center">
                                                <div class="col-xl-9">
                                                    <?php if (mysqli_num_rows($posts) > 0) : ?>
                                                        <div class="d-grid gap-4">
                                                            <?php while ($post = mysqli_fetch_assoc($posts)) : ?>
                                                                <?php
                                                                $authorDisplayName = trim(($post['author_firstname'] ?? '') . ' ' . ($post['author_lastname'] ?? '')) ?: ($post['author_name'] ?? 'Author');
                                                                $postExcerpt = mb_strimwidth(strip_tags((string) $post['body']), 0, 170, '...');
                                                                ?>
                                                                <article class="post-card modern-post-card">
                                                                    <div class="modern-post-card__body">
                                                                        <div class="modern-post-card__header">
                                                                            <a href="UserProfile?id=<?= (int) $post['author_id'] ?>" class="modern-post-card__author">
                                                                                <span class="avatar avatar-sm avatar-circle modern-post-card__avatar">
                                                                                    <?php if (!empty($post['author_avatar'])): ?>
                                                                                        <img src="account/uploads/<?= htmlspecialchars($post['author_avatar']) ?>" alt="<?= htmlspecialchars($authorDisplayName) ?>">
                                                                                    <?php else: ?>
                                                                                        <span class="avatar-title bg-soft-primary text-primary fw-semibold">
                                                                                            <?= htmlspecialchars(strtoupper(substr($authorDisplayName, 0, 1))) ?>
                                                                                        </span>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                                <span>
                                                                                    <span class="modern-post-card__author-name"><?= htmlspecialchars($authorDisplayName) ?></span>
                                                                                    <span class="modern-post-card__author-meta">Story author</span>
                                                                                </span>
                                                                            </a>
                                                                            <span class="modern-post-card__time">
                                                                                <i class="mdi mdi-clock-outline"></i>
                                                                                <?= htmlspecialchars(getRelativeTime($post['created_at'])) ?>
                                                                            </span>
                                                                        </div>

                                                                        <div class="modern-post-card__chips">
                                                                            <a href="categoryPost?id=<?= (int) $post['category_id'] ?>" class="modern-post-chip modern-post-chip--category">
                                                                                <i class="bx bx-purchase-tag-alt"></i>
                                                                                <?= htmlspecialchars($post['category_title'] ?? 'General') ?>
                                                                            </a>
                                                                            <span class="modern-post-chip">
                                                                                <i class="bx bx-comment-dots"></i>
                                                                                <?= (int) ($post['comment_count'] ?? 0) ?> comments
                                                                            </span>
                                                                        </div>

                                                                        <h5 class="modern-post-card__title">
                                                                            <a href="postOverview?id=<?= (int) $post['id'] ?>">
                                                                                <?= htmlspecialchars($post['title']) ?>
                                                                            </a>
                                                                        </h5>

                                                                        <p class="modern-post-card__excerpt"><?= htmlspecialchars($postExcerpt) ?></p>

                                                                        <?php if (!empty($post['thumbnail'])): ?>
                                                                            <a href="postOverview?id=<?= (int) $post['id'] ?>" class="modern-post-card__media">
                                                                                <img src="account/uploads/<?= htmlspecialchars($post['thumbnail']) ?>" class="modern-post-card__image" alt="<?= htmlspecialchars($post['title']) ?>">
                                                                                <span class="modern-post-card__media-badge">
                                                                                    <i class="mdi mdi-arrow-top-right"></i>
                                                                                    Open story
                                                                                </span>
                                                                            </a>
                                                                        <?php endif; ?>

                                                                        <div class="modern-post-card__footer">
                                                                            <div class="modern-post-card__actions">
                                                                                <button type="button" class="modern-post-action" onclick="handleInteraction(<?= (int) $post['id'] ?>, 'like')">
                                                                                    <i id="like-icon-<?= (int) $post['id'] ?>" class="mdi <?= !empty($post['user_liked']) ? 'mdi-thumb-up text-success' : 'mdi-thumb-up-outline text-muted' ?>"></i>
                                                                                    <span id="likes-<?= (int) $post['id'] ?>" class="<?= !empty($post['user_liked']) ? 'text-success' : 'text-muted' ?>"><?= (int) ($post['likes'] ?? 0) ?></span>
                                                                                </button>

                                                                                <button type="button" class="modern-post-action" onclick="handleInteraction(<?= (int) $post['id'] ?>, 'dislike')">
                                                                                    <i id="dislike-icon-<?= (int) $post['id'] ?>" class="mdi <?= !empty($post['user_disliked']) ? 'mdi-thumb-down text-danger' : 'mdi-thumb-down-outline text-muted' ?>"></i>
                                                                                    <span id="dislikes-<?= (int) $post['id'] ?>" class="<?= !empty($post['user_disliked']) ? 'text-danger' : 'text-muted' ?>"><?= (int) ($post['dislikes'] ?? 0) ?></span>
                                                                                </button>

                                                                                <a href="postOverview?id=<?= (int) $post['id'] ?>#comments" class="modern-post-action">
                                                                                    <i class="bx bx-comment-dots"></i>
                                                                                    <span><?= (int) ($post['comment_count'] ?? 0) ?></span>
                                                                                </a>

                                                                                <button type="button" class="modern-post-action" onclick='openShareOptions(<?= (int) $post['id'] ?>, <?= json_encode($post["title"]) ?>)'>
                                                                                    <i class="mdi mdi-share text-primary"></i>
                                                                                    <span id="shares-<?= (int) $post['id'] ?>"><?= (int) ($post['share_count'] ?? 0) ?></span>
                                                                                </button>
                                                                            </div>

                                                                            <a href="postOverview?id=<?= (int) $post['id'] ?>" class="modern-post-readmore">
                                                                                Read article
                                                                                <i class="mdi mdi-arrow-right"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </article>
                                                            <?php endwhile; ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <div class="alert alert-info mb-0">No posts found right now.</div>
                                                    <?php endif; ?>

                                                    <hr class="my-5">

                                                    <?php if ($total_pages > 1): ?>
                                                        <div class="text-center mt-4 mb-5">
                                                            <ul class="pagination justify-content-center pagination-rounded">
                                                                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                                                    <a href="<?= ($current_page <= 1) ? 'javascript:void(0);' : '?page=' . ($current_page - 1) ?>" class="page-link">
                                                                        <i class="mdi mdi-chevron-left"></i>
                                                                    </a>
                                                                </li>

                                                                <?php
                                                                $visible_pages = 2;

                                                                for ($i = 1; $i <= $total_pages; $i++):
                                                                    if ($i == 1 || $i == $total_pages || ($i >= $current_page - $visible_pages && $i <= $current_page + $visible_pages)):
                                                                        ?>
                                                                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                                                            <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                                                                        </li>
                                                                    <?php
                                                                    elseif ($i == $current_page - $visible_pages - 1 || $i == $current_page + $visible_pages + 1):
                                                                        ?>
                                                                        <li class="page-item disabled">
                                                                            <a href="javascript:void(0);" class="page-link">...</a>
                                                                        </li>
                                                                    <?php
                                                                    endif;
                                                                endfor;
                                                                ?>

                                                                <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                                                    <a href="<?= ($current_page >= $total_pages) ? 'javascript:void(0);' : '?page=' . ($current_page + 1) ?>" class="page-link">
                                                                        <i class="mdi mdi-chevron-right"></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
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

            <?php include 'includes/footer.php' ?>
        </div>
    </div>

    <script src="account/assets/js/like%26share.js"></script>
    <script src="account/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="account/assets/libs/jquery/jquery.min.js"></script>
    <script src="account/assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="account/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="account/assets/libs/eva-icons/eva.min.js"></script>
    <script src="account/assets/js/scroll-top.init.js"></script>
    <script src="account/assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
    <script src="account/assets/libs/slick-carousel/slick/slick.min.js"></script>
    <script src="account/assets/js/apps/product-overview.init.js"></script>
    <script src="account/assets/js/app.js"></script>
</body>

</html>
