<?php
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: signup.php');
    exit;
}

$firstname = trim($_POST['firstname']);
$lastname  = trim($_POST['lastname']);
$username  = trim($_POST['username']);
$email     = trim($_POST['email']);
$password  = $_POST['create_password'];
$confirm   = $_POST['confirm_password'];


$_SESSION = [];



if (!$firstname) $_SESSION['signup-errors'] = "First name is required";
if (!$lastname)  $_SESSION['signup-errors'] = "Last name is required";
if (!$username)  $_SESSION['signup-errors'] = "Username is required";

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
    header('location: signup.php');
    exit;
}

$stmt = $connection->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['signup-errors'] = ["Username or Email already exists"];
    header('location: signup.php');
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $connection->prepare("
    INSERT INTO users (firstname, lastname, username, email, password, is_admin)
    VALUES (?, ?, ?, ?, ?, 0)
");

$stmt->bind_param("sssss",
    $firstname,
    $lastname,
    $username,
    $email,
    $hashed_password
);

$stmt->execute();

$_SESSION['signup-success'] = "Registration successful. Please login.";
header('location: signin.php');
exit;