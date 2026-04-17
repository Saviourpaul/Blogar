<?php

require_once __DIR__ . '/../account/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: forgot-password');
    exit;
}

$email = trim($_POST['email'] ?? '');
$_SESSION['forgot-password-data'] = ['email' => $email];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'Please provide a valid email address.'
    ];
    header('location: forgot-password');
    exit;
}

$stmt = $connection->prepare("SELECT id, firstname, lastname, email FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$_SESSION['alert'] = [
    'type' => 'success',
    'message' => 'If that email exists in our system, a reset link has been sent.'
];

if (!$user) {
    header('location: forgot-password');
    exit;
}

$cleanup = $connection->prepare("DELETE FROM password_resets WHERE user_id = ? OR expires_at < NOW() OR used_at IS NOT NULL");
$cleanup->bind_param('i', $user['id']);
$cleanup->execute();
$cleanup->close();

$plainToken = bin2hex(random_bytes(32));
$tokenHash = password_hash($plainToken, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', time() + 3600);

$insert = $connection->prepare("
    INSERT INTO password_resets (user_id, token_hash, expires_at)
    VALUES (?, ?, ?)
");
$insert->bind_param('iss', $user['id'], $tokenHash, $expiresAt);
$insert->execute();
$insert->close();

$siteUrl = rtrim((string) getSettingValue($connection, 'site_url', ''), '/');
if ($siteUrl === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $siteUrl = $scheme . '://' . $host . '/Blogar';
}

$resetLink = $siteUrl . '/reset-password?token=' . urlencode($plainToken);
$fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));

sendEventEmail($connection, 'password_reset', $user['email'], $fullName, [
    'firstname' => $user['firstname'] ?? $fullName,
    'fullname' => $fullName,
    'email' => $user['email'],
    'reset_link' => $resetLink
]);

header('location: forgot-password');
exit;
