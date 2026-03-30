<?php
$connection = require 'config/database.php';

$post_id = $_POST['post_id'];
$content = $_POST['content'];
$parent_id = $_POST['parent_id'] ?? "NULL";

$connection->query("
INSERT INTO comments (post_id, user_id, parent_id, content)
VALUES ($post_id, {$_SESSION['user_id']}, $parent_id, '$content')
");