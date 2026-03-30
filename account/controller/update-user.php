<?php

require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$id = intval($_POST['id']);
$firstname = trim($_POST['firstname']);
$lastname = trim($_POST['lastname']);
$username = trim($_POST['username']);
$is_admin = intval($_POST['userrole']);

if (!$firstname || !$lastname || !$email || !$username) {
    $_SESSION['edit-user'] = "Please fill in all fields";
    header("Location: ../ManageUser.php?id=$id");
    exit();
}

if ($_SESSION['is_admin'] != 1) {
    die("Unauthorized action");
}

$stmt = $connection->prepare(
    "UPDATE users 
     SET firstname=?,  lastname=?, username=?, is_admin=? 
     WHERE id=?"
);

$stmt->bind_param("sssii", $firstname,  $lastname, $username, $is_admin, $id);

$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['edit-user-success'] =
        "User $firstname $lastname updated successfully";
} else {
    $_SESSION['edit-user'] = "No changes made";
}

header('Location: ../ManageUser.php');
exit;