<?php

require_once __DIR__ . '/../account/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: signup');
    exit;
}

$firstname = trim($_POST['firstname']);
$lastname  = trim($_POST['lastname']);
$username  = trim($_POST['username']);
$email     = trim($_POST['email']);
$accountType = normalizeOnboardingAccountType($_POST['account_type'] ?? '');
$password  = $_POST['create_password'];
$confirm   = $_POST['confirm_password'];


$_SESSION = [];

ensureUserOnboardingSchema($connection);


if (!$firstname) $_SESSION['signup-errors'] = "First name is required";
if (!$lastname)  $_SESSION['signup-errors'] = "Last name is required";
if (!$username)  $_SESSION['signup-errors'] = "Username is required";
if ($accountType === null) $_SESSION['signup-errors'] = "Choose how you want to use IdeaHub";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['signup-errors']= "Invalid email format";
}

if (strlen($password) < 8) {
    $_SESSION['signup-errors'] = "Password must be at least 8 characters";
}

// must contain letters + numbers
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $_SESSION['signup-errors'] = "Password must include letters and numbers";
}

if ($password !== $confirm) {
    $_SESSION['signup-errors'] = "Passwords do not match";
}

if (!empty($_SESSION['signup-errors'])) {
    $_SESSION['signup-data'] = $_POST;
    header('location: signup');
    exit;
}

$stmt = $connection->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['signup-errors'] = "Username or Email already exists";
    header('location: signup');
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $connection->prepare("
    INSERT INTO users (firstname, lastname, username, email, password, is_admin, account_type)
    VALUES (?, ?, ?, ?, ?, 0, ?)
");

$stmt->bind_param("ssssss",
    $firstname,
    $lastname,
    $username,
    $email,
    $hashed_password,
    $accountType
);

$stmt->execute();

$newUserId = (int) $stmt->insert_id;
$fullName = trim($firstname . ' ' . $lastname);

sendEventEmail($connection, 'welcome_email', $email, $fullName, [
    'firstname' => $firstname,
    'fullname' => $fullName,
    'email' => $email,
    'username' => $username
]);

createNotification(
    $connection,
    $newUserId,
    'welcome',
    'Welcome to IdeaHub',
    'Your account is ready. Start exploring ideas, posts, and collaborations.',
    'dashboard'
);

sendAdminEventEmail($connection, 'admin_new_user', [
    'firstname' => $firstname,
    'fullname' => $fullName,
    'email' => $email,
    'username' => $username,
    'actor_name' => $fullName
]);

foreach (getAdminNotificationRecipients($connection) as $recipient) {
    $adminStmt = $connection->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($adminStmt) {
        $adminStmt->bind_param('s', $recipient['email']);
        $adminStmt->execute();
        $adminRow = $adminStmt->get_result()->fetch_assoc();
        $adminStmt->close();

        if (!empty($adminRow['id'])) {
            createNotification(
                $connection,
                (int) $adminRow['id'],
                'admin_new_user',
                'New user signup',
                $fullName . ' just created a new account.',
                'ManageUser',
                $newUserId
            );
        }
    }
}

$_SESSION['signup-success'] = "Registration successful. Sign in to finish setting up your workspace.";
header('location: signin');
exit;
