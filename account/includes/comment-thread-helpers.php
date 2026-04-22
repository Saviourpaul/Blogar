<?php

if (!function_exists('normalizeCommentThreadSort')) {
    function normalizeCommentThreadSort($sort): string
    {
        $sort = strtolower(trim((string) $sort));
        return $sort === 'oldest' ? 'oldest' : 'recent';
    }
}

if (!function_exists('commentThreadRelativeTime')) {
    function commentThreadRelativeTime($datetime): string
    {
        $timestamp = strtotime((string) $datetime);
        if ($timestamp === false) {
            return 'just now';
        }

        $diff = max(0, time() - $timestamp);
        if ($diff < 60) {
            return 'just now';
        }

        $units = [
            ['seconds' => 31536000, 'singular' => 'year', 'plural' => 'years'],
            ['seconds' => 2592000, 'singular' => 'month', 'plural' => 'months'],
            ['seconds' => 86400, 'singular' => 'day', 'plural' => 'days'],
            ['seconds' => 3600, 'singular' => 'hour', 'plural' => 'hours'],
            ['seconds' => 60, 'singular' => 'minute', 'plural' => 'minutes'],
        ];

        foreach ($units as $unit) {
            if ($diff >= $unit['seconds']) {
                $value = (int) floor($diff / $unit['seconds']);
                $label = $value === 1 ? $unit['singular'] : $unit['plural'];
                return $value . ' ' . $label . ' ago';
            }
        }

        return 'just now';
    }
}

if (!function_exists('commentThreadAvatarUrl')) {
    function commentThreadAvatarUrl($avatarValue): string
    {
        $avatarValue = trim((string) $avatarValue);
        if ($avatarValue === '') {
            return '';
        }

        if (preg_match('/^(https?:)?\/\//i', $avatarValue) || strpos($avatarValue, 'account/uploads/') === 0) {
            return $avatarValue;
        }

        return 'account/uploads/' . ltrim($avatarValue, '/');
    }
}

if (!function_exists('fetchCommentThreadSummary')) {
    function fetchCommentThreadSummary(mysqli $connection, int $postId): array
    {
        $stmt = $connection->prepare("
            SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN COALESCE(parent_id, 0) = 0 THEN 1 ELSE 0 END) AS root_count
            FROM comments
            WHERE post_id = ?
        ");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $summary = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();

        return [
            'total_count' => (int) ($summary['total_count'] ?? 0),
            'root_count' => (int) ($summary['root_count'] ?? 0),
        ];
    }
}

if (!function_exists('countCommentThreadChildren')) {
    function countCommentThreadChildren(mysqli $connection, int $postId, int $parentId): int
    {
        $stmt = $connection->prepare("
            SELECT COUNT(*) AS total
            FROM comments
            WHERE post_id = ? AND COALESCE(parent_id, 0) = ?
        ");
        $stmt->bind_param('ii', $postId, $parentId);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) ($count['total'] ?? 0);
    }
}

