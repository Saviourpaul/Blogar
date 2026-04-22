<?php

require_once __DIR__ . '/../includes/helpers.php';

ensurePostMediaSchema($connection);

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

$author_id = (int) $_SESSION['user-id'];

$title = trim((string) filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS));
$body = sanitizeRichTextHtml($_POST['body'] ?? '');
$category_id = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$media_type = normalizePostMediaType($_POST['media_type'] ?? 'image');
$video_source = normalizePostVideoSource($_POST['video_source'] ?? '');
$video_link = trim((string) ($_POST['video_link'] ?? ''));

$thumbnail = $_FILES['thumbnail'] ?? null;
$video_file = $_FILES['video_file'] ?? null;

$errors = [];
$thumbnail_name = '';
$video_provider = '';
$stored_video_value = '';
$newlyStoredFiles = [];
$transactionStarted = false;

if ($title === '') {
    $errors[] = "Post title required";
}

if ($body === '') {
    $errors[] = "Post body required";
}

if (!$category_id) {
    $errors[] = "Invalid category";
}

if ($media_type === 'video') {
    if ($video_source === 'embed') {
        $parsedVideo = parseExternalVideoReference($video_link);
        if (!$parsedVideo['is_valid']) {
            $errors[] = "Use a valid YouTube or Vimeo URL";
        } else {
            $video_provider = $parsedVideo['provider'];
            $stored_video_value = $parsedVideo['video_id'];
        }
    } elseif ($video_source === 'upload') {
        if (!$video_file || ($video_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = "Video upload required";
        } elseif (($video_file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = "Video upload failed";
        }
    } else {
        $errors[] = "Select how the video should be added";
    }
} elseif (!$thumbnail || ($thumbnail['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $errors[] = "Image thumbnail required";
}

if (!empty($errors)) {
    $_SESSION['add-post'] = implode("<br>", $errors);
    $_SESSION['add-post-data'] = $_POST;
    header('location: CreatePost');
    exit;
}

try {
    if ($thumbnail && ($thumbnail['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $storedThumbnail = storePostUpload(
            $thumbnail,
            '',
            'post_thumb_',
            [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
            ],
            5 * 1024 * 1024
        );

        $thumbnail_name = $storedThumbnail['stored_path'];
        $newlyStoredFiles[] = $thumbnail_name;
    }

    if ($media_type === 'video' && $video_source === 'upload') {
        $storedVideo = storePostUpload(
            $video_file,
            'videos',
            'post_video_',
            [
                'video/mp4' => 'mp4',
                'video/webm' => 'webm',
                'video/ogg' => 'ogv',
                'application/ogg' => 'ogv',
            ],
            50 * 1024 * 1024
        );

        $video_provider = 'upload';
        $stored_video_value = $storedVideo['stored_path'];
        $newlyStoredFiles[] = $stored_video_value;
    }

    if ($media_type === 'image' && $thumbnail_name === '') {
        throw new RuntimeException('Image posts require a thumbnail.');
    }

    if ($media_type !== 'video') {
        $video_source = null;
        $video_provider = null;
        $stored_video_value = null;
    }

    $thumbnail_db_value = $thumbnail_name !== '' ? $thumbnail_name : null;

    $transactionStarted = true;
    $connection->begin_transaction();

    if ($is_featured) {
        $connection->query("UPDATE posts SET is_featured=0");
    }

    $stmt = $connection->prepare(
        "INSERT INTO posts
        (author_id, title, body, category_id, thumbnail, is_featured, media_type, video_source, video_provider, video_url)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "issisissss",
        $author_id,
        $title,
        $body,
        $category_id,
        $thumbnail_db_value,
        $is_featured,
        $media_type,
        $video_source,
        $video_provider,
        $stored_video_value
    );

    $stmt->execute();
    $newPostId = (int) $stmt->insert_id;
    $stmt->close();

    $connection->commit();
    $transactionStarted = false;

    $_SESSION['add-post-success'] = "Post added successfully";

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
} catch (Throwable $exception) {
    if ($transactionStarted) {
        $connection->rollback();
    }

    foreach ($newlyStoredFiles as $storedFile) {
        deletePostUploadAsset($storedFile);
    }

    error_log('Add post failed: ' . $exception->getMessage());
    $_SESSION['add-post'] = $exception instanceof RuntimeException
        ? $exception->getMessage()
        : "Failed to add post";
    $_SESSION['add-post-data'] = $_POST;
    header('location: CreatePost');
    exit;
}

header('location: managePost');
exit;
