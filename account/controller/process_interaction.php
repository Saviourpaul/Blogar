<?php

header('Content-Type: application/json');

$session_user_id = $_SESSION['user-id'] ?? $_SESSION['user-id'] ?? null;

if (!$session_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$user_id = (int)$session_user_id;
$post_id = (int)($_POST['post_id'] ?? 0);
$action  = $_POST['action'] ?? ''; 
$user_choice = null;

// 2. Validate Input
if ($post_id <= 0 || !in_array($action, ['like', 'dislike', 'share'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
    exit;
}

if ($action === 'share') {
    $stmt = $connection->prepare("INSERT IGNORE INTO post_interactions (post_id, user_id, interaction_type) VALUES (?, ?, 'share')");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
} else {
    $stmt = $connection->prepare("SELECT interaction_type FROM post_interactions WHERE post_id = ? AND user_id = ? AND interaction_type != 'share'");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if (!$existing) {
        $stmt = $connection->prepare("INSERT INTO post_interactions (post_id, user_id, interaction_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $action);
        $stmt->execute();
        $user_choice = $action;
    } elseif ($existing['interaction_type'] === $action) {
        $stmt = $connection->prepare("DELETE FROM post_interactions WHERE post_id = ? AND user_id = ? AND interaction_type = ?");
        $stmt->bind_param("iis", $post_id, $user_id, $action);
        $stmt->execute();
        $user_choice = null;
    } else {
        $stmt = $connection->prepare("UPDATE post_interactions SET interaction_type = ? WHERE post_id = ? AND user_id = ? AND interaction_type != 'share'");
        $stmt->bind_param("sii", $action, $post_id, $user_id);
        $stmt->execute();
        $user_choice = $action;
    }
}

$stmt = $connection->prepare("
    SELECT 
        COUNT(CASE WHEN interaction_type = 'like' THEN 1 END) as likes,
        COUNT(CASE WHEN interaction_type = 'dislike' THEN 1 END) as dislikes,
        COUNT(CASE WHEN interaction_type = 'share' THEN 1 END) as shares
    FROM post_interactions WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$counts = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'status' => 'success', 
    'user_choice' => $user_choice,
    'data' => [
        'likes' => (int)($counts['likes'] ?? 0),
        'dislikes' => (int)($counts['dislikes'] ?? 0),
        'shares' => (int)($counts['shares'] ?? 0)
    ]
]);

$connection->close();
exit;