if (!function_exists('fetchCommentThreadNodes')) {
    function fetchCommentThreadNodes(
        mysqli $connection,
        int $postId,
        int $currentUserId,
        int $parentId = 0,
        int $limit = 10,
        int $offset = 0,
        string $sort = 'recent'
    ): array {
        $sort = normalizeCommentThreadSort($sort);
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        $commentHasUserId = dbColumnExists($connection, 'comments', 'user_id');
        $commentInteractionsEnabled = dbTableExists($connection, 'comment_interactions');

        $select = [
            'c.*',
            'COALESCE(reply_counts.reply_count, 0) AS reply_count',
        ];
        $joins = [
            "LEFT JOIN (
                SELECT COALESCE(parent_id, 0) AS parent_id, COUNT(*) AS reply_count
                FROM comments
                WHERE post_id = ?
                GROUP BY COALESCE(parent_id, 0)
            ) reply_counts ON reply_counts.parent_id = c.id",
        ];

        if ($commentHasUserId) {
            $select[] = 'u.username';
            $select[] = 'u.firstname';
            $select[] = 'u.lastname';
            $select[] = 'u.avatar';
            $select[] = 'u.profile_role';
            $select[] = 'u.is_admin';
            $joins[] = 'LEFT JOIN users u ON c.user_id = u.id';
        } else {
            $select[] = "'' AS username";
            $select[] = "'' AS firstname";
            $select[] = "'' AS lastname";
            $select[] = "'' AS avatar";
            $select[] = "'' AS profile_role";
            $select[] = '0 AS is_admin';
        }

        if ($commentInteractionsEnabled) {
            $select[] = 'COALESCE(interactions.likes, 0) AS likes';
            $select[] = 'COALESCE(interactions.dislikes, 0) AS dislikes';
            $select[] = 'COALESCE(interactions.shares, 0) AS shares';
            $select[] = 'COALESCE(viewer_votes.user_liked, 0) AS user_liked';
            $select[] = 'COALESCE(viewer_votes.user_disliked, 0) AS user_disliked';

            $joins[] = "LEFT JOIN (
                SELECT
                    comment_id,
                    SUM(CASE WHEN interaction_type = 'like' THEN 1 ELSE 0 END) AS likes,
                    SUM(CASE WHEN interaction_type = 'dislike' THEN 1 ELSE 0 END) AS dislikes,
                    SUM(CASE WHEN interaction_type = 'share' THEN 1 ELSE 0 END) AS shares
                FROM comment_interactions
                GROUP BY comment_id
            ) interactions ON interactions.comment_id = c.id";
            $joins[] = "LEFT JOIN (
                SELECT
                    comment_id,
                    MAX(CASE WHEN interaction_type = 'like' THEN 1 ELSE 0 END) AS user_liked,
                    MAX(CASE WHEN interaction_type = 'dislike' THEN 1 ELSE 0 END) AS user_disliked
                FROM comment_interactions
                WHERE user_id = ?
                GROUP BY comment_id
            ) viewer_votes ON viewer_votes.comment_id = c.id";
        } else {
            $select[] = '0 AS likes';
            $select[] = '0 AS dislikes';
            $select[] = '0 AS shares';
            $select[] = '0 AS user_liked';
            $select[] = '0 AS user_disliked';
        }

        $orderBy = $sort === 'oldest'
            ? 'c.created_at ASC, c.id ASC'
            : 'c.created_at DESC, c.id DESC';

        $sql = "
            SELECT " . implode(",\n                ", $select) . "
            FROM comments c
            " . implode("\n            ", $joins) . "
            WHERE c.post_id = ? AND COALESCE(c.parent_id, 0) = ?
            ORDER BY $orderBy
            LIMIT ? OFFSET ?
        ";

        if ($commentInteractionsEnabled) {
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('iiiiii', $postId, $currentUserId, $postId, $parentId, $limit, $offset);
        } else {
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('iiiii', $postId, $postId, $parentId, $limit, $offset);
        }

        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}

