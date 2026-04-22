<?php

require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ensurePostMediaSchema($connection);

if (!isSettingEnabled($connection, 'enable_delete_post', false)) {
    $_SESSION['delete-post'] = "Delete post is currently disabled in settings.";
    header("Location: managePost");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}


$id = intval($_GET['id']);

$stmt = $connection->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Post not found");
}

$post = $result->fetch_assoc();
$title = $post['title'] ?? 'selected';

// Delete thumbnail safely
if (!empty($post['thumbnail'])) {
    deletePostUploadAsset($post['thumbnail']);
}

if (
    normalizePostMediaType($post['media_type'] ?? 'image') === 'video' &&
    normalizePostVideoSource($post['video_source'] ?? '') === 'upload' &&
    normalizePostVideoProvider($post['video_provider'] ?? '') === 'upload' &&
    !empty($post['video_url'])
) {
    deletePostUploadAsset($post['video_url']);
}

// Delete post
$stmt = $connection->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows === 1) {
    $_SESSION['delete-post-success'] = "Post $title deleted successfully";
} else {
    $_SESSION['delete-post'] = "Failed to delete post.";
}
header("Location: managePost");
exit();

