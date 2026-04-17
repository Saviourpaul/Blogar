<?php

require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_POST['submit'])) {
    header('location: CreatePost');
    exit;
}

if (!isset($_SESSION['user-id'])) {
    header('location: signin');
    exit;
}

$postSettingResult = mysqli_query($connection, "SELECT enable_post FROM settings WHERE id = 1 LIMIT 1");
$is_create_post_enabled = true;

if ($postSettingResult && mysqli_num_rows($postSettingResult) > 0) {
    $postSetting = mysqli_fetch_assoc($postSettingResult);
    $is_create_post_enabled = !isset($postSetting['enable_post']) || (int) $postSetting['enable_post'] === 1;
}

if (!$is_create_post_enabled) {
    $_SESSION['add-post'] = "Create post is currently disabled in settings.";
    header('location: managePost');
    exit;
}

$author_id = $_SESSION['user-id'];

$title = trim(filter_input(INPUT_POST,'title',FILTER_SANITIZE_SPECIAL_CHARS));
$body = sanitizeRichTextHtml($_POST['body'] ?? '');
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
    header('location: CreatePost');
    exit;
}

/* image storage */

$extension = pathinfo($thumbnail['name'], PATHINFO_EXTENSION);
$thumbnail_name = uniqid('post_',true).".".$extension;

$destination = "account/uploads/".$thumbnail_name;

if(!move_uploaded_file($thumbnail['tmp_name'],$destination)){
    $_SESSION['add-post'] = "Failed to upload image";
    header('location: CreatePost');
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

    $newPostId = $stmt->insert_id;

    $connection->commit();

    $_SESSION['add-post-success']="Post added successfully";

    $authorQuery = $connection->prepare("SELECT firstname, lastname, email FROM users WHERE id = ? LIMIT 1");
    $authorQuery->bind_param("i", $author_id);
    $authorQuery->execute();
    $authorData = $authorQuery->get_result()->fetch_assoc();
    $authorQuery->close();

    $categoryQuery = $connection->prepare("SELECT title FROM categories WHERE id = ? LIMIT 1");
    $categoryQuery->bind_param("i", $category_id);
    $categoryQuery->execute();
    $categoryData = $categoryQuery->get_result()->fetch_assoc();
    $categoryQuery->close();

    $siteUrl = rtrim((string) getSettingValue($connection, 'site_url', ''), '/');
    $postLink = ($siteUrl !== '' ? $siteUrl : 'postOverview') . ($siteUrl !== '' ? '/postOverview?id=' . $newPostId : '?id=' . $newPostId);
    $authorName = trim(($authorData['firstname'] ?? '') . ' ' . ($authorData['lastname'] ?? ''));

    if (!empty($authorData['email'])) {
        sendEventEmail($connection, 'new_post_notification', $authorData['email'], $authorName, [
            'firstname' => $authorData['firstname'] ?? $authorName,
            'fullname' => $authorName,
            'post_title' => $title,
            'post_link' => $postLink,
            'category_title' => $categoryData['title'] ?? '',
            'actor_name' => $authorName
        ]);
    }

    createNotification(
        $connection,
        $author_id,
        'new_post',
        'Post submitted',
        'Your post "' . $title . '" was submitted successfully.',
        'postOverview?id=' . $newPostId,
        $author_id
    );

    sendAdminEventEmail($connection, 'admin_new_post', [
        'post_title' => $title,
        'post_link' => $postLink,
        'actor_name' => $authorName,
        'category_title' => $categoryData['title'] ?? ''
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
                    'admin_new_post',
                    'New post submission',
                    $authorName . ' submitted "' . $title . '".',
                    'postOverview?id=' . $newPostId,
                    $author_id
                );
            }
        }
    }

}catch(Exception $e){

    $connection->rollback();

    $_SESSION['add-post']="Failed to add post";
}

header('location: managePost');
exit;
