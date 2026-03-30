<?php

include '../config/database.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);

$stmt = $connection->prepare("SELECT avatar, firstname, lastname FROM users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $connection->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    die("User not found");
}

$stmt->bind_result($avatar, $firstname, $lastname);
$stmt->fetch();

// Delete avatar
if (!empty($avatar)) {
    $avatar_path = '../images/' . $avatar;
    if (file_exists($avatar_path)) {
        unlink($avatar_path);
    }
}

$stmt->close();

// Delete user
$stmt = $connection->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows === 1) {
    $_SESSION['delete-user-success'] = "$firstname $lastname deleted successfully";
} else {
    $_SESSION['delete-user'] = "Failed to delete user.";
}

header("Location: ../ManageUser.php");
exit();