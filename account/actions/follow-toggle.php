<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/helpers.php';
if (!isset($connection) || !($connection instanceof mysqli)) {
    require_once dirname(__DIR__, 2) . '/config/database.php';
}

header('Content-Type: application/json');

$currentUserId = isset($_SESSION['user-id']) ? (int) $_SESSION['user-id'] : 0;
$targetUserId = isset($_POST['target_user_id']) ? (int) $_POST['target_user_id'] : 0;

if ($currentUserId <= 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($targetUserId <= 0 || $targetUserId === $currentUserId) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Invalid follow target']);
    exit;
}

if (!ensureFollowersTable($connection)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Follower storage is unavailable']);
    exit;
}

$targetStmt = $connection->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
$targetStmt->bind_param('i', $targetUserId);
$targetStmt->execute();
$targetExists = $targetStmt->get_result()->fetch_assoc();
$targetStmt->close();

if (!$targetExists) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$toggleResult = toggleFollowUser($connection, $currentUserId, $targetUserId);

if (empty($toggleResult['success']) || empty($toggleResult['action'])) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not update follow status']);
    exit;
}

if ($toggleResult['action'] === 'followed') {
    $actorStmt = $connection->prepare("
        SELECT firstname, lastname, username
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $actorStmt->bind_param('i', $currentUserId);
    $actorStmt->execute();
    $actor = $actorStmt->get_result()->fetch_assoc();
    $actorStmt->close();

    $actorName = trim(($actor['firstname'] ?? '') . ' ' . ($actor['lastname'] ?? ''));
    if ($actorName === '') {
        $actorName = trim((string) ($actor['username'] ?? 'A user'));
    }

    createNotification(
        $connection,
        $targetUserId,
        'follow',
        'New follower',
        $actorName . ' started following you.',
        'UserProfile?id=' . $currentUserId,
        $currentUserId
    );
}

echo json_encode([
    'status' => 'success',
    'action' => $toggleResult['action'],
    'follower_count' => getFollowerCount($connection, $targetUserId),
    'following_count' => getFollowingCount($connection, $currentUserId),
    'button_label' => $toggleResult['action'] === 'followed' ? 'Following' : 'Follow'
]);
exit;
