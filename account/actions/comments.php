<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/helpers.php';

$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'dashboard';
$current_user_id = isset($_SESSION['user-id']) ? (int) $_SESSION['user-id'] : 0;

if (!isSettingEnabled($connection, 'enable_comment', true)) {
    $_SESSION['comments-error'] = 'Comments are currently disabled in settings.';
    header("Location: $redirect_url");
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    $_SESSION['comments-error'] = 'Invalid security token. Please refresh and try again.';
    header("Location: $redirect_url");
    exit(); 
}

$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$parent_id = isset($_POST['parent_id']) ? (int) $_POST['parent_id'] : 0;

$name = trim(strip_tags($_POST['name'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));
$message = preg_replace('/\R{3,}/', "\n\n", (string) $message);

if ($current_user_id <= 0) {
    $_SESSION['comments-error'] = 'You must be logged in to comment.';
    header("Location: $redirect_url");
    exit();
}

if ($post_id <= 0 || empty($name) || empty($message)) {
    $_SESSION['comments-error'] = 'All fields are required.';
    header("Location: $redirect_url");
    exit(); 
}

if (mb_strlen($message) > 5000) {
    $_SESSION['comments-error'] = 'Comments must be 5000 characters or fewer.';
    header("Location: $redirect_url");
    exit();
}

$commentHasUserId = dbColumnExists($connection, 'comments', 'user_id');
$commentHasEditExpiresAt = dbColumnExists($connection, 'comments', 'edit_expires_at');
$commentHasDeletedAt = dbColumnExists($connection, 'comments', 'deleted_at');
$commentEditWindow = max(1, (int) getSettingValue($connection, 'comment_edit_window', 15));

$actorName = $name;
$actorStmt = $connection->prepare("SELECT firstname, lastname FROM users WHERE id = ? LIMIT 1");
if ($actorStmt) {
    $actorStmt->bind_param("i", $current_user_id);
    $actorStmt->execute();
    $actorData = $actorStmt->get_result()->fetch_assoc();
    $actorStmt->close();

    if ($actorData) {
        $sessionDisplayName = trim(($actorData['firstname'] ?? '') . ' ' . ($actorData['lastname'] ?? ''));
        if ($sessionDisplayName !== '') {
            $actorName = $sessionDisplayName;
            $name = $sessionDisplayName;
        }
    }
}

if ($parent_id > 0) {
    $parentFields = "id";
    if ($commentHasDeletedAt) {
        $parentFields .= ", deleted_at";
    }

    $stmt = $connection->prepare("SELECT $parentFields FROM comments WHERE id = ? AND post_id = ?");
    $stmt->bind_param("ii", $parent_id, $post_id);
    $stmt->execute();
    $parentComment = $stmt->get_result()->fetch_assoc();
    
    if (!$parentComment) {
        $_SESSION['comments-error'] = 'That comment is no longer available for replies.';
        $stmt->close();
        header("Location: $redirect_url");
        exit();
    } elseif ($commentHasDeletedAt && !empty($parentComment['deleted_at'])) {
        $_SESSION['comments-error'] = 'That comment is no longer available for replies.';
        $stmt->close();
        header("Location: $redirect_url");
        exit();
    }
    $stmt->close();
}

if ($commentHasUserId && $commentHasEditExpiresAt) {
    $stmt = $connection->prepare("
        INSERT INTO comments (post_id, parent_id, user_id, name, message, created_at, edit_expires_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    $edit_expires_at = (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))
        ->modify('+' . $commentEditWindow . ' minutes')
        ->format('Y-m-d H:i:s');
    $stmt->bind_param("iiisss", $post_id, $parent_id, $current_user_id, $name, $message, $edit_expires_at);
} elseif ($commentHasUserId) {
    $stmt = $connection->prepare("
        INSERT INTO comments (post_id, parent_id, user_id, name, message, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiiss", $post_id, $parent_id, $current_user_id, $name, $message);
} elseif ($commentHasEditExpiresAt) {
    $stmt = $connection->prepare("
        INSERT INTO comments (post_id, parent_id, name, message, created_at, edit_expires_at) 
        VALUES (?, ?, ?, ?, NOW(), ?)
    ");
    $edit_expires_at = (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))
        ->modify('+' . $commentEditWindow . ' minutes')
        ->format('Y-m-d H:i:s');
    $stmt->bind_param("iisss", $post_id, $parent_id, $name, $message, $edit_expires_at);
} else {
    $stmt = $connection->prepare("
        INSERT INTO comments (post_id, parent_id, name, message, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiss", $post_id, $parent_id, $name, $message);
}

if ($stmt->execute()) {
    $newCommentId = (int) $stmt->insert_id;
    $_SESSION['comments-success'] = 'Comment posted successfully!';
    $commentPath = [];

    if ($parent_id > 0) {
        $cursorId = $parent_id;
        $visited = [];
        $ancestorStmt = $connection->prepare("
            SELECT id, COALESCE(parent_id, 0) AS parent_id
            FROM comments
            WHERE id = ? AND post_id = ?
            LIMIT 1
        ");

        while ($cursorId > 0 && !isset($visited[$cursorId])) {
            $visited[$cursorId] = true;
            $ancestorStmt->bind_param('ii', $cursorId, $post_id);
            $ancestorStmt->execute();
            $ancestorRow = $ancestorStmt->get_result()->fetch_assoc();

            if (!$ancestorRow) {
                break;
            }

            array_unshift($commentPath, $cursorId);
            $cursorId = (int) ($ancestorRow['parent_id'] ?? 0);
        }

        $ancestorStmt->close();
    }

    $sortContext = strtolower(trim((string) ($_POST['comment_sort_context'] ?? 'recent')));
    $sortContext = $sortContext === 'oldest' ? 'oldest' : 'recent';
    $pageContext = max(1, (int) ($_POST['comment_page_context'] ?? 1));

    $commentLinkParams = ['id' => $post_id];
    if ($parent_id > 0) {
        if ($sortContext !== 'recent') {
            $commentLinkParams['comment_sort'] = $sortContext;
        }
        if ($pageContext > 1) {
            $commentLinkParams['comment_page'] = $pageContext;
        }
    }
    if (!empty($commentPath)) {
        $commentLinkParams['comment_path'] = implode(',', $commentPath);
        $commentLinkParams['comment_focus'] = $newCommentId;
    }

    $redirect_url = 'postOverview?' . http_build_query($commentLinkParams) . '#comment-' . $newCommentId;

    $postMetaStmt = $connection->prepare("
        SELECT p.id, p.title, p.author_id, u.firstname, u.lastname, u.email
        FROM posts p
        JOIN users u ON p.author_id = u.id
        WHERE p.id = ?
        LIMIT 1
    ");
    $postMetaStmt->bind_param("i", $post_id);
    $postMetaStmt->execute();
    $postMeta = $postMetaStmt->get_result()->fetch_assoc();
    $postMetaStmt->close();

    $siteUrl = rtrim((string) getSettingValue($connection, 'site_url', ''), '/');
    $postLink = $siteUrl !== ''
        ? $siteUrl . '/' . ltrim($redirect_url, '/')
        : $redirect_url;

    if ($postMeta && !empty($postMeta['email']) && (int) $postMeta['author_id'] !== $current_user_id) {
        $postOwnerTitle = $parent_id > 0 ? 'New reply on your post' : 'New comment on your post';
        $postOwnerMessage = $parent_id > 0
            ? $actorName . ' replied in the discussion on "' . $postMeta['title'] . '".'
            : $actorName . ' commented on "' . $postMeta['title'] . '".';

        sendEventEmail($connection, 'comment_notification', $postMeta['email'], trim($postMeta['firstname'] . ' ' . $postMeta['lastname']), [
            'firstname' => $postMeta['firstname'],
            'fullname' => trim($postMeta['firstname'] . ' ' . $postMeta['lastname']),
            'post_title' => $postMeta['title'],
            'post_link' => $postLink,
            'actor_name' => $actorName
        ]);

        createNotification(
            $connection,
            (int) $postMeta['author_id'],
            'comment',
            $postOwnerTitle,
            $postOwnerMessage,
            'postOverview?id=' . $post_id,
            $current_user_id > 0 ? $current_user_id : null
        );
    }

    if ($parent_id > 0 && $commentHasUserId) {
        $replyRecipientStmt = $connection->prepare("
            SELECT u.id, u.firstname, u.lastname, u.email
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ? AND c.post_id = ?
            LIMIT 1
        ");
        $replyRecipientStmt->bind_param("ii", $parent_id, $post_id);
        $replyRecipientStmt->execute();
        $replyRecipient = $replyRecipientStmt->get_result()->fetch_assoc();
        $replyRecipientStmt->close();

        if (
            $replyRecipient &&
            !empty($replyRecipient['email']) &&
            (int) $replyRecipient['id'] !== $current_user_id &&
            (int) $replyRecipient['id'] !== (int) ($postMeta['author_id'] ?? 0)
        ) {
            sendEventEmail($connection, 'reply_notification', $replyRecipient['email'], trim($replyRecipient['firstname'] . ' ' . $replyRecipient['lastname']), [
                'firstname' => $replyRecipient['firstname'],
                'fullname' => trim($replyRecipient['firstname'] . ' ' . $replyRecipient['lastname']),
                'post_title' => $postMeta['title'] ?? '',
                'post_link' => $postLink,
                'actor_name' => $actorName
            ]);

            createNotification(
                $connection,
                (int) $replyRecipient['id'],
                'reply',
                'New reply to your comment',
                $actorName . ' replied to your comment on "' . ($postMeta['title'] ?? 'your post') . '".',
                'postOverview?id=' . $post_id,
                $current_user_id > 0 ? $current_user_id : null
            );
        }
    }
} else {
    error_log("Comment Insert Error: " . $stmt->error);
    $_SESSION['comments-error'] = 'An error occurred. Your comment could not be posted.';
}

$stmt->close();
$connection->close();



header("Location: $redirect_url");
exit();
