<?php

require_once __DIR__ . '/../includes/helpers.php';

if(!isset($_POST['submit'])) {
    header('location: AddUser');
    exit;
}

$errors = [];

// Sanitize inputs
$firstname = trim(filter_input(INPUT_POST,'firstname',FILTER_SANITIZE_SPECIAL_CHARS));
$lastname  = trim(filter_input(INPUT_POST,'lastname',FILTER_SANITIZE_SPECIAL_CHARS));
$username  = trim(filter_input(INPUT_POST,'username',FILTER_SANITIZE_SPECIAL_CHARS));
$email     = filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);
$password  = $_POST['create_password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';
$is_admin  = intval($_POST['userrole'] ?? 0);

$avatar = $_FILES['avatar'] ?? null;

if(!$firstname) $errors[] = "First name required";
if(!$lastname)  $errors[] = "Last name required";
if(!$username)  $errors[] = "Username required";
if(!$email)     $errors[] = "Valid email required";

if(strlen($password) < 8)
    $errors[] = "Password must be at least 8 characters";

if($password !== $confirm)
    $errors[] = "Passwords do not match";

if(!$avatar || $avatar['error'] !== 0)
    $errors[] = "Avatar upload failed";

$allowed_mimes = ['image/jpeg','image/png'];

if(empty($errors)) {

    $mime = mime_content_type($avatar['tmp_name']);

    if(!in_array($mime,$allowed_mimes)) {
        $errors[] = "Only JPG or PNG allowed";
    }

    if($avatar['size'] > 5000000) {
        $errors[] = "File exceeds 5MB";
    }
}

if(!empty($errors)) {

    $_SESSION['add-user'] = implode(",", $errors);
    $_SESSION['add-user-data'] = $_POST;

    header('location: AddUser');
    exit;
}

$extension = pathinfo($avatar['name'], PATHINFO_EXTENSION);
$avatar_name = uniqid('avatar_',true).'.'.$extension;

$destination = 'account/uploads/'.$avatar_name;

move_uploaded_file($avatar['tmp_name'],$destination);

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $connection->prepare(
    "SELECT id FROM users WHERE username=? OR email=? LIMIT 1"
);

$stmt->bind_param("ss",$username,$email);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){

    $_SESSION['add-user'] = "Username or Email already exists";
    header('location: AddUser');
    exit;
}

$stmt = $connection->prepare(
"INSERT INTO users
(firstname,lastname,username,email,password,avatar,is_admin)
VALUES (?,?,?,?,?,?,?)"
);

$stmt->bind_param(
"ssssssi",
$firstname,
$lastname,
$username,
$email,
$hashed_password,
$avatar_name,
$is_admin
);

$stmt->execute();

$_SESSION['add-user-success'] =
"New user $firstname $lastname added successfully";

$fullName = trim($firstname . ' ' . $lastname);

sendEventEmail($connection, 'welcome_email', $email, $fullName, [
    'firstname' => $firstname,
    'fullname' => $fullName,
    'email' => $email,
    'username' => $username
]);

createNotification(
    $connection,
    (int) $stmt->insert_id,
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
                (int) $stmt->insert_id
            );
        }
    }
}

header('location: ManageUser');
exit;
           

        
