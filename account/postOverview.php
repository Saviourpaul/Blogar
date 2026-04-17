<?php $pageTitle = 'all Post';
require 'includes/header.php';
require_once 'includes/helpers.php';


if (isset($_GET['id'])) {




    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM posts WHERE id=$id";
    $result = mysqli_query($connection, $query);

    $post = mysqli_fetch_assoc($result);
    $author_id = $post['author_id'];
    $author_query = "SELECT * FROM users WHERE id=$author_id";
    $author_result = mysqli_query($connection, $author_query);
    $author = mysqli_fetch_assoc($author_result);
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
    $current_user_id = (int)($_SESSION['user-id'] ?? 0);

    $commentHasUserId = dbColumnExists($connection, 'comments', 'user_id');
    $commentHasEditExpiresAt = dbColumnExists($connection, 'comments', 'edit_expires_at');
    $commentHasDeletedAt = dbColumnExists($connection, 'comments', 'deleted_at');
    $commentInteractionsEnabled = dbTableExists($connection, 'comment_interactions');
    $edit_window = max(1, (int) getSettingValue($connection, 'comment_edit_window', 15));

    $commentSelect = ['c.*'];
    $commentJoin = '';

    if ($commentHasUserId) {
        $commentSelect[] = 'u.username';
        $commentSelect[] = 'u.firstname';
        $commentSelect[] = 'u.lastname';
        $commentSelect[] = 'u.avatar';
        $commentJoin = ' LEFT JOIN users u ON c.user_id = u.id ';
    }

    if ($commentInteractionsEnabled) {
        $commentSelect[] = "(SELECT COUNT(*) FROM comment_interactions WHERE comment_id = c.id AND interaction_type = 'like') AS likes";
        $commentSelect[] = "(SELECT COUNT(*) FROM comment_interactions WHERE comment_id = c.id AND interaction_type = 'dislike') AS dislikes";
        $commentSelect[] = "(SELECT COUNT(*) FROM comment_interactions WHERE comment_id = c.id AND interaction_type = 'share') AS shares";
        $commentSelect[] = "EXISTS(SELECT 1 FROM comment_interactions WHERE comment_id = c.id AND user_id = ? AND interaction_type = 'like') AS user_liked";
        $commentSelect[] = "EXISTS(SELECT 1 FROM comment_interactions WHERE comment_id = c.id AND user_id = ? AND interaction_type = 'dislike') AS user_disliked";

        $stmt = $connection->prepare("
            SELECT " . implode(",\n                ", $commentSelect) . "
            FROM comments c
            $commentJoin
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->bind_param("iii", $current_user_id, $current_user_id, $post_id);
    } else {
        $commentSelect[] = '0 AS likes';
        $commentSelect[] = '0 AS dislikes';
        $commentSelect[] = '0 AS shares';
        $commentSelect[] = '0 AS user_liked';
        $commentSelect[] = '0 AS user_disliked';

        $stmt = $connection->prepare("
            SELECT " . implode(",\n                ", $commentSelect) . "
            FROM comments c
            $commentJoin
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->bind_param("i", $post_id);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $comments = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $commentDataMap = [];
    foreach ($comments as $comment) {
        $commentDataMap[(int) $comment['id']] = $comment;
    }

    $commentTree = [];
    foreach ($comments as $c) {
        $resolvedParentId = (int) ($c['parent_id'] ?? 0);
        if ($resolvedParentId > 0 && !isset($commentDataMap[$resolvedParentId])) {
            $resolvedParentId = 0;
            $c['parent_id'] = 0;
        }

        $commentTree[$resolvedParentId][] = $c;
    }
}

$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$current_user_id = $_SESSION['user-id'] ?? 0;



$query = "
    SELECT 
        p.*, 
        u.username AS author_name,
        c.title AS category_title,
        
        -- Get Specific Counts for this post
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'like') AS likes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'dislike') AS dislikes,
        (SELECT COUNT(*) FROM post_interactions WHERE post_id = p.id AND interaction_type = 'share') AS share_count,

        -- Check if current user has liked/disliked
        EXISTS(SELECT 1 FROM post_interactions WHERE post_id = p.id AND user_id = $current_user_id AND interaction_type = 'like') AS user_liked,
        EXISTS(SELECT 1 FROM post_interactions WHERE post_id = p.id AND user_id = $current_user_id AND interaction_type = 'dislike') AS user_disliked

    FROM posts p
    JOIN users u ON p.author_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = $post_id 
    LIMIT 1
";

$result = mysqli_query($connection, $query);
$post = mysqli_fetch_assoc($result);


$id = $_SESSION['user-id'] ?? null;
$is_comment_enabled = isSettingEnabled($connection, 'enable_comment', true);

if ($id) {
    $stmt = $connection->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");

    $stmt->bind_param("i", $id);

    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }

    $stmt->close();
} else {
    die("Unauthorized access.");
}

$authorDisplayName = trim(($author['firstname'] ?? '') . ' ' . ($author['lastname'] ?? ''));
if ($authorDisplayName === '') {
    $authorDisplayName = $post['author_name'] ?? 'Author';
}

$postSummary = mb_strimwidth(strip_tags((string) ($post['body'] ?? '')), 0, 220, '...');
$commentCount = isset($comments) ? count($comments) : 0;
$discussionCount = $commentCount;
$postPublishedDate = !empty($post['created_at']) ? date('M j, Y', strtotime($post['created_at'])) : '';

function formatCompactRelativeTime($datetime)
{
    $timestamp = strtotime((string) $datetime);
    if ($timestamp === false) {
        return 'just now';
    }

    $diff = time() - $timestamp;
    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . 'm';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . 'h';
    }
    if ($diff < 2592000) {
        return floor($diff / 86400) . 'd';
    }
    if ($diff < 31536000) {
        return floor($diff / 2592000) . 'mo';
    }

    return floor($diff / 31536000) . 'y';
}

