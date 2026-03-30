<?php
require 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Login required']);
    exit;
}

$connection = require 'config/database.php';

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// check if already liked
$check = $connection->query("SELECT * FROM likes WHERE user_id=$user_id AND post_id=$post_id");

if ($check->num_rows > 0) {
    // UNLIKE
    $connection->query("DELETE FROM likes WHERE user_id=$user_id AND post_id=$post_id");
    $connection->query("UPDATE posts SET likes_count = likes_count - 1 WHERE id=$post_id");

    echo json_encode(['status' => 'unliked']);
} else {
    // LIKE
    $connection->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
    $connection->query("UPDATE posts SET likes_count = likes_count + 1 WHERE id=$post_id");

    echo json_encode(['status' => 'liked']);
}