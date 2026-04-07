<?php
// Ensure session is started before accessing $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php'; 

$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['comments-error'] = 'Invalid security token. Please refresh and try again.';
    header("Location: $redirect_url");
    exit(); 
}

$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$parent_id = isset($_POST['parent_id']) ? (int) $_POST['parent_id'] : 0;

$name = trim(strip_tags($_POST['name'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

if ($post_id <= 0 || empty($name) || empty($message)) {
    $_SESSION['comments-error'] = 'All fields are required.';
    header("Location: $redirect_url");
    exit(); 
}

if ($parent_id > 0) {
    $stmt = $connection->prepare("SELECT id FROM comments WHERE id = ? AND post_id = ?");
    $stmt->bind_param("ii", $parent_id, $post_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        $parent_id = 0; 
    }
    $stmt->close();
}

$stmt = $connection->prepare("
    INSERT INTO comments (post_id, parent_id, name, message, created_at) 
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiss", $post_id, $parent_id, $name, $message);

if ($stmt->execute()) {
    $_SESSION['comments-success'] = 'Comment posted successfully!';
} else {
    error_log("Comment Insert Error: " . $stmt->error);
    $_SESSION['comments-error'] = 'An error occurred. Your comment could not be posted.';
}

$stmt->close();
$connection->close();

header("Location: $redirect_url");
exit();