function countThreadReplies(array $tree, int $commentId, array &$cache = [])
{
    if (isset($cache[$commentId])) {
        return $cache[$commentId];
    }

    $count = 0;
    foreach ($tree[$commentId] ?? [] as $childComment) {
        $childId = (int) ($childComment['id'] ?? 0);
        $count++;
        $count += countThreadReplies($tree, $childId, $cache);
    }

    $cache[$commentId] = $count;
    return $count;
}

$commentReplyCountCache = [];
?>

<style>
    .post-detail-shell {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 2rem;
        overflow: hidden;
        background:
            radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 28%),
            radial-gradient(circle at bottom right, rgba(25, 135, 84, 0.08), transparent 24%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 30px 72px rgba(15, 23, 42, 0.08);
    }

    .post-detail-shell .card-body {
        padding: 0;
    }

    .post-detail-main {
        padding: clamp(1rem, 2.5vw, 2rem);
    }

    .post-detail-header {
        margin-bottom: 1.5rem;
    }

    .post-detail-kicker {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .post-detail-title {
        color: #0f172a;
        font-size: clamp(2rem, 4vw, 3.3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.08;
        margin-bottom: 1rem;
    }

    .post-detail-summary {
        max-width: 52rem;
        margin: 0 auto;
        color: #64748b;
        font-size: 1.05rem;
        line-height: 1.8;
    }

    .post-detail-cover {
        position: relative;
        margin-bottom: 1.5rem;
        border-radius: 1.7rem;
        overflow: hidden;
        background: #e2e8f0;
        box-shadow: 0 26px 56px rgba(15, 23, 42, 0.16);
    }

    .post-detail-cover::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0) 55%, rgba(15, 23, 42, 0.22) 100%);
        pointer-events: none;
    }

    .post-detail-cover img {
        width: 100%;
        display: block;
        aspect-ratio: 16 / 9;
        object-fit: cover;
    }

    .post-detail-meta-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1.1rem 1.2rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1.4rem;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
    }

    .post-detail-author {
        display: inline-flex;
        align-items: center;
        gap: 0.95rem;
        min-width: 0;
        color: inherit;
        text-decoration: none;
    }

    .post-detail-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid rgba(13, 110, 253, 0.12);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.1);
        flex-shrink: 0;
    }

    .post-detail-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .post-detail-avatar .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .post-detail-author-name {
        display: block;
        color: #0f172a;
        font-weight: 800;
        line-height: 1.1;
    }

    .post-detail-author-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin-top: 0.3rem;
        color: #64748b;
        font-size: 0.88rem;
    }

    .post-detail-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .post-detail-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 999px;
        background: #ffffff;
        color: #475569;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    button.post-detail-action {
        appearance: none;
        cursor: pointer;
    }

    .post-detail-action:hover {
        border-color: rgba(13, 110, 253, 0.2);
        background: #f8fbff;
        transform: translateY(-1px);
    }

    .post-reading-surface,
    .post-discussion-shell {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1.6rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .post-reading-surface {
        padding: clamp(1.2rem, 2.6vw, 2rem);
        margin-top: 1.5rem;
    }

    .post-discussion-shell {
        margin-top: 1.5rem;
        padding: clamp(1rem, 2.4vw, 1.75rem);
    }

    .post-response-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1.4rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: none;
    }

    .threaded-comments {
        display: grid;
        gap: 1rem;
    }

    .threaded-comments--nested {
        position: relative;
        margin-top: 1rem;
        margin-left: clamp(1rem, 3vw, 2rem);
        padding-left: clamp(1.2rem, 2.5vw, 1.8rem);
        border-left: 3px solid rgba(13, 110, 253, 0.2);
    }

    .threaded-comments--nested::before {
        content: "";
        position: absolute;
        left: -3px;
        top: 0;
        bottom: 0;
        width: 3px;
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(13, 110, 253, 0.3) 0%, rgba(100, 116, 139, 0.15) 100%);
    }

    /* Depth levels for visual distinction */
    .threaded-comments--depth-1 {
        margin-left: clamp(1rem, 3vw, 2rem);
    }

    .threaded-comments--depth-2 {
        margin-left: clamp(2rem, 6vw, 4rem);
    }

    .threaded-comments--depth-3 {
        margin-left: clamp(3rem, 9vw, 6rem);
    }

    .threaded-comments--depth-4-plus {
        margin-left: clamp(4rem, 12vw, 8rem);
    }

    .thread-comment-item {
        position: relative;
    }

    .threaded-comments--nested > .thread-comment-item::before {
        content: "";
        position: absolute;
        left: calc(clamp(1.2rem, 2.5vw, 1.8rem) * -1 - 3px);
        top: 1.6rem;
        width: clamp(0.8rem, 2vw, 1.2rem);
        height: 2px;
        border-radius: 999px;
        background: rgba(13, 110, 253, 0.25);
    }

    .thread-comment {
        position: relative;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1.25rem;
        padding: 1rem 1.1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .thread-comment--reply {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    }

    .thread-comment__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.9rem;
        margin-bottom: 0.8rem;
    }

    .thread-comment__author {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        min-width: 0;
    }

    .thread-comment__avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid rgba(13, 110, 253, 0.12);
        background: #eaf2ff;
        flex-shrink: 0;
    }

    .thread-comment__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thread-comment__avatar .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .thread-comment__author-name {
        display: block;
        color: #0f172a;
        font-weight: 700;
        line-height: 1.15;
    }

    .thread-comment__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 0.25rem;
        color: #64748b;
        font-size: 0.82rem;
    }

    .thread-comment__parent-link {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: #0d6efd;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
    }

    .thread-comment__parent-link:hover {
        background: rgba(13, 110, 253, 0.08);
        color: #0b5ed7;
        text-decoration: underline;
    }

    .thread-comment__replying {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.7rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .thread-comment__body {
        color: #334155;
        line-height: 1.75;
        word-break: break-word;
    }

    .thread-comment__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: space-between;
        margin-top: 1rem;
        padding-top: 0.9rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }

    .comment-interactions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
        align-items: center;
    }

    .comment-interaction-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border: none;
        border-radius: 999px;
        background: transparent;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .comment-interaction-btn:hover {
        background: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
    }

    .comment-interaction-btn.active {
        background: rgba(13, 110, 253, 0.12);
        color: #0d6efd;
    }

    .comment-interaction-btn.dislike.active {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.12);
    }

    .comment-action-menu {
        display: flex;
        gap: 0.3rem;
    }

    .comment-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.4rem 0.6rem;
        border: none;
        border-radius: 6px;
        background: transparent;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .comment-action-btn:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .comment-action-btn.edit:hover {
        color: #0d6efd;
    }

    .comment-action-btn.delete:hover {
        color: #dc3545;
    }

    .thread-comment__edited {
        display: inline-block;
        margin-left: 0.5rem;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        background: rgba(255, 193, 7, 0.1);
        color: #ff9800;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .comment-edit-form {
        display: none;
        margin-top: 0.8rem;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }

    .comment-edit-form.active {
        display: block;
    }

    .comment-edit-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid rgba(13, 110, 253, 0.2);
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.9rem;
        resize: vertical;
        min-height: 80px;
    }

    .comment-edit-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.6rem;
    }

    .comment-edit-save {
        padding: 0.5rem 1rem;
        background: #0d6efd;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .comment-edit-save:hover {
        background: #0b5ed7;
    }

    .comment-edit-cancel {
        padding: 0.5rem 1rem;
        background: #e9ecef;
        color: #495057;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .comment-edit-cancel:hover {
        background: #dee2e6;
    }

    /* Thread highlighting */
    .thread-comment-item.highlight-parent .thread-comment {
        animation: highlight-pulse 0.6s ease-in-out;
        background: linear-gradient(180deg, #fffacd 0%, #fffbea 100%);
        border-color: rgba(255, 193, 7, 0.3);
    }

    @keyframes highlight-pulse {
        0% {
            background-color: #ffff99;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.6);
        }
        100% {
            background-color: transparent;
            box-shadow: none;
        }
    }

    /* Thread level indication */
    .thread-comment-item[data-thread-level="0"] .thread-comment {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .thread-comment-item[data-thread-level="1"] .thread-comment {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    }

    .thread-comment-item[data-thread-level="2"] .thread-comment {
        background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
    }

    .thread-comment-item[data-thread-level="3"] .thread-comment {
        background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%);
    }

    @media (max-width: 575.98px) {
        .thread-comment {
            padding: 0.9rem;
        }

        .thread-comment__header {
            flex-direction: column;
            align-items: stretch;
        }

        .thread-comment__actions .btn {
            width: 100%;
        }
    }

    .rich-post-content {
        color: #334155;
    }

    .rich-post-content > *:last-child {
        margin-bottom: 0;
    }

    .rich-post-content p,
    .rich-post-content ul,
    .rich-post-content ol,
    .rich-post-content blockquote,
    .rich-post-content pre {
        margin-bottom: 1rem;
    }

    .rich-post-content p {
        font-size: 1.02rem;
        line-height: 1.95;
    }

    .rich-post-content ul,
    .rich-post-content ol {
        padding-left: 1.5rem;
    }

    .rich-post-content a {
        color: #0d6efd;
        text-decoration: underline;
        word-break: break-word;
    }

    .rich-post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 1rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        margin: 0.5rem 0 1.25rem;
    }

    .rich-post-content blockquote {
        border-left: 4px solid #d0d7de;
        margin-left: 0;
        padding: 0.25rem 0 0.25rem 1rem;
        color: #64748b;
        font-style: italic;
    }

    .rich-post-content pre {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1rem;
        overflow-x: auto;
    }

    .rich-post-content h2,
    .rich-post-content h3,
    .rich-post-content h4 {
        color: #0f172a;
        font-weight: 800;
        margin-bottom: 0.8rem;
        margin-top: 1.6rem;
    }

    .rich-post-content h2:first-child,
    .rich-post-content h3:first-child,
    .rich-post-content h4:first-child {
        margin-top: 0;
    }

    @media (max-width: 767.98px) {
        .post-detail-meta-bar {
            padding: 1rem;
        }

        .post-detail-title {
            font-size: clamp(1.7rem, 8vw, 2.4rem);
        }

        .post-detail-action {
            width: calc(50% - 0.35rem);
            justify-content: center;
        }
    }

    @media (max-width: 575.98px) {
        .post-detail-action {
            width: 100%;
        }
    }
