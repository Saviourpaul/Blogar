<?php

require_once __DIR__ . '/../includes/helpers.php';

if(!isset($_POST['submit'])){
    header('location: managePost');
    exit;
}

$id = intval($_POST['id']);
$title = trim($_POST['title']);
$body = sanitizeRichTextHtml($_POST['body'] ?? '');
$category_id = intval($_POST['category']);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;

$previous_thumbnail_name = $_POST['previous_thumbnail_name'];
$thumbnail = $_FILES['thumbnail'];

$thumbnail_name = $previous_thumbnail_name;




if(!empty($thumbnail['name'])){

    $allowed = ['jpg','jpeg','png'];
    $ext = strtolower(pathinfo($thumbnail['name'], PATHINFO_EXTENSION));

    if(!in_array($ext,$allowed)){
        $_SESSION['edit-post'] = "Invalid image format";
        header('location: managePost');
        exit;
    }

    if($thumbnail['size'] > 5000000){
        $_SESSION['edit-post'] = "Image must be less than 5MB";
        header('location: managePost');
        exit;
    }

    $thumbnail_name = time().'_'.$thumbnail['name'];

    move_uploaded_file(
        $thumbnail['tmp_name'],
        'account/images/'.$thumbnail_name
    );

    /* delete old thumbnail */

    $old_path = 'account/images/'.$previous_thumbnail_name;

    if(file_exists($old_path)){
        unlink($old_path);
    }
}


if($is_featured == 1){
    mysqli_query($connection,"UPDATE posts SET is_featured=0");
}




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

header('location: managePost');
exit;
