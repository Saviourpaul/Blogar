<?php

session_start();
require '../config/database.php';

if(!isset($_POST['submit'])){
    header('location: ../manage-post.php');
    exit;
}

$id = intval($_POST['id']);
$title = trim($_POST['title']);
$body = trim($_POST['body']);
$category_id = intval($_POST['category']);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;

$previous_thumbnail_name = $_POST['previous_thumbnail_name'];
$thumbnail = $_FILES['thumbnail'];

$thumbnail_name = $previous_thumbnail_name;


/*
|--------------------------------------------------------------------------
| Upload new thumbnail if provided
|--------------------------------------------------------------------------
*/

if(!empty($thumbnail['name'])){

    $allowed = ['jpg','jpeg','png'];
    $ext = strtolower(pathinfo($thumbnail['name'], PATHINFO_EXTENSION));

    if(!in_array($ext,$allowed)){
        $_SESSION['edit-post'] = "Invalid image format";
        header('location: ../manage-post.php');
        exit;
    }

    if($thumbnail['size'] > 5000000){
        $_SESSION['edit-post'] = "Image must be less than 5MB";
        header('location: ../manage-post.php');
        exit;
    }

    $thumbnail_name = time().'_'.$thumbnail['name'];

    move_uploaded_file(
        $thumbnail['tmp_name'],
        '../images/'.$thumbnail_name
    );

    /* delete old thumbnail */

    $old_path = '../images/'.$previous_thumbnail_name;

    if(file_exists($old_path)){
        unlink($old_path);
    }
}


/*
|--------------------------------------------------------------------------
| Reset featured post
|--------------------------------------------------------------------------
*/

if($is_featured == 1){
    mysqli_query($connection,"UPDATE posts SET is_featured=0");
}


/*
|--------------------------------------------------------------------------
| Update post
|--------------------------------------------------------------------------
*/

$stmt = $connection->prepare(
"UPDATE posts
SET title=?, body=?, category_id=?, thumbnail=?, is_featured=?
WHERE id=?"
);

$stmt->bind_param(
"ssissi",
$title,
$body,
$category_id,
$thumbnail_name,
$is_featured,
$id
);

$stmt->execute();

$_SESSION['edit-post-success'] = "Post updated successfully";

header('location: ../manage-post.php');
exit;