if (!function_exists('renderCommentThreadList')) {
    function renderCommentThreadList(
        array $comments,
        int $currentUserId,
        bool $currentUserIsAdmin,
        int $editWindow,
        bool $commentHasEditExpiresAt,
        bool $commentHasDeletedAt,
        bool $commentInteractionsEnabled,
        int $level = 0,
        int $maxDepth = 4,
        array $ancestorPath = []
    ): void {
        if (empty($comments)) {
            return;
        }

        $isFlattenedList = $level > $maxDepth;
        ?>
        <div class="reddit-thread-list<?= $level > 0 ? ' reddit-thread-list--nested' : '' ?><?= $isFlattenedList ? ' reddit-thread-list--flattened' : '' ?>" data-thread-level="<?= $level ?>">
            <?php foreach ($comments as $comment): ?>
                <?php
                $commentId = (int) ($comment['id'] ?? 0);
                $isDeleted = $commentHasDeletedAt && !empty($comment['deleted_at']);
                $isAuthor = ((int) ($comment['user_id'] ?? 0) === $currentUserId);
                $canModerate = $currentUserIsAdmin || $isAuthor;

                $displayName = trim((string) (($comment['firstname'] ?? '') . ' ' . ($comment['lastname'] ?? '')));
                if ($displayName === '') {
                    $displayName = (string) ($comment['username'] ?? $comment['name'] ?? 'Member');
                }
                if ($isDeleted) {
                    $displayName = '[deleted]';
                }

                $avatarSrc = $isDeleted ? '' : commentThreadAvatarUrl($comment['avatar'] ?? '');
                $avatarLetter = strtoupper(substr($isDeleted ? 'Deleted' : $displayName, 0, 1));
                $rawMessage = (string) ($comment['message'] ?? '');
                $bodyHtml = $isDeleted
                    ? '<em>Comment deleted by author.</em>'
                    : nl2br(htmlspecialchars($rawMessage, ENT_QUOTES, 'UTF-8'));

                $createdAt = (string) ($comment['created_at'] ?? '');
                $createdTimestamp = $createdAt !== '' ? strtotime($createdAt) : false;
                $createdAtTitle = $createdTimestamp ? date('M j, Y \a\t g:i A', $createdTimestamp) : '';
                $relativeTime = commentThreadRelativeTime($createdAt);

                $likes = (int) ($comment['likes'] ?? 0);
                $dislikes = (int) ($comment['dislikes'] ?? 0);
                $replyCount = (int) ($comment['reply_count'] ?? 0);
                $hasChildren = $replyCount > 0;
                $userVote = !empty($comment['user_liked']) ? 'like' : (!empty($comment['user_disliked']) ? 'dislike' : '');
                $isEdited = !$isDeleted && (
                    !empty($comment['is_edited']) ||
                    (!empty($comment['edited_at']) && (string) $comment['edited_at'] !== (string) ($comment['created_at'] ?? ''))
                );

                $editDeadline = $commentHasEditExpiresAt && !empty($comment['edit_expires_at'])
                    ? strtotime((string) $comment['edit_expires_at'])
                    : ($createdTimestamp ? $createdTimestamp + ($editWindow * 60) : false);
                $canEdit = $canModerate && !$isDeleted && (
                    $currentUserIsAdmin ||
                    ($editDeadline && time() <= $editDeadline)
                );

                $commentPathValue = implode(',', $ancestorPath);
                $collapsedLabel = 'View replies (' . $replyCount . ')';
                $nextLevel = $level + 1;
                $isFlattenedComment = $level > $maxDepth;
                ?>
                <article
                    class="reddit-comment<?= $isDeleted ? ' is-deleted' : '' ?><?= $isFlattenedComment ? ' reddit-comment--flattened' : '' ?>"
                    id="comment-<?= $commentId ?>"
                    data-comment-id="<?= $commentId ?>"
                    data-comment-created-at="<?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?>"
                    data-depth="<?= $level ?>"
                    data-comment-path="<?= htmlspecialchars($commentPathValue, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="reddit-comment__container">
                        <div class="reddit-comment__avatar-col">
                            <?php if ($avatarSrc !== ''): ?>
                                <img src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>" class="reddit-comment__avatar">
                            <?php else: ?>
                                <div class="reddit-comment__avatar reddit-comment__avatar--placeholder"><?= htmlspecialchars($avatarLetter, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="reddit-comment__content">
                            <div class="reddit-comment__header">
                                <div class="reddit-comment__author-wrap">
                                    <span class="reddit-comment__author"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (!$isDeleted && !empty($comment['is_admin'])): ?>
                                        <span class="reddit-comment__meta-chip reddit-comment__meta-chip--role">Admin</span>
                                    <?php endif; ?>
                                </div>
                                <div class="reddit-comment__meta" id="comment-meta-<?= $commentId ?>">
                                    <span class="reddit-comment__timestamp" title="<?= htmlspecialchars($createdAtTitle, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($relativeTime, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if ($isEdited): ?>
                                        <span class="reddit-comment__meta-chip reddit-comment__meta-chip--edit"><i class="bx bx-edit-alt"></i> edited</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="reddit-comment__body" id="comment-content-<?= $commentId ?>"><?= $bodyHtml ?></div>

                            <div class="reddit-comment__actions-bar">
                                <?php if (!$isDeleted && $commentInteractionsEnabled): ?>
                                    <div class="reddit-comment__votes">
                                        <button
                                            type="button"
                                            class="reddit-comment__vote-btn<?= $userVote === 'like' ? ' is-active' : '' ?>"
                                            data-comment-id="<?= $commentId ?>"
                                            data-comment-vote="1"
                                            data-vote-type="like"
                                            aria-label="Like comment">
                                            <i class="mdi mdi-thumb-up-outline"></i>
                                            <span data-comment-like-count><?= $likes ?></span>
                                        </button>
                                        <button
                                            type="button"
                                            class="reddit-comment__vote-btn<?= $userVote === 'dislike' ? ' is-active' : '' ?>"
                                            data-comment-id="<?= $commentId ?>"
                                            data-comment-vote="1"
                                            data-vote-type="dislike"
                                            aria-label="Dislike comment">
                                            <i class="mdi mdi-thumb-down-outline"></i>
                                            <span data-comment-dislike-count><?= $dislikes ?></span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if (!$isDeleted): ?>
                                    <button
                                        type="button"
                                        class="reddit-comment__action"
                                        data-comment-reply="<?= $commentId ?>"
                                        data-comment-author="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="mdi mdi-reply-outline"></i>
                                        Reply
                                    </button>
                                <?php endif; ?>

                                <div class="dropdown">
                                    <button type="button" class="reddit-comment__action" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="mdi mdi-dots-horizontal"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button type="button" class="dropdown-item" data-comment-share="<?= $commentId ?>"><i class="mdi mdi-share-outline"></i> Share</button></li>
                                        <li><a class="dropdown-item" href="#comment-<?= $commentId ?>"><i class="mdi mdi-link-variant"></i> Permalink</a></li>
                                        <?php if ($canModerate && !$isDeleted): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php if ($canEdit): ?>
                                                <li><a class="dropdown-item" href="#" data-comment-edit="<?= $commentId ?>"><i class="bx bx-edit-alt"></i> Edit</a></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item text-danger" href="#" data-comment-delete="<?= $commentId ?>"><i class="bx bx-trash-alt"></i> Delete</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <?php if ($canModerate && !$isDeleted): ?>
                                <div class="reddit-comment__edit" id="comment-edit-form-<?= $commentId ?>" hidden>
                                    <textarea id="comment-edit-input-<?= $commentId ?>" maxlength="5000" placeholder="Edit your comment..."><?= htmlspecialchars($rawMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    <div class="reddit-comment__edit-actions">
                                        <button type="button" class="btn btn-light rounded-pill" data-comment-edit-cancel="<?= $commentId ?>">Cancel</button>
                                        <button type="button" class="btn btn-primary rounded-pill" data-comment-edit-save="<?= $commentId ?>">Save changes</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="reddit-comment__reply-slot" id="comment-reply-slot-<?= $commentId ?>"></div>

                    <?php if ($hasChildren): ?>
                        <div class="reddit-comment__thread-shell<?= $nextLevel > $maxDepth ? ' reddit-comment__thread-shell--flattened' : '' ?>">
                            <button
                                type="button"
                                class="reddit-comment__thread-toggle"
                                data-comment-toggle="<?= $commentId ?>"
                                data-comment-level="<?= $nextLevel ?>"
                                data-collapsed-label="<?= htmlspecialchars($collapsedLabel, ENT_QUOTES, 'UTF-8') ?>"
                                data-expanded-label="Hide replies"
                                aria-expanded="false">
                                <i class="mdi mdi-chevron-down"></i>
                                <span data-comment-toggle-label><?= htmlspecialchars($collapsedLabel, ENT_QUOTES, 'UTF-8') ?></span>
                            </button>
                            <div
                                class="reddit-comment__children<?= $nextLevel > $maxDepth ? ' reddit-comment__children--flattened' : '' ?>"
                                id="comment-children-<?= $commentId ?>"
                                data-loaded="false"
                                data-level="<?= $nextLevel ?>"
                                hidden></div>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if (!function_exists('buildCommentThreadPageUrl')) {
    function buildCommentThreadPageUrl(int $postId, int $page, string $sort): string
    {
        $params = ['id' => $postId];
        if ($sort !== 'recent') {
            $params['comment_sort'] = $sort;
        }
        if ($page > 1) {
            $params['comment_page'] = $page;
        }

        return 'postOverview?' . http_build_query($params) . '#comments';
    }
}
