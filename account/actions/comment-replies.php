<?php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/comment-thread-helpers.php';
require_once __DIR__ . '/../../config/database.php';

$currentUserId = (int) ($_SESSION['user-id'] ?? 0);
$currentUserIsAdmin = !empty($_SESSION['is_admin']);

if ($currentUserId <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized. Please sign in again.',
    ]);
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid security token. Please refresh and try again.',
    ]);
    exit;
}

$postId = (int) ($_POST['post_id'] ?? 0);
$parentId = (int) ($_POST['parent_id'] ?? 0);
$offset = max(0, (int) ($_POST['offset'] ?? 0));
$limit = max(1, min(25, (int) ($_POST['limit'] ?? 8)));
$level = max(1, (int) ($_POST['level'] ?? 1));
$sort = normalizeCommentThreadSort($_POST['sort'] ?? 'oldest');
$ancestorPathRaw = trim((string) ($_POST['ancestor_path'] ?? ''));

if ($postId <= 0 || $parentId <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid comment thread request.',
    ]);
    exit;
}

$ancestorPath = [];
if ($ancestorPathRaw !== '') {
    foreach (explode(',', $ancestorPathRaw) as $pathSegment) {
        $pathId = (int) $pathSegment;
        if ($pathId > 0) {
            $ancestorPath[] = $pathId;
        }
    }
}

$commentHasEditExpiresAt = dbColumnExists($connection, 'comments', 'edit_expires_at');
$commentHasDeletedAt = dbColumnExists($connection, 'comments', 'deleted_at');
$commentInteractionsEnabled = dbTableExists($connection, 'comment_interactions');
$editWindow = max(1, (int) getSettingValue($connection, 'comment_edit_window', 15));
$maxDepth = 4;

$parentCheck = $connection->prepare('SELECT id FROM comments WHERE id = ? AND post_id = ? LIMIT 1');
$parentCheck->bind_param('ii', $parentId, $postId);
$parentCheck->execute();
$parentExists = $parentCheck->get_result()->fetch_assoc();
$parentCheck->close();

if (!$parentExists) {
    echo json_encode([
        'status' => 'error',
        'message' => 'That parent comment no longer exists.',
    ]);
    exit;
}

$children = fetchCommentThreadNodes($connection, $postId, $currentUserId, $parentId, $limit, $offset, $sort);
$totalChildren = countCommentThreadChildren($connection, $postId, $parentId);

ob_start();
renderCommentThreadList(
    $children,
    $currentUserId,
    $currentUserIsAdmin,
    $editWindow,
    $commentHasEditExpiresAt,
    $commentHasDeletedAt,
    $commentInteractionsEnabled,
    $level,
    $maxDepth,
    array_merge($ancestorPath, [$parentId])
);
$html = trim(ob_get_clean());

echo json_encode([
    'status' => 'success',
    'html' => $html,
    'meta' => [
        'loaded' => count($children),
        'total' => $totalChildren,
        'next_offset' => $offset + count($children),
        'has_more' => ($offset + count($children)) < $totalChildren,
    ],
]);

$connection->close();
exit;
