<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

$userId = isset($_SESSION['user-id']) ? (int) $_SESSION['user-id'] : 0;
if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$notificationId = isset($_POST['notification_id']) ? (int) $_POST['notification_id'] : 0;
$marked = $notificationId > 0
    ? markNotificationsRead($connection, $userId, [$notificationId])
    : markNotificationsRead($connection, $userId);

echo json_encode([
    'status' => $marked ? 'success' : 'error',
    'unread_count' => getUnreadNotificationCount($connection, $userId)
]);
exit;
