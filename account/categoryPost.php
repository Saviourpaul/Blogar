<?php $pageTitle = 'all Post';
require 'includes/header.php';

ensurePostMediaSchema($connection);

$current_user_id = isset($_SESSION['user-id']) ? (int) $_SESSION['user-id'] : 0;
$category_id = isset($_GET['id']) ? (int) filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;
$category = null;
$posts = false;

$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$offset = ($current_page - 1) * $items_per_page;
$total_pages = 0;

if ($category_id > 0) {
    $category_query = "SELECT * FROM categories WHERE id = $category_id LIMIT 1";
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);

    $total_query = "SELECT COUNT(*) as total FROM posts WHERE category_id = $category_id";
    $total_result = mysqli_query($connection, $total_query);
    $total_rows = (int) (mysqli_fetch_assoc($total_result)['total'] ?? 0);
    $total_pages = (int) ceil($total_rows / $items_per_page);

    $query = "
        SELECT
            p.*,
            c.title AS category_title,
            u.username AS author_name,
            u.firstname AS author_firstname,
            u.lastname AS author_lastname,
            u.avatar AS author_avatar,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
            (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'like') AS likes,
            (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'dislike') AS dislikes,
            (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'share') AS share_count,
            EXISTS(SELECT 1 FROM post_interactions WHERE post_id = p.id AND user_id = $current_user_id AND interaction_type = 'like') AS user_liked,
            EXISTS(SELECT 1 FROM post_interactions WHERE post_id = p.id AND user_id = $current_user_id AND interaction_type = 'dislike') AS user_disliked
        FROM posts p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN users u ON u.id = p.author_id
        WHERE p.category_id = $category_id
        ORDER BY p.created_at DESC
        LIMIT $items_per_page OFFSET $offset
    ";
    $posts = mysqli_query($connection, $query);
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
                                <h4 class="mb-sm-0"><?= htmlspecialchars($category['title'] ?? 'Category posts') ?></h4>
                            </div>
                        </div>
                    </div>

                    <?php if ($posts && mysqli_num_rows($posts) === 0): ?>
                        <div class="alert alert-warning">No posts found for this category.</div>
                    <?php elseif (!$category): ?>
                        <div class="alert alert-warning">This category could not be found.</div>
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
                                                    <?php if ($posts && mysqli_num_rows($posts) > 0): ?>
                                                        <div class="d-grid gap-4">
                                                            <?php while ($post = mysqli_fetch_assoc($posts)): ?>
                                                                <?php
                                                                $authorDisplayName = trim(($post['author_firstname'] ?? '') . ' ' . ($post['author_lastname'] ?? '')) ?: ($post['author_name'] ?? 'Author');
                                                                $postExcerpt = mb_strimwidth(strip_tags((string) $post['body']), 0, 170, '...');
                                                                $postMedia = getPostMediaDetails($post);
                                                                $postCtaLabel = $postMedia['is_video'] ? 'Watch video' : 'Read article';
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
                                                                                <?= htmlspecialchars($post['category_title'] ?? ($category['title'] ?? 'General')) ?>
                                                                            </a>
                                                                            <span class="modern-post-chip">
                                                                                <i class="bx bx-comment-dots"></i>
                                                                                <?= (int) ($post['comment_count'] ?? 0) ?> comments
                                                                            </span>
                                                                            <?php if ($postMedia['is_video']): ?>
                                                                                <span class="modern-post-chip">
                                                                                    <i class="mdi mdi-play-circle-outline"></i>
                                                                                    <?= htmlspecialchars($postMedia['video_provider_label'], ENT_QUOTES, 'UTF-8') ?>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        </div>

                                                                        <h5 class="modern-post-card__title">
                                                                            <a href="postOverview?id=<?= (int) $post['id'] ?>">
                                                                                <?= htmlspecialchars($post['title']) ?>
                                                                            </a>
                                                                        </h5>

                                                                        <p class="modern-post-card__excerpt"><?= htmlspecialchars($postExcerpt) ?></p>

                                                                        <?= renderPostMediaPreview($post, 'postOverview?id=' . (int) $post['id'], $post['title']) ?>

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
                                                                                <?= htmlspecialchars($postCtaLabel, ENT_QUOTES, 'UTF-8') ?>
                                                                                <i class="mdi mdi-arrow-right"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </article>
                                                            <?php endwhile; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <hr class="my-5">

                                                    <?php if ($total_pages > 1): ?>
                                                        <div class="text-center mt-4 mb-5">
                                                            <ul class="pagination justify-content-center pagination-rounded">
                                                                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                                                    <a href="<?= ($current_page <= 1) ? 'javascript:void(0);' : '?id=' . $category_id . '&page=' . ($current_page - 1) ?>"
                                                                        class="page-link">
                                                                        <i class="mdi mdi-chevron-left"></i>
                                                                    </a>
                                                                </li>

                                                                <?php
                                                                $visible_pages = 2;

                                                                for ($i = 1; $i <= $total_pages; $i++):
                                                                    if ($i == 1 || $i == $total_pages || ($i >= $current_page - $visible_pages && $i <= $current_page + $visible_pages)):
                                                                        ?>
                                                                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                                                            <a href="?id=<?= $category_id ?>&page=<?= $i ?>" class="page-link"><?= $i ?></a>
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
                                                                    <a href="<?= ($current_page >= $total_pages) ? 'javascript:void(0);' : '?id=' . $category_id . '&page=' . ($current_page + 1) ?>"
                                                                        class="page-link">
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
