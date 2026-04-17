<?php
$composerName = trim((string) (($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));
if ($composerName === '') {
    $composerName = 'Member';
}

$composerAvatarSrc = '';
if (!empty($user['avatar'])) {
    $avatarValue = (string) $user['avatar'];
    if (preg_match('/^(https?:)?\/\//i', $avatarValue) || strpos($avatarValue, 'account/uploads/') === 0) {
        $composerAvatarSrc = $avatarValue;
    } else {
        $composerAvatarSrc = 'account/uploads/' . ltrim($avatarValue, '/');
    }
}

if (!function_exists('renderCommentThread')) {
    function renderCommentThread(
        array $tree,
        array $commentIndex,
        array &$replyCountCache,
        int $current_user_id,
        int $edit_window,
        bool $commentHasEditExpiresAt,
        bool $commentHasDeletedAt,
        bool $commentInteractionsEnabled,
        int $parent_id = 0,
        int $level = 0
    ): void {
        if (!isset($tree[$parent_id])) {
            return;
        }
        ?>
        <div class="reddit-thread-list<?= $level > 0 ? ' reddit-thread-list--nested' : '' ?>">
            <?php foreach ($tree[$parent_id] as $comment): ?>
                <?php
                $commentId = (int) ($comment['id'] ?? 0);
                $isDeleted = $commentHasDeletedAt && !empty($comment['deleted_at']);
                $displayName = trim((string) (($comment['firstname'] ?? '') . ' ' . ($comment['lastname'] ?? '')));
                if ($displayName === '') {
                    $displayName = (string) ($comment['username'] ?? $comment['name'] ?? 'Member');
                }
                if ($isDeleted) {
                    $displayName = '[deleted]';
                }

                $isCommentAuthor = ((int) ($comment['user_id'] ?? 0) === $current_user_id);
                $rawMessage = (string) ($comment['message'] ?? '');
                $bodyHtml = $isDeleted
                    ? '<em>Comment deleted by author.</em>'
                    : nl2br(htmlspecialchars($rawMessage, ENT_QUOTES, 'UTF-8'));
                $createdAt = (string) ($comment['created_at'] ?? '');
                $createdTimestamp = $createdAt !== '' ? strtotime($createdAt) : false;
                $createdAtTitle = $createdTimestamp ? date('M j, Y \a\t g:i A', $createdTimestamp) : '';
                $relativeTime = htmlspecialchars(formatCompactRelativeTime($createdAt), ENT_QUOTES, 'UTF-8');
                $replyCount = countThreadReplies($tree, $commentId, $replyCountCache);
                $hasChildren = !empty($tree[$commentId]);
                $likes = (int) ($comment['likes'] ?? 0);
                $dislikes = (int) ($comment['dislikes'] ?? 0);
                $score = $likes - $dislikes;
                $userVote = !empty($comment['user_liked']) ? 'like' : (!empty($comment['user_disliked']) ? 'dislike' : '');
                $isEdited = !$isDeleted && (
                    !empty($comment['is_edited']) ||
                    (!empty($comment['edited_at']) && (string) $comment['edited_at'] !== (string) $comment['created_at'])
                );
                $editDeadline = $commentHasEditExpiresAt && !empty($comment['edit_expires_at'])
                    ? strtotime((string) $comment['edit_expires_at'])
                    : ($createdTimestamp ? $createdTimestamp + ($edit_window * 60) : false);
                $minutesRemaining = $editDeadline ? max(0, (int) ceil(($editDeadline - time()) / 60)) : 0;
                $canEdit = $isCommentAuthor && !$isDeleted && $editDeadline && time() <= $editDeadline;
                $parentCommentId = (int) ($comment['parent_id'] ?? 0);
                $parentDisplayName = '';
                if ($parentCommentId > 0 && isset($commentIndex[$parentCommentId])) {
                    $parentComment = $commentIndex[$parentCommentId];
                    $parentDisplayName = trim((string) (($parentComment['firstname'] ?? '') . ' ' . ($parentComment['lastname'] ?? '')));
                    if ($parentDisplayName === '') {
                        $parentDisplayName = (string) ($parentComment['username'] ?? $parentComment['name'] ?? 'Member');
                    }
                    if ($commentHasDeletedAt && !empty($parentComment['deleted_at'])) {
                        $parentDisplayName = '[deleted]';
                    }
                }
                $replyLabel = $replyCount . ' ' . ($replyCount === 1 ? 'reply' : 'replies');
                ?>
                <article class="reddit-comment<?= $isDeleted ? ' is-deleted' : '' ?>" id="comment-<?= $commentId ?>">
                    <div class="reddit-comment__card">
                        <div class="reddit-comment__main">
                            <div class="reddit-comment__vote<?= ($isDeleted || !$commentInteractionsEnabled) ? ' reddit-comment__vote--static' : '' ?>">
                                <?php if ($isDeleted): ?>
                                    <span class="reddit-comment__meta-chip">deleted</span>
                                <?php elseif (!$commentInteractionsEnabled): ?>
                                    <span class="reddit-comment__meta-chip">voting unavailable</span>
                                <?php else: ?>
                                    <button type="button"
                                        class="reddit-comment__vote-btn<?= $userVote === 'like' ? ' is-active' : '' ?>"
                                        data-comment-id="<?= $commentId ?>"
                                        data-comment-vote="1"
                                        data-vote-type="like"
                                        aria-label="Upvote comment">
                                        <i class="bx bx-up-arrow-alt"></i>
                                    </button>
                                    <span class="reddit-comment__score" id="comment-score-<?= $commentId ?>"><?= $score ?></span>
                                    <button type="button"
                                        class="reddit-comment__vote-btn<?= $userVote === 'dislike' ? ' is-active' : '' ?>"
                                        data-comment-id="<?= $commentId ?>"
                                        data-comment-vote="1"
                                        data-vote-type="dislike"
                                        aria-label="Downvote comment">
                                        <i class="bx bx-down-arrow-alt"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="reddit-comment__content-wrap">
                                <div class="reddit-comment__meta" id="comment-meta-<?= $commentId ?>">
                                    <span class="reddit-comment__author"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
                                    <a href="#comment-<?= $commentId ?>" class="reddit-comment__action p-0" style="padding:0; border-radius:0;" title="<?= htmlspecialchars($createdAtTitle, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $relativeTime ?>
                                    </a>
                                    <?php if ($isEdited): ?>
                                        <span class="reddit-comment__meta-chip reddit-comment__meta-chip--edit">
                                            <i class="bx bx-edit-alt"></i> edited
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($canEdit && $minutesRemaining > 0): ?>
                                        <span class="reddit-comment__meta-chip"><?= $minutesRemaining ?>m left to edit</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($parentDisplayName !== '' && !$isDeleted): ?>
                                    <div class="reddit-comment__context">
                                        replying to <?= htmlspecialchars($parentDisplayName, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>

                                <div class="reddit-comment__body" id="comment-content-<?= $commentId ?>"><?= $bodyHtml ?></div>

                                <?php if ($isCommentAuthor && !$isDeleted): ?>
                                    <div class="reddit-comment__edit" id="comment-edit-form-<?= $commentId ?>" hidden>
                                        <textarea id="comment-edit-input-<?= $commentId ?>" maxlength="5000" placeholder="Edit your comment..."><?= htmlspecialchars($rawMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <div class="reddit-comment__edit-actions">
                                            <button type="button" class="btn btn-light rounded-pill" data-comment-edit-cancel="<?= $commentId ?>">Cancel</button>
                                            <button type="button" class="btn btn-primary rounded-pill" data-comment-edit-save="<?= $commentId ?>">Save changes</button>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="reddit-comment__actions">
                                    <?php if (!$isDeleted): ?>
                                        <button type="button"
                                            class="reddit-comment__action"
                                            data-comment-reply="<?= $commentId ?>"
                                            data-comment-author="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="mdi mdi-reply"></i>
                                            Reply
                                        </button>
                                    <?php endif; ?>

                                    <button type="button"
                                        class="reddit-comment__action"
                                        data-comment-share="<?= $commentId ?>">
                                        <i class="mdi mdi-share-outline"></i>
                                        Share
                                    </button>

                                    <a href="#comment-<?= $commentId ?>" class="reddit-comment__action">
                                        <i class="mdi mdi-link-variant"></i>
                                        Permalink
                                    </a>

                                    <?php if ($parentCommentId > 0): ?>
                                        <button type="button"
                                            class="reddit-comment__action"
                                            data-comment-parent-link="<?= $parentCommentId ?>">
                                            <i class="bx bx-reply-all"></i>
                                            Parent
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($hasChildren): ?>
                                        <button type="button"
                                            class="reddit-comment__action"
                                            data-comment-toggle="<?= $commentId ?>"
                                            data-expanded-label="Hide replies"
                                            data-collapsed-label="Show <?= htmlspecialchars($replyLabel, ENT_QUOTES, 'UTF-8') ?>"
                                            aria-expanded="true">
                                            <i class="bx bx-git-branch"></i>
                                            <span data-comment-toggle-label>Hide replies</span>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($isCommentAuthor && !$isDeleted): ?>
                                        <?php if ($canEdit): ?>
                                            <button type="button"
                                                class="reddit-comment__action"
                                                data-comment-edit="<?= $commentId ?>">
                                                <i class="bx bx-edit-alt"></i>
                                                Edit
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="reddit-comment__action is-disabled" disabled>
                                                <i class="bx bx-time-five"></i>
                                                Edit expired
                                            </button>
                                        <?php endif; ?>

                                        <button type="button"
                                            class="reddit-comment__action"
                                            data-comment-delete="<?= $commentId ?>">
                                            <i class="bx bx-trash-alt"></i>
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="reddit-comment__reply-slot" id="comment-reply-slot-<?= $commentId ?>"></div>

                        <?php if ($hasChildren): ?>
                            <div class="reddit-comment__children" id="comment-children-<?= $commentId ?>">
                                <?php renderCommentThread($tree, $commentIndex, $replyCountCache, $current_user_id, $edit_window, $commentHasEditExpiresAt, $commentHasDeletedAt, $commentInteractionsEnabled, $commentId, $level + 1); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
?>

<div class="post-discussion-shell" id="comments">
    <div class="reddit-discussion">
        <div class="reddit-discussion__header">
            <div>
                <div class="reddit-discussion__eyebrow">
                    <i class="bx bx-message-dots"></i>
                    Community Thread
                </div>
                <h5 class="reddit-discussion__title">Comments that branch like a real discussion</h5>
                <p class="reddit-discussion__summary">Reply inline, follow nested threads, and share direct links to any comment.</p>
            </div>
            <div class="reddit-discussion__count">
                <i class="bx bx-comment-dots"></i>
                <span><?= $discussionCount ?></span>
            </div>
        </div>

        <?php if ($is_comment_enabled): ?>
            <div id="comment-composer-home">
                <div class="reddit-composer-shell" id="comment-composer-shell">
                    <div class="card-body">
                        <div class="reddit-composer__identity">
                            <div class="reddit-composer__avatar">
                                <?php if ($composerAvatarSrc !== ''): ?>
                                    <img src="<?= htmlspecialchars($composerAvatarSrc, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($composerName, ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <?= htmlspecialchars(strtoupper(substr($composerName, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="reddit-composer__identity-label">Commenting as</span>
                                <span class="reddit-composer__identity-name"><?= htmlspecialchars($composerName, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>

                        <form action="comments" method="post" id="comment-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                            <input type="hidden" name="parent_id" id="parent_id" value="0">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($composerName, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="reddit-composer__replying" id="replying-to-container" hidden>
                                <i class="mdi mdi-reply"></i>
                                <span>Replying to <span id="reply-name"></span></span>
                            </div>

                            <textarea
                                class="reddit-composer__textarea"
                                name="message"
                                id="commentmessage-input"
                                placeholder="What do you think?"
                                rows="4"
                                maxlength="5000"
                                required></textarea>

                            <div class="reddit-composer__actions">
                                <div class="reddit-composer__hint" id="form-title" data-default-title="Join the conversation">
                                    Add context, answer a question, or start a new branch.
                                </div>
                                <div class="reddit-composer__buttons">
                                    <button type="button" class="btn btn-light rounded-pill" id="reply-cancel-button" hidden>Cancel reply</button>
                                    <button type="submit" name="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="mdi mdi-send me-2"></i>
                                        <span id="comment-submit-label">Comment</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mb-0">
                Comments are currently disabled.
            </div>
        <?php endif; ?>

        <div class="comment-area mb-0" data-comment-thread data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <?php if (empty($commentTree)): ?>
                <div class="text-center py-4 py-md-5 bg-light rounded-3 border border-dashed border-secondary border-opacity-25 px-3">
                    <i class="bx bx-conversation display-4 text-muted mb-3 opacity-50"></i>
                    <h5 class="text-muted fw-normal fs-6 fs-md-5">No comments yet</h5>
                    <p class="text-muted small mb-0">Be the first to start the conversation.</p>
                </div>
            <?php else: ?>
                <?php renderCommentThread($commentTree, $commentDataMap, $commentReplyCountCache, $current_user_id, $edit_window, $commentHasEditExpiresAt, $commentHasDeletedAt, $commentInteractionsEnabled); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
