<?php


header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$session_user_id = $_SESSION['user-id'] ?? null;

if (!$session_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid security token. Please refresh and try again.']);
    exit;
}

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';

$user_id = (int)$session_user_id;
$comment_id = (int)($_POST['comment_id'] ?? 0);
$action = $_POST['action'] ?? '';
$user_choice = null;

if (!dbTableExists($connection, 'comment_interactions')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Comment interactions are not available until the latest migration is applied.'
    ]);
    exit;
}

// Validate Input
if ($comment_id <= 0 || !in_array($action, ['like', 'dislike', 'share'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
    exit;
}

// Verify comment exists
$commentHasDeletedAt = dbColumnExists($connection, 'comments', 'deleted_at');
$commentFields = "id";
if ($commentHasDeletedAt) {
    $commentFields .= ", deleted_at";
}

$stmt = $connection->prepare("SELECT $commentFields FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$comment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$comment) {
    echo json_encode(['status' => 'error', 'message' => 'Comment not found.']);
    exit;
}

if ($commentHasDeletedAt && !empty($comment['deleted_at']) && $action !== 'share') {
    echo json_encode(['status' => 'error', 'message' => 'Deleted comments can no longer be voted on.']);
    exit;
}

// Handle share (can be added multiple times)
if ($action === 'share') {
    $stmt = $connection->prepare("INSERT IGNORE INTO comment_interactions (comment_id, user_id, interaction_type) VALUES (?, ?, 'share')");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $stmt->close();
} else {
    // Handle like/dislike (exclusive - can only have one at a time)
    $stmt = $connection->prepare("SELECT interaction_type FROM comment_interactions WHERE comment_id = ? AND user_id = ? AND interaction_type != 'share'");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existing) {
        // No existing interaction, add new one
        $stmt = $connection->prepare("INSERT INTO comment_interactions (comment_id, user_id, interaction_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $comment_id, $user_id, $action);
        $stmt->execute();
        $user_choice = $action;
        $stmt->close();
    } elseif ($existing['interaction_type'] === $action) {
        // User clicked the same button again, remove interaction
        $stmt = $connection->prepare("DELETE FROM comment_interactions WHERE comment_id = ? AND user_id = ? AND interaction_type = ?");
        $stmt->bind_param("iis", $comment_id, $user_id, $action);
        $stmt->execute();
        $user_choice = null;
        $stmt->close();
    } else {
        // User changed their interaction (like to dislike or vice versa)
        $stmt = $connection->prepare("UPDATE comment_interactions SET interaction_type = ? WHERE comment_id = ? AND user_id = ? AND interaction_type != 'share'");
        $stmt->bind_param("sii", $action, $comment_id, $user_id);
        $stmt->execute();
        $user_choice = $action;
        $stmt->close();
    }
}

// Get updated counts
$stmt = $connection->prepare("
    SELECT 
        COUNT(CASE WHEN interaction_type = 'like' THEN 1 END) as likes,
        COUNT(CASE WHEN interaction_type = 'dislike' THEN 1 END) as dislikes,
        COUNT(CASE WHEN interaction_type = 'share' THEN 1 END) as shares
    FROM comment_interactions WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$counts = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user's current choice
$stmt = $connection->prepare("SELECT interaction_type FROM comment_interactions WHERE comment_id = ? AND user_id = ? AND interaction_type != 'share'");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
$user_current = $stmt->get_result()->fetch_assoc();
$stmt->close();

$current_choice = $user_current['interaction_type'] ?? null;

echo json_encode([
    'status' => 'success',
    'user_choice' => $current_choice,
    'data' => [
        'likes' => (int)($counts['likes'] ?? 0),
        'dislikes' => (int)($counts['dislikes'] ?? 0),
        'shares' => (int)($counts['shares'] ?? 0),
        'score' => (int)($counts['likes'] ?? 0) - (int)($counts['dislikes'] ?? 0)
    ]
]);

$connection->close();
exit;
