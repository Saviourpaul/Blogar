<?php

if (!isset($_POST['submit'])) {
    header('Location: addCategory');
    exit();
}

$title = trim($_POST['title']);
$description = trim($_POST['description']);

if (empty($title) || empty($description)) {
    $_SESSION['add-category'] = "Please enter all fields";
    $_SESSION['add-category-data'] = $_POST;
    header('Location: addCategory');
    exit();
}

$query = "INSERT INTO categories (title, description) VALUES (?, ?)";
$stmt = mysqli_prepare($connection, $query);

if (!$stmt) {
    $_SESSION['add-category'] = "Database prepare failed";
    header('Location: addCategory');
    exit();
}

mysqli_stmt_bind_param($stmt, "ss", $title, $description);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) !== 1) {
    $_SESSION['add-category'] = "Couldn't add category";
    header('Location: addCategory');
    exit();
}

$_SESSION['add-category-success'] = "Category $title added successfully";
header('Location: manageCategory');
exit();




