<?php

require_once __DIR__ . '/../includes/helpers.php';

ensurePostMediaSchema($connection);

if (!isset($_POST['submit'])) {
    header('location: managePost');
    exit;
}

if (!isset($_SESSION['user-id'])) {
    header('location: signin');
    exit;
}

$current_user_id = (int) $_SESSION['user-id'];
$is_admin = !empty($_SESSION['is_admin']);

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['edit-post'] = "Invalid post request";
    header('location: managePost');
    exit;
}

$postStmt = $connection->prepare("SELECT * FROM posts WHERE id = ? LIMIT 1");
$postStmt->bind_param("i", $id);
$postStmt->execute();
$existingPost = $postStmt->get_result()->fetch_assoc();
$postStmt->close();

if (!$existingPost) {
    $_SESSION['edit-post'] = "Post not found";
    header('location: managePost');
    exit;
}

if (!$is_admin && (int) ($existingPost['author_id'] ?? 0) !== $current_user_id) {
    $_SESSION['edit-post'] = "You are not allowed to edit this post";
    header('location: managePost');
    exit;
}

$title = trim((string) filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS));
$body = sanitizeRichTextHtml($_POST['body'] ?? '');
$category_id = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$media_type = normalizePostMediaType($_POST['media_type'] ?? ($existingPost['media_type'] ?? 'image'));
$video_source = normalizePostVideoSource($_POST['video_source'] ?? ($existingPost['video_source'] ?? ''));
$video_link = trim((string) ($_POST['video_link'] ?? ''));

$thumbnail = $_FILES['thumbnail'] ?? null;
$video_file = $_FILES['video_file'] ?? null;

$existingThumbnail = normalizePostUploadPath($existingPost['thumbnail'] ?? '');
$existingVideoSource = normalizePostVideoSource($existingPost['video_source'] ?? '');
$existingVideoProvider = normalizePostVideoProvider($existingPost['video_provider'] ?? '');
$existingVideoValue = trim((string) ($existingPost['video_url'] ?? ''));

$thumbnail_name = $existingThumbnail;
$video_provider = '';
$stored_video_value = '';

$errors = [];
$newlyStoredFiles = [];
$filesToDeleteAfterSuccess = [];
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

if ($thumbnail && ($thumbnail['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && ($thumbnail['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    $errors[] = "Image upload failed";
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
        $hasExistingUpload = $existingVideoSource === 'upload' && $existingVideoProvider === 'upload' && $existingVideoValue !== '';
        $hasNewUpload = $video_file && ($video_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

        if (!$hasExistingUpload && !$hasNewUpload) {
            $errors[] = "Video upload required";
        } elseif ($video_file && ($video_file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && ($video_file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = "Video upload failed";
        }
    } else {
        $errors[] = "Select how the video should be added";
    }
}

if (!empty($errors)) {
    $_SESSION['edit-post'] = implode("<br>", $errors);
    $_SESSION['edit-post-data'] = $_POST;
    header('location: UpdatePost?id=' . $id);
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

        if ($existingThumbnail !== '' && $existingThumbnail !== $thumbnail_name) {
            $filesToDeleteAfterSuccess[] = $existingThumbnail;
        }
    }

    if ($media_type === 'image') {
        if ($thumbnail_name === '') {
            throw new RuntimeException('Image posts require a thumbnail.');
        }

        if ($existingVideoSource === 'upload' && $existingVideoProvider === 'upload' && $existingVideoValue !== '') {
            $filesToDeleteAfterSuccess[] = $existingVideoValue;
        }
    } elseif ($video_source === 'upload') {
        if ($video_file && ($video_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
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

            if ($existingVideoSource === 'upload' && $existingVideoProvider === 'upload' && $existingVideoValue !== '' && $existingVideoValue !== $stored_video_value) {
                $filesToDeleteAfterSuccess[] = $existingVideoValue;
            }
        } else {
            $video_provider = 'upload';
            $stored_video_value = $existingVideoValue;
        }
    } elseif ($video_source === 'embed') {
        if ($existingVideoSource === 'upload' && $existingVideoProvider === 'upload' && $existingVideoValue !== '') {
            $filesToDeleteAfterSuccess[] = $existingVideoValue;
        }
    }

    if ($media_type !== 'video') {
        $video_source = null;
        $video_provider = null;
        $stored_video_value = null;
    }

    $thumbnail_db_value = $thumbnail_name !== '' ? $thumbnail_name : null;

    $transactionStarted = true;
    $connection->begin_transaction();

    if ($is_featured === 1) {
        mysqli_query($connection, "UPDATE posts SET is_featured=0");
    }

    $stmt = $connection->prepare(
        "UPDATE posts
        SET title = ?, body = ?, category_id = ?, thumbnail = ?, is_featured = ?, media_type = ?, video_source = ?, video_provider = ?, video_url = ?
        WHERE id = ?"
    );

    $stmt->bind_param(
        "ssissssssi",
        $title,
        $body,
        $category_id,
        $thumbnail_db_value,
        $is_featured,
        $media_type,
        $video_source,
        $video_provider,
        $stored_video_value,
        $id
    );

    $stmt->execute();
    $stmt->close();

    $connection->commit();
    $transactionStarted = false;

    foreach (array_unique(array_filter($filesToDeleteAfterSuccess)) as $storedFile) {
        deletePostUploadAsset($storedFile);
    }

    $_SESSION['edit-post-success'] = "Post updated successfully";
} catch (Throwable $exception) {
    if ($transactionStarted) {
        $connection->rollback();
    }

    foreach (array_unique(array_filter($newlyStoredFiles)) as $storedFile) {
        deletePostUploadAsset($storedFile);
    }

    error_log('Update post failed: ' . $exception->getMessage());
    $_SESSION['edit-post'] = $exception instanceof RuntimeException
        ? $exception->getMessage()
        : "Failed to update post";
    $_SESSION['edit-post-data'] = $_POST;
    header('location: UpdatePost?id=' . $id);
    exit;
}

header('location: managePost');
exit;
