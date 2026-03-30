<?php
include '../config/database.php';

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

// Delete thumbnail safely
if (!empty($post['thumbnail'])) {
    $thumbnail = '../images/' . $post['thumbnail'];
    if (file_exists($thumbnail)) {
        unlink($thumbnail);
    }
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
header("Location: ../managePost.php");
exit();