</style>
<link rel="stylesheet" href="account/assets/css/comment-thread.css">

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
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Post Details</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card post-detail-shell">
                                <div class="card-body">
                                    <div class="post-detail-main">
                                        <div class="row justify-content-center">
                                            <div class="col-xxl-9 col-xl-10">
                                                <div class="post-detail-header text-center">
                                                    <div class="post-detail-kicker">
                                                        <a href="categoryPost?id=<?= (int) $category['id'] ?>" class="modern-post-chip modern-post-chip--category">
                                                            <i class="bx bx-purchase-tag-alt"></i>
                                                            <?= htmlspecialchars($category['title']) ?>
                                                        </a>
                                                        <span class="modern-post-chip">
                                                            <i class="mdi mdi-clock-outline"></i>
                                                            <?= htmlspecialchars(getRelativeTime($post['created_at'])) ?>
                                                        </span>
                                                        <span class="modern-post-chip">
                                                            <i class="bx bx-comment-dots"></i>
                                                            <?= $commentCount ?> comments
                                                        </span>
                                                    </div>
                                                    <h1 class="post-detail-title"><?= htmlspecialchars($post['title']) ?></h1>
                                                    <?php if ($postSummary !== ''): ?>
                                                        <p class="post-detail-summary"><?= htmlspecialchars($postSummary) ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (!empty($post['thumbnail'])): ?>
                                                    <div class="post-detail-cover">
                                                        <img src="account/uploads/<?= htmlspecialchars($post['thumbnail']) ?>"
                                                            alt="<?= htmlspecialchars($post['title']) ?>">
                                                    </div>
                                                <?php endif; ?>

                                                <div class="post-detail-meta-bar">
                                                    <a href="UserProfile?id=<?= (int) $author['id'] ?>" class="post-detail-author">
                                                        <span class="post-detail-avatar">
                                                            <?php if (!empty($author['avatar'])): ?>
                                                                <img src="account/uploads/<?= htmlspecialchars($author['avatar']) ?>" alt="<?= htmlspecialchars($authorDisplayName) ?>">
                                                            <?php else: ?>
                                                                <span class="avatar-title bg-soft-primary text-primary fw-semibold">
                                                                    <?= htmlspecialchars(strtoupper(substr($authorDisplayName, 0, 1))) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span>
                                                            <span class="post-detail-author-name"><?= htmlspecialchars($authorDisplayName) ?></span>
                                                            <span class="post-detail-author-meta">
                                                                <span><i class="mdi mdi-calendar-range-outline me-1"></i><?= htmlspecialchars($postPublishedDate) ?></span>
                                                                <span><i class="mdi mdi-history me-1"></i><?= htmlspecialchars(getRelativeTime($post['created_at'])) ?></span>
                                                            </span>
                                                        </span>
                                                    </a>

                                                    <div class="post-detail-actions">
                                                        <button type="button" class="post-detail-action" onclick="handleInteraction(<?= (int) $post['id'] ?>, 'like')">
                                                            <i id="like-icon-<?= (int) $post['id'] ?>" class="mdi <?= $post['user_liked'] ? 'mdi-thumb-up text-success' : 'mdi-thumb-up-outline text-muted' ?>"></i>
                                                            <span id="likes-<?= (int) $post['id'] ?>" class="<?= $post['user_liked'] ? 'text-success' : 'text-muted' ?>"><?= (int) $post['likes'] ?></span>
                                                        </button>

                                                        <button type="button" class="post-detail-action" onclick="handleInteraction(<?= (int) $post['id'] ?>, 'dislike')">
                                                            <i id="dislike-icon-<?= (int) $post['id'] ?>" class="mdi <?= $post['user_disliked'] ? 'mdi-thumb-down text-danger' : 'mdi-thumb-down-outline text-muted' ?>"></i>
                                                            <span id="dislikes-<?= (int) $post['id'] ?>" class="<?= $post['user_disliked'] ? 'text-danger' : 'text-muted' ?>"><?= (int) $post['dislikes'] ?></span>
                                                        </button>

                                                        <button type="button" class="post-detail-action" onclick='openShareOptions(<?= (int) $post['id'] ?>, <?= json_encode($post["title"]) ?>)'>
                                                            <i class="mdi mdi-share text-primary"></i>
                                                            <span id="shares-<?= (int) $post['id'] ?>"><?= (int) $post['share_count'] ?></span>
                                                            <span>shares</span>
                                                        </button>

                                                        <a href="#comments" class="post-detail-action">
                                                            <i class="bx bx-comment-dots text-muted"></i>
                                                            <span><?= $commentCount ?></span>
                                                            <span>comments</span>
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="post-content mt-4">
                                                    <div class="post-reading-surface">
                                                        <div class="rich-post-content font-size-15 lh-base mb-0">
                                                            <?= $post['body'] ?>
                                                        </div>
                                                    </div>

                                                    <?php include 'includes/comment-thread-overview.php'; ?>
                                                    <?php if (false): ?>
                                                    <div class="post-discussion-shell" id="comments">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                                                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                                                <i class="bx bx-message-dots text-primary me-2 fs-4"></i>
                                                                Discussion
                                                                <span class="badge bg-soft-primary text-primary ms-2 rounded-pill">
                                                                    <?= $discussionCount ?>
                                                                </span>
                                                            </h5>

                                                            <div id="replying-to-container"
                                                                class="bg-light rounded-pill px-3 py-1 border border-primary border-opacity-25 align-items-center"
                                                                style="display:none; max-width: 100%;">
                                                                <span class="text-muted small me-2 text-truncate d-inline-block align-middle"
                                                                    style="max-width: 120px;">
                                                                    Replying to <span id="reply-name" class="fw-bold text-primary"></span>
                                                                </span>
                                                                <button type="button"
                                                                    class="btn btn-sm text-danger p-1 border-0 ms-1"
                                                                    onclick="cancelReply()"
                                                                    aria-label="Cancel Reply">
                                                                    <i class="bx bx-x fs-5 align-middle"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                            <?php
                                                            function renderComments($tree, $parent_id = 0, $level = 0, $parentDisplayName = '', $current_user_id = 0, $edit_window = 15, $commentData = [])
                                                            {
                                                                if (!isset($tree[$parent_id])) {
                                                                    return;
                                                                }

                                                                // Determine CSS class for depth level
                                                                $depthClass = '';
                                                                if ($level === 0) {
                                                                    $depthClass = '';
                                                                } elseif ($level === 1) {
                                                                    $depthClass = 'threaded-comments--depth-1';
                                                                } elseif ($level === 2) {
                                                                    $depthClass = 'threaded-comments--depth-2';
                                                                } elseif ($level === 3) {
                                                                    $depthClass = 'threaded-comments--depth-3';
                                                                } else {
                                                                    $depthClass = 'threaded-comments--depth-4-plus';
                                                                }

                                                                $containerClass = $level > 0 ? "threaded-comments threaded-comments--nested $depthClass" : 'threaded-comments';
                                                                ?>
                                                                <div class="<?= $containerClass ?>" data-thread-level="<?= $level ?>">
                                                                    <?php foreach ($tree[$parent_id] as $c): ?>
                                                                        <?php
                                                                        $id = (int) $c['id'];
                                                                        $rawName = trim(($c['firstname'] ?? '') . ' ' . ($c['lastname'] ?? ''));
                                                                        if ($rawName === '') {
                                                                            $rawName = $c['username'] ?? $c['name'] ?? 'Member';
                                                                        }

                                                                        $name = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
                                                                        $message = nl2br(htmlspecialchars($c['message'], ENT_QUOTES, 'UTF-8'));
                                                                        $date = htmlspecialchars(getRelativeTime($c['created_at']), ENT_QUOTES, 'UTF-8');
                                                                        $isReply = ($level > 0);
                                                                        $childCount = isset($tree[$id]) ? count($tree[$id]) : 0;
                                                                        $avatarSrc = '';
                                                                        $isCommentAuthor = ((int)$c['user_id'] === $current_user_id);
                                                                        $isEdited = isset($c['is_edited']) && $c['is_edited'];
                                                                        $editedAt = isset($c['edited_at']) ? $c['edited_at'] : null;

                                                                        // Check if comment can still be edited
                                                                        $commentAgeMinutes = (time() - strtotime($c['created_at'])) / 60;
                                                                        $canEdit = $isCommentAuthor && $commentAgeMinutes <= $edit_window;

                                                                        // Get parent comment info for jump link
                                                                        $parentCommentId = (int)($c['parent_id'] ?? 0);
                                                                        $parentCommentName = '';
                                                                        if ($parentCommentId > 0 && isset($commentData[$parentCommentId])) {
                                                                            $parentInfo = $commentData[$parentCommentId];
                                                                            $parentCommentName = trim(($parentInfo['firstname'] ?? '') . ' ' . ($parentInfo['lastname'] ?? ''));
                                                                            if ($parentCommentName === '') {
                                                                                $parentCommentName = $parentInfo['username'] ?? $parentInfo['name'] ?? 'Member';
                                                                            }
                                                                        }

                                                                        if (!empty($c['avatar'])) {
                                                                            $avatarValue = (string) $c['avatar'];
                                                                            if (preg_match('/^(https?:)?\/\//i', $avatarValue) || strpos($avatarValue, 'account/uploads/') === 0) {
                                                                                $avatarSrc = $avatarValue;
                                                                            } else {
                                                                                $avatarSrc = 'account/uploads/' . ltrim($avatarValue, '/');
                                                                            }
                                                                        }

                                                                        // Get interaction counts
                                                                        $likes = (int)($c['likes'] ?? 0);
                                                                        $dislikes = (int)($c['dislikes'] ?? 0);
                                                                        $shares = (int)($c['shares'] ?? 0);
                                                                        $userLiked = isset($c['user_liked']) && $c['user_liked'];
                                                                        $userDisliked = isset($c['user_disliked']) && $c['user_disliked'];
                                                                        ?>

                                                                        <div class="thread-comment-item" id="comment-<?= $id ?>" data-comment-id="<?= $id ?>" data-thread-level="<?= $level ?>">
                                                                            <article class="thread-comment <?= $isReply ? 'thread-comment--reply' : 'thread-comment--root' ?>">
                                                                                <div class="thread-comment__header">
                                                                                    <div class="thread-comment__author">
                                                                                        <div class="thread-comment__avatar">
                                                                                            <?php if ($avatarSrc !== ''): ?>
                                                                                                <img src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                                                                    alt="<?= $name ?>'s avatar">
                                                                                            <?php else: ?>
                                                                                                <div class="avatar-title bg-soft-primary text-primary fw-bold">
                                                                                                    <?= htmlspecialchars(strtoupper(substr($rawName, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                                                                                </div>
                                                                                            <?php endif; ?>
                                                                                        </div>

                                                                                        <div class="min-vw-0">
                                                                                            <span class="thread-comment__author-name"><?= $name ?></span>
                                                                                            <div class="thread-comment__meta">
                                                                                                <span><i class="bx bx-time-five me-1"></i><?= $date ?></span>
                                                                                                <?php if ($isEdited && $editedAt): ?>
                                                                                                    <span class="thread-comment__edited" title="Edited on <?= htmlspecialchars(date('M j, Y \a\t g:i A', strtotime($editedAt)), ENT_QUOTES, 'UTF-8') ?>">
                                                                                                        <i class="bx bx-edit-alt"></i> edited
                                                                                                    </span>
                                                                                                <?php endif; ?>
                                                                                                <?php if ($childCount > 0): ?>
                                                                                                    <span><i class="mdi mdi-source-branch me-1"></i><?= $childCount ?> repl<?= $childCount === 1 ? 'y' : 'ies' ?></span>
                                                                                                <?php endif; ?>
                                                                                                <!-- Jump to parent link -->
                                                                                                <?php if ($parentCommentId > 0): ?>
                                                                                                    <a href="#comment-<?= $parentCommentId ?>" class="thread-comment__parent-link" title="Jump to parent comment">
                                                                                                        <i class="bx bx-reply-all"></i> Parent
                                                                                                    </a>
                                                                                                <?php endif; ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <?php if ($isReply && $parentDisplayName !== ''): ?>
                                                                                    <div class="thread-comment__replying">
                                                                                        <i class="mdi mdi-reply"></i>
                                                                                        Replying to <?= htmlspecialchars($parentDisplayName, ENT_QUOTES, 'UTF-8') ?>
                                                                                    </div>
                                                                                <?php endif; ?>

                                                                                <div class="thread-comment__body" id="comment-content-<?= $id ?>">
                                                                                    <?= $message ?>
                                                                                </div>

                                                                                <!-- Comment Edit Form -->
                                                                                <div class="comment-edit-form" id="comment-edit-form-<?= $id ?>">
                                                                                    <textarea class="comment-edit-textarea" id="comment-edit-input-<?= $id ?>" placeholder="Edit your comment..."><?= htmlspecialchars($c['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                                                                    <div class="comment-edit-actions">
                                                                                        <button class="comment-edit-save" onclick="saveCommentEdit(<?= $id ?>)">
                                                                                            <i class="bx bx-check"></i> Save
                                                                                        </button>
                                                                                        <button class="comment-edit-cancel" onclick="cancelCommentEdit(<?= $id ?>)">
                                                                                            <i class="bx bx-x"></i> Cancel
                                                                                        </button>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="thread-comment__actions">
                                                                                    <div class="comment-interactions">
                                                                                        <button type="button" class="comment-interaction-btn like <?= $userLiked ? 'active' : '' ?>" 
                                                                                            onclick="handleCommentInteraction(<?= $id ?>, 'like')"
                                                                                            title="Like this comment">
                                                                                            <i class="mdi mdi-thumb-up-outline"></i>
                                                                                            <span id="comment-likes-<?= $id ?>"><?= $likes ?></span>
                                                                                        </button>

                                                                                        <button type="button" class="comment-interaction-btn dislike <?= $userDisliked ? 'active' : '' ?>" 
                                                                                            onclick="handleCommentInteraction(<?= $id ?>, 'dislike')"
                                                                                            title="Dislike this comment">
                                                                                            <i class="mdi mdi-thumb-down-outline"></i>
                                                                                            <span id="comment-dislikes-<?= $id ?>"><?= $dislikes ?></span>
                                                                                        </button>

                                                                                        <button type="button" class="comment-interaction-btn share" 
                                                                                            onclick="handleCommentInteraction(<?= $id ?>, 'share')"
                                                                                            title="Share this comment">
                                                                                            <i class="mdi mdi-share-outline"></i>
                                                                                            <span id="comment-shares-<?= $id ?>"><?= $shares ?></span>
                                                                                        </button>

                                                                                        <button type="button" class="comment-interaction-btn reply" 
                                                                                            onclick='setReplyId(<?= $id ?>, <?= json_encode($rawName) ?>)'
                                                                                            title="Reply to this comment">
                                                                                            <i class="mdi mdi-reply"></i>
                                                                                            Reply
                                                                                        </button>
                                                                                    </div>

                                                                                    <?php if ($isCommentAuthor): ?>
                                                                                        <div class="comment-action-menu">
                                                                                            <?php if ($canEdit): ?>
                                                                                                <button type="button" class="comment-action-btn edit" 
                                                                                                    onclick="startCommentEdit(<?= $id ?>)"
                                                                                                    title="Edit this comment (<?= $edit_window ?> min window)">
                                                                                                    <i class="bx bx-edit-alt"></i> Edit
                                                                                                </button>
                                                                                            <?php else: ?>
                                                                                                <span class="comment-action-btn" title="Editing window expired" style="opacity: 0.5; cursor: not-allowed;">
                                                                                                    <i class="bx bx-edit-alt"></i> Edit
                                                                                                </span>
                                                                                            <?php endif; ?>

                                                                                            <button type="button" class="comment-action-btn delete" 
                                                                                                onclick="deleteComment(<?= $id ?>)"
                                                                                                title="Delete this comment">
                                                                                                <i class="bx bx-trash-alt"></i> Delete
                                                                                            </button>
                                                                                        </div>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </article>

                                                                            <?php renderComments($tree, $id, $level + 1, $rawName, $current_user_id, $edit_window, $commentData); ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <?php
                                                            }
                                                            ?>

                                                            <div class="comment-area mb-4 mb-md-5">
                                                                <?php if (empty($commentTree)): ?>
                                                                    <div
                                                                        class="text-center py-4 py-md-5 bg-light rounded-3 border border-dashed border-secondary border-opacity-25 px-3">
                                                                        <i
                                                                            class="bx bx-conversation display-4 text-muted mb-3 opacity-50"></i>
                                                                        <h5 class="text-muted fw-normal fs-6 fs-md-5">No
                                                                            comments yet</h5>
                                                                        <p class="text-muted small mb-0">Be the first to
                                                                            start the conversation!</p>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="comments-list overflow-hidden">
                                                                        <?php 
                                                                        // Build comment data map for parent lookup
                                                                        $commentDataMap = [];
                                                                        foreach ($comments as $c) {
                                                                            $commentDataMap[(int)$c['id']] = $c;
                                                                        }
                                                                        
                                                                        $edit_window = (int)getSettingValue($connection, 'comment_edit_window', 15);
                                                                        renderComments($commentTree, 0, 0, '', $current_user_id, $edit_window, $commentDataMap); 
                                                                        ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="card post-response-card">
                                                                <div class="card-body p-3 p-md-4">
                                                                    <h5 class="card-title mb-3 mb-md-4 fw-bold text-dark fs-5 fs-md-4"
                                                                        id="form-title">Leave a Response</h5>

                                                                    <?php if ($is_comment_enabled): ?>
                                                                        <form action="comments" method="post"
                                                                            id="comment-form">
                                                                            <input type="hidden" name="csrf_token"
                                                                                value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                                                            <input type="hidden" name="post_id"
                                                                                value="<?= (int) $post['id'] ?>">
                                                                            <input type="hidden" name="parent_id"
                                                                                id="parent_id" value="0">

                                                                            <div class="d-flex align-items-center mb-3">
                                                                                <div
                                                                                    class="avatar-sm me-2 me-md-3 flex-shrink-0">
                                                                                    <div class="avatar-title rounded-circle bg-primary text-white d-flex align-items-center justify-content-center comment-avatar"
                                                                                        style="width: 40px; height: 40px; font-size: 1rem;">
                                                                                        <?= strtoupper(substr($user['firstname'], 0, 1)) ?>
                                                                                    </div>
                                                                                </div>
                                                                                <input type="text"
                                                                                    class="form-control bg-light border-0 fw-medium text-dark px-3 py-2"
                                                                                    name="name"
                                                                                    value="<?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                    id="commentname-input" readonly>
                                                                            </div>

                                                                            <div class="mb-3">
                                                                                <textarea
                                                                                    class="form-control custom-comment-input bg-light border-0 p-3 shadow-none"
                                                                                    name="message" id="commentmessage-input"
                                                                                    placeholder="What are your thoughts?"
                                                                                    rows="4"
                                                                                    style="resize: vertical; border-radius: 12px; transition: all 0.2s ease-in-out;"
                                                                                    required></textarea>
                                                                            </div>

                                                                            <div
                                                                                class="d-grid d-sm-flex justify-content-sm-end mt-4">
                                                                                <button type="submit" name="submit"
                                                                                    class="btn btn-primary px-4 py-2 shadow-sm rounded-pill transition-all fw-medium">
                                                                                    <i class="mdi mdi-send me-2"></i>
                                                                                    Publish Comment
                                                                                </button>
                                                                            </div>
                                                                        </form>
                                                                    <?php else: ?>
                                                                        <div class="alert alert-warning mb-0">
                                                                            Comments are currently disabled.
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
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




                                    <?php if (isset($_SESSION['comments-success'])): ?>

                                        <script>

                                            document.addEventListener("DOMContentLoaded", function () {

                                                Swal.fire({
                                                    icon: "success",
                                                    title: "Success",
                                                    text: <?= json_encode((string) $_SESSION['comments-success']) ?>,
                                                    confirmButtonColor: "#3085d6"
                                                });

                                            });

                                        </script>

                                        <?php unset($_SESSION['comments-success']); ?>


                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['comments-error'])): ?>

                                        <script>

                                            document.addEventListener("DOMContentLoaded", function () {

                                                Swal.fire({
                                                    icon: "error",
                                                    title: "Error",
                                                    text: <?= json_encode((string) $_SESSION['comments-error']) ?>,
                                                    confirmButtonColor: "red"
                                                });

                                            });

                                        </script>

                                        <?php unset($_SESSION['comments-error']); ?>


                                    <?php endif; ?>





                                    <script src="account/assets/js/comment-thread.js"></script>
                                    <?php if (false): ?>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            const parentIdInput = document.getElementById("parent_id");
                                            const replyContainer = document.getElementById("replying-to-container");
                                            const replyName = document.getElementById("reply-name");
                                            const formTitle = document.getElementById("form-title");
                                            const commentForm = document.getElementById("comment-form");
                                            const commentMessageInput = document.getElementById("commentmessage-input");

                                            function resetReplyState() {
                                                if (parentIdInput) {
                                                    parentIdInput.value = "0";
                                                }

                                                if (replyName) {
                                                    replyName.textContent = "";
                                                }

                                                if (replyContainer) {
                                                    replyContainer.style.display = "none";
                                                    replyContainer.classList.remove("d-inline-flex");
                                                }

                                                if (formTitle) {
                                                    formTitle.textContent = "Leave a Response";
                                                }
                                            }

                                            window.setReplyId = function (commentId, commenterName) {
                                                if (parentIdInput) {
                                                    parentIdInput.value = String(commentId);
                                                }

                                                if (replyName) {
                                                    replyName.textContent = commenterName || "this comment";
                                                }

                                                if (replyContainer) {
                                                    replyContainer.style.display = "inline-flex";
                                                    replyContainer.classList.add("d-inline-flex");
                                                }

                                                if (formTitle) {
                                                    formTitle.textContent = "Reply to Comment";
                                                }

                                                if (commentMessageInput) {
                                                    commentMessageInput.focus();
                                                }
                                            };

                                            window.cancelReply = function () {
                                                resetReplyState();
                                            };

                                            if (commentForm) {
                                                commentForm.addEventListener("submit", function () {
                                                    resetReplyState();
                                                });
                                            }

                                            // Smooth scroll for parent comment links
                                            document.querySelectorAll('.thread-comment__parent-link').forEach(link => {
                                                link.addEventListener('click', function(e) {
                                                    e.preventDefault();
                                                    const href = this.getAttribute('href');
                                                    const commentId = href.substring(1);
                                                    const targetElement = document.getElementById(commentId);
                                                    
                                                    if (targetElement) {
                                                        targetElement.scrollIntoView({ 
                                                            behavior: 'smooth',
                                                            block: 'center'
                                                        });
                                                        
                                                        // Highlight parent comment
                                                        targetElement.classList.add('highlight-parent');
                                                        setTimeout(() => {
                                                            targetElement.classList.remove('highlight-parent');
                                                        }, 3000);
                                                    }
                                                });
                                            });
                                        });

                                        // Comment Interaction Handler
                                        async function handleCommentInteraction(commentId, action) {
                                            try {
                                                const response = await fetch('account/actions/comment-interactions.php', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/x-www-form-urlencoded',
                                                    },
                                                    body: `comment_id=${commentId}&action=${action}`
                                                });

                                                const data = await response.json();

                                                if (data.status === 'success') {
                                                    updateCommentInteractionUI(commentId, action, data);
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error',
                                                        text: data.message || 'Failed to process interaction',
                                                        confirmButtonColor: '#3085d6'
                                                    });
                                                }
                                            } catch (error) {
                                                console.error('Error:', error);
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: 'Failed to process interaction',
                                                    confirmButtonColor: '#3085d6'
                                                });
                                            }
                                        }

                                        function updateCommentInteractionUI(commentId, action, data) {
                                            // Update counts
                                            const likesSpan = document.getElementById(`comment-likes-${commentId}`);
                                            const dislikesSpan = document.getElementById(`comment-dislikes-${commentId}`);
                                            const sharesSpan = document.getElementById(`comment-shares-${commentId}`);

                                            if (likesSpan) likesSpan.textContent = data.data.likes;
                                            if (dislikesSpan) dislikesSpan.textContent = data.data.dislikes;
                                            if (sharesSpan) sharesSpan.textContent = data.data.shares;

                                            // Update button states
                                            const likeBtn = document.querySelector(`#comment-likes-${commentId}`).closest('.like');
                                            const dislikeBtn = document.querySelector(`#comment-dislikes-${commentId}`).closest('.dislike');

                                            if (likeBtn && dislikeBtn) {
                                                likeBtn.classList.remove('active');
                                                dislikeBtn.classList.remove('active');

                                                if (data.user_choice === 'like') {
                                                    likeBtn.classList.add('active');
                                                } else if (data.user_choice === 'dislike') {
                                                    dislikeBtn.classList.add('active');
                                                }
                                            }
                                        }

                                        // Comment Edit Functions
                                        window.startCommentEdit = function(commentId) {
                                            const contentDiv = document.getElementById(`comment-content-${commentId}`);
                                            const editForm = document.getElementById(`comment-edit-form-${commentId}`);
                                            
                                            if (contentDiv && editForm) {
                                                contentDiv.style.display = 'none';
                                                editForm.classList.add('active');
                                                document.getElementById(`comment-edit-input-${commentId}`).focus();
                                            }
                                        };

                                        window.cancelCommentEdit = function(commentId) {
                                            const contentDiv = document.getElementById(`comment-content-${commentId}`);
                                            const editForm = document.getElementById(`comment-edit-form-${commentId}`);
                                            
                                            if (contentDiv && editForm) {
                                                contentDiv.style.display = 'block';
                                                editForm.classList.remove('active');
                                            }
                                        };

                                        window.saveCommentEdit = async function(commentId) {
                                            const newContent = document.getElementById(`comment-edit-input-${commentId}`).value.trim();

                                            if (!newContent) {
                                                Swal.fire({
                                                    icon: 'warning',
                                                    title: 'Warning',
                                                    text: 'Comment cannot be empty',
                                                    confirmButtonColor: '#3085d6'
                                                });
                                                return;
                                            }

                                            try {
                                                const response = await fetch('account/actions/comment-edit-delete.php', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/x-www-form-urlencoded',
                                                    },
                                                    body: `comment_id=${commentId}&action=edit&content=${encodeURIComponent(newContent)}`
                                                });

                                                const data = await response.json();

                                                if (data.status === 'success') {
                                                    const contentDiv = document.getElementById(`comment-content-${commentId}`);
                                                    if (contentDiv) {
                                                        contentDiv.innerHTML = data.data.content;
                                                    }
                                                    cancelCommentEdit(commentId);
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: 'Success',
                                                        text: 'Comment updated successfully',
                                                        confirmButtonColor: '#3085d6'
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error',
                                                        text: data.message || 'Failed to update comment',
                                                        confirmButtonColor: '#3085d6'
                                                    });
                                                }
                                            } catch (error) {
                                                console.error('Error:', error);
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: 'Failed to update comment',
                                                    confirmButtonColor: '#3085d6'
                                                });
                                            }
                                        };

                                        // Comment Delete Function
                                        window.deleteComment = function(commentId) {
                                            Swal.fire({
                                                title: 'Delete Comment?',
                                                text: 'This action cannot be undone.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#dc3545',
                                                cancelButtonColor: '#6c757d',
                                                confirmButtonText: 'Delete',
                                                cancelButtonText: 'Cancel'
                                            }).then(async (result) => {
                                                if (result.isConfirmed) {
                                                    try {
                                                        const response = await fetch('account/actions/comment-edit-delete.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/x-www-form-urlencoded',
                                                            },
                                                            body: `comment_id=${commentId}&action=delete`
                                                        });

                                                        const data = await response.json();

                                                        if (data.status === 'success') {
                                                            const commentItem = document.getElementById(`comment-${commentId}`);
                                                            if (commentItem) {
                                                                commentItem.style.opacity = '0.5';
                                                                setTimeout(() => {
                                                                    commentItem.remove();
                                                                }, 300);
                                                            }
                                                            Swal.fire({
                                                                icon: 'success',
                                                                title: 'Deleted',
                                                                text: 'Comment has been deleted',
                                                                confirmButtonColor: '#3085d6'
                                                            });
                                                        } else {
                                                            Swal.fire({
                                                                icon: 'error',
                                                                title: 'Error',
                                                                text: data.message || 'Failed to delete comment',
                                                                confirmButtonColor: '#3085d6'
                                                            });
                                                        }
                                                    } catch (error) {
                                                        console.error('Error:', error);
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: 'Error',
                                                            text: 'Failed to delete comment',
                                                            confirmButtonColor: '#3085d6'
                                                        });
                                                    }
                                                }
                                            });
                                        };
                                    </script>
                                    <?php endif; ?>

                                    <!-- like and share js -->
                                    <script src="account/assets/js/like%26share.js"></script>
                                    <!-- Bootstrap bundle js -->
                                    <script src="account/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

                                    <!-- Layouts main js -->
                                    <script src="account/assets/libs/jquery/jquery.min.js"></script>

                                    <!-- Metimenu js -->
                                    <script src="account/assets/libs/metismenu/metisMenu.min.js"></script>

                                    <!-- simplebar js -->
                                    <script src="account/assets/libs/simplebar/simplebar.min.js"></script>

                                    <script src="account/assets/libs/eva-icons/eva.min.js"></script>

                                    <!-- Scroll Top init -->
                                    <script src="account/assets/js/scroll-top.init.js"></script>
                                    <!-- touchspin js -->
                                    <script
                                        src="account/assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>

                                    <!-- slick-carousel js -->
                                    <script src="account/assets/libs/slick-carousel/slick/slick.min.js"></script>

                                    <!-- Product overview init js -->
                                    <script src="account/assets/js/apps/product-overview.init.js"></script>

                                    <!-- App js -->
                                    <script src="account/assets/js/app.js"></script>

</body>


</html>
