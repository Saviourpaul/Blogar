<?php
/*
 * Comment Edit/Delete Handler
 * Handles editing and deleting comments with time restrictions
 * Comments can only be edited within a configurable time window (default: 15 minutes)
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/helpers.php';

$session_user_id = $_SESSION['user-id'] ?? null;

if (!$session_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid security token. Please refresh and try again.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$user_id = (int)$session_user_id;
$comment_id = (int)($_POST['comment_id'] ?? 0);
$action = $_POST['action'] ?? '';
$new_content = isset($_POST['content']) ? trim($_POST['content']) : '';

// Validate input
if ($comment_id <= 0 || !in_array($action, ['edit', 'delete'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
    exit;
}

$commentHasEditExpiresAt = dbColumnExists($connection, 'comments', 'edit_expires_at');
$commentHasEditedAt = dbColumnExists($connection, 'comments', 'edited_at');
$commentHasIsEdited = dbColumnExists($connection, 'comments', 'is_edited');
$commentHasDeletedAt = dbColumnExists($connection, 'comments', 'deleted_at');
$commentHasDeletedBy = dbColumnExists($connection, 'comments', 'deleted_by');
$commentHasName = dbColumnExists($connection, 'comments', 'name');
$commentEditsEnabled = dbTableExists($connection, 'comment_edits');

// Fetch comment details
$commentFields = ['id', 'user_id', 'message', 'created_at'];
if ($commentHasEditExpiresAt) {
    $commentFields[] = 'edit_expires_at';
}
if ($commentHasDeletedAt) {
    $commentFields[] = 'deleted_at';
}

$stmt = $connection->prepare("SELECT " . implode(', ', $commentFields) . " FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$comment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$comment) {
    echo json_encode(['status' => 'error', 'message' => 'Comment not found.']);
    exit;
}

// Check if user is the comment author
if ((int)$comment['user_id'] !== $user_id) {
    echo json_encode(['status' => 'error', 'message' => 'You can only edit or delete your own comments.']);
    exit;
}

if ($commentHasDeletedAt && !empty($comment['deleted_at'])) {
    echo json_encode(['status' => 'error', 'message' => 'This comment has already been deleted.']);
    exit;
}

if ($action === 'edit') {
    // Get edit time window (in minutes) from settings, default to 15
    $edit_window = max(1, (int)getSettingValue($connection, 'comment_edit_window', 15));
    $edit_deadline = $commentHasEditExpiresAt && !empty($comment['edit_expires_at'])
        ? strtotime((string) $comment['edit_expires_at'])
        : strtotime((string) $comment['created_at']) + ($edit_window * 60);

    if ($edit_deadline !== false && time() > $edit_deadline) {
        echo json_encode(['status' => 'error', 'message' => "Comments can only be edited within $edit_window minutes of creation."]);
        exit;
    }

    // Edit comment
    if (empty($new_content)) {
        echo json_encode(['status' => 'error', 'message' => 'Comment content cannot be empty.']);
        exit;
    }

    if (strlen($new_content) > 5000) {
        echo json_encode(['status' => 'error', 'message' => 'Comment is too long (maximum 5000 characters).']);
        exit;
    }

    // Check if content actually changed
    if ($new_content === $comment['message']) {
        echo json_encode(['status' => 'error', 'message' => 'No changes made to the comment.']);
        exit;
    }

    // Log edit history
    if ($commentEditsEnabled) {
        $stmt = $connection->prepare("
            INSERT INTO comment_edits (comment_id, edited_by, previous_content, new_content)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $comment_id, $user_id, $comment['message'], $new_content);
        
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save edit history.']);
            exit;
        }
        $stmt->close();
    }

    // Update comment
    $updateParts = ["message = ?"];
    if ($commentHasEditedAt) {
        $updateParts[] = "edited_at = NOW()";
    }
    if ($commentHasIsEdited) {
        $updateParts[] = "is_edited = TRUE";
    }

    $stmt = $connection->prepare("
        UPDATE comments 
        SET " . implode(', ', $updateParts) . "
        WHERE id = ?
    ");
    $stmt->bind_param("si", $new_content, $comment_id);
    
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update comment.']);
        exit;
    }
    $stmt->close();

    $content_html = nl2br(htmlspecialchars($new_content, ENT_QUOTES, 'UTF-8'));
    $minutes_remaining = $edit_deadline !== false ? max(0, (int) ceil(($edit_deadline - time()) / 60)) : 0;

    echo json_encode([
        'status' => 'success',
        'message' => 'Comment updated successfully.',
        'data' => [
            'content_html' => $content_html,
            'is_edited' => true,
            'edited_at' => date('Y-m-d H:i:s'),
            'minutes_remaining' => $minutes_remaining,
            'can_edit' => $minutes_remaining > 0
        ]
    ]);
    exit;

} elseif ($action === 'delete') {
    if ($commentHasDeletedAt) {
        $deleteParts = ["deleted_at = NOW()", "message = ''"];
        if ($commentHasDeletedBy) {
            $deleteParts[] = "deleted_by = ?";
        }

        if ($commentHasName) {
            $deleteParts[] = "name = '[deleted]'";
        }

        $stmt = $connection->prepare("
            UPDATE comments
            SET " . implode(', ', $deleteParts) . "
            WHERE id = ?
        ");
        if ($commentHasDeletedBy) {
            $stmt->bind_param("ii", $user_id, $comment_id);
        } else {
            $stmt->bind_param("i", $comment_id);
        }

        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment.']);
            exit;
        }
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Comment deleted successfully.',
            'mode' => 'soft_delete',
            'data' => [
                'display_name' => '[deleted]',
                'content_html' => '<em>Comment deleted by author.</em>'
            ]
        ]);
        exit;
    }

    // Fallback for schemas without soft-delete support
    $stmt = $connection->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment.']);
        exit;
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Comment deleted successfully.',
        'mode' => 'hard_delete'
    ]);
    exit;
}
