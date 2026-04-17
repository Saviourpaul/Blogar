<?php

require_once __DIR__ . '/../account/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: signin');
    exit;
}

$token = trim($_POST['token'] ?? '');
$password = $_POST['create_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($token === '' || $password === '' || $confirm === '') {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'All fields are required.'
    ];
    header('location: reset-password?token=' . urlencode($token));
    exit;
}

if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'Password must be at least 8 characters and include letters and numbers.'
    ];
    header('location: reset-password?token=' . urlencode($token));
    exit;
}

if ($password !== $confirm) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'Passwords do not match.'
    ];
    header('location: reset-password?token=' . urlencode($token));
    exit;
}

$result = mysqli_query($connection, "
    SELECT pr.id, pr.user_id, pr.token_hash, pr.expires_at, pr.used_at, u.firstname, u.lastname
    FROM password_resets pr
    JOIN users u ON pr.user_id = u.id
    WHERE pr.used_at IS NULL
    ORDER BY pr.id DESC
");

$resetRequest = null;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strtotime($row['expires_at']) < time()) {
            continue;
        }

        if (password_verify($token, $row['token_hash'])) {
            $resetRequest = $row;
            break;
        }
    }
}

if (!$resetRequest) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'This password reset link is invalid or has expired.'
    ];
    header('location: forgot-password');
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$connection->begin_transaction();

try {
    $updateUser = $connection->prepare("
        UPDATE users
        SET password = ?, login_attempts = 0
        WHERE id = ?
    ");
    $updateUser->bind_param('si', $hashedPassword, $resetRequest['user_id']);
    $updateUser->execute();
    $updateUser->close();

    $markUsed = $connection->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE id = ?
    ");
    $markUsed->bind_param('i', $resetRequest['id']);
    $markUsed->execute();
    $markUsed->close();

    $connection->commit();

    createNotification(
        $connection,
        (int) $resetRequest['user_id'],
        'welcome',
        'Password updated',
        'Your password was reset successfully. You can now sign in with your new password.',
        'signin'
    );

    $_SESSION['alert'] = [
        'type' => 'success',
        'message' => 'Your password has been updated successfully.'
    ];
    header('location: signin');
    exit;
} catch (Exception $exception) {
    $connection->rollback();
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'Unable to reset password right now. Please try again.'
    ];
    header('location: reset-password?token=' . urlencode($token));
    exit;
}
