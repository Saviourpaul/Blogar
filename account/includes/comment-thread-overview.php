<?php
require_once __DIR__ . '/comment-thread-helpers.php';

$composerName = trim((string) (($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));
if ($composerName === '') {
    $composerName = 'Member';
}

$composerAvatarSrc = commentThreadAvatarUrl($user['avatar'] ?? '');
?>

<div class="post-discussion-shell" id="comments">
    <div class="reddit-discussion">
        <div class="reddit-discussion__header">
            <div class="reddit-discussion__header-left">
                <div class="reddit-discussion__eyebrow">
                    <i class="bx bx-message-dots"></i>
                    Comments
                </div>
                <div class="reddit-discussion__title-row">
                    <h5 class="reddit-discussion__title">Comments</h5>
                    <span class="reddit-discussion__count badge rounded-pill bg-primary-subtle text-primary"><?= (int) $discussionCount ?></span>
                </div>
            </div>
            <div class="reddit-discussion__controls">
                <label for="comment-sort-select" class="reddit-discussion__sort-label">Sort by</label>
                <select id="comment-sort-select" class="form-select form-select-sm">
                    <option value="recent" <?= $commentSort === 'recent' ? 'selected' : '' ?>>Most recent</option>
                    <option value="oldest" <?= $commentSort === 'oldest' ? 'selected' : '' ?>>Oldest first</option>
                </select>
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
                            <input type="hidden" name="comment_page_context" value="<?= (int) $commentPage ?>">
                            <input type="hidden" name="comment_sort_context" value="<?= htmlspecialchars($commentSort, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="reddit-composer__replying" id="replying-to-container" hidden>
                                <i class="mdi mdi-reply-outline"></i>
                                <span>Replying to <span id="reply-name"></span></span>
                            </div>

                            <textarea
                                class="reddit-composer__textarea"
                                name="message"
                                id="commentmessage-input"
                                placeholder="Add comment..."
                                rows="4"
                                maxlength="5000"
                                required></textarea>

                            <div class="reddit-composer__actions">
                                <div class="reddit-composer__hint" id="form-title" data-default-title="Join the conversation">
                                    Add context, answer a question, or start a new branch.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light rounded-pill px-4" id="reply-cancel-button" hidden>Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="comment-submit-button">
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

        <div
            class="comment-area mb-0"
            data-comment-thread
            data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>"
            data-post-id="<?= (int) $post['id'] ?>"
            data-replies-endpoint="account/actions/comment-replies.php"
            data-reply-page-size="<?= (int) $replyPageSize ?>"
            data-comment-sort="<?= htmlspecialchars($commentSort, ENT_QUOTES, 'UTF-8') ?>">
            <?php if (empty($rootComments)): ?>
                <div class="reddit-thread-empty">
                    <i class="bx bx-conversation"></i>
                    <h5>No comments yet</h5>
                    <p>Be the first to start the conversation.</p>
                </div>
            <?php else: ?>
                <?php
                renderCommentThreadList(
                    $rootComments,
                    $current_user_id,
                    $currentUserIsAdmin,
                    $edit_window,
                    $commentHasEditExpiresAt,
                    $commentHasDeletedAt,
                    $commentInteractionsEnabled,
                    0,
                    $maxCommentDepth,
                    []
                );
                ?>

                <?php if ($commentPageCount > 1): ?>
                    <div class="reddit-discussion__pager">
                        <?php if ($commentPage > 1): ?>
                            <a class="reddit-discussion__pager-link" href="<?= htmlspecialchars(buildCommentThreadPageUrl((int) $post['id'], $commentPage - 1, $commentSort), ENT_QUOTES, 'UTF-8') ?>">Newer comments</a>
                        <?php else: ?>
                            <span class="reddit-discussion__pager-link is-disabled">Newer comments</span>
                        <?php endif; ?>

                        <span class="reddit-discussion__pager-status">Page <?= (int) $commentPage ?> of <?= (int) $commentPageCount ?></span>

                        <?php if ($commentPage < $commentPageCount): ?>
                            <a class="reddit-discussion__pager-link" href="<?= htmlspecialchars(buildCommentThreadPageUrl((int) $post['id'], $commentPage + 1, $commentSort), ENT_QUOTES, 'UTF-8') ?>">Older comments</a>
                        <?php else: ?>
                            <span class="reddit-discussion__pager-link is-disabled">Older comments</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
