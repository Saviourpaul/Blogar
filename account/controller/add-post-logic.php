<?php
require '../config/database.php';

if (!isset($_POST['submit'])) {
    header('location: ../CreatePost.php');
    exit;
}

if (!isset($_SESSION['user-id'])) {
    header('location: auth/signin.php');
    exit;
}

$author_id = $_SESSION['user-id'];

$title = trim(filter_input(INPUT_POST,'title',FILTER_SANITIZE_SPECIAL_CHARS));
$body = trim(filter_input(INPUT_POST,'body',FILTER_SANITIZE_SPECIAL_CHARS));
$category_id = filter_input(INPUT_POST,'category',FILTER_VALIDATE_INT);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;

$thumbnail = $_FILES['thumbnail'] ?? null;

$errors = [];

/* validation */

if(!$title) $errors[] = "Post title required";
if(!$body) $errors[] = "Post body required";
if(!$category_id) $errors[] = "Invalid category";

if(!$thumbnail || $thumbnail['error'] !== 0){
    $errors[] = "Image upload failed";
}

$allowed_mimes = ['image/jpeg','image/png'];

if(empty($errors)){

    $mime = mime_content_type($thumbnail['tmp_name']);

    if(!in_array($mime,$allowed_mimes)){
        $errors[] = "Only JPG and PNG allowed";
    }

    if($thumbnail['size'] > 5000000){
        $errors[] = "Image must be less than 5MB";
    }
}

if(!empty($errors)){
    $_SESSION['add-post'] = implode("<br>",$errors);
    $_SESSION['add-post-data'] = $_POST;
    header('location: ../CreatePost.php');
    exit;
}

/* image storage */

$extension = pathinfo($thumbnail['name'], PATHINFO_EXTENSION);
$thumbnail_name = uniqid('post_',true).".".$extension;

$destination = "../uploads/".$thumbnail_name;

if(!move_uploaded_file($thumbnail['tmp_name'],$destination)){
    $_SESSION['add-post'] = "Failed to upload image";
    header('location: ../CreatePost.php');
    exit;
}

/* database transaction */

$connection->begin_transaction();

try{

    if($is_featured){
        $connection->query("UPDATE posts SET is_featured=0");
    }

    $stmt = $connection->prepare(
        "INSERT INTO posts
        (author_id,title,body,category_id,thumbnail,is_featured)
        VALUES (?,?,?,?,?,?)"
    );

    $stmt->bind_param(
        "issisi",
        $author_id,
        $title,
        $body,
        $category_id,
        $thumbnail_name,
        $is_featured
    );

    $stmt->execute();

    $connection->commit();

    $_SESSION['add-post-success']="Post added successfully";

}catch(Exception $e){

    $connection->rollback();

    $_SESSION['add-post']="Failed to add post";
}

header('location: ../managePost.php');
exit;