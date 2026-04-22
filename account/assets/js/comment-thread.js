document.addEventListener('DOMContentLoaded', () => {
    const threadRoot = document.querySelector('[data-comment-thread]');
    if (!threadRoot) {
        return;
    }

    const csrfToken = threadRoot.dataset.csrfToken || '';
    const postId = threadRoot.dataset.postId || '';
    const repliesEndpoint = threadRoot.dataset.repliesEndpoint || 'account/actions/comment-replies.php';
    const replyPageSize = Number.parseInt(threadRoot.dataset.replyPageSize || '8', 10) || 8;

    const composerHome = document.getElementById('comment-composer-home');
    const composerShell = document.getElementById('comment-composer-shell');
    const parentIdInput = document.getElementById('parent_id');
    const replyBanner = document.getElementById('replying-to-container');
    const replyName = document.getElementById('reply-name');
    const formTitle = document.getElementById('form-title');
    const commentMessageInput = document.getElementById('commentmessage-input');
    const submitLabel = document.getElementById('comment-submit-label');
    const replyCancelButton = document.getElementById('reply-cancel-button');
    const sortSelect = document.getElementById('comment-sort-select');
    const defaultTitle = formTitle?.dataset.defaultTitle || 'Join the conversation';

    function showToast(icon, title, text = '') {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                timer: 2600,
                showConfirmButton: false,
                icon,
                title,
                text
            });
            return;
        }

        if (text) {
            window.alert(`${title}\n${text}`);
            return;
        }

        window.alert(title);
    }

    function getCommentElement(commentId) {
        return document.getElementById(`comment-${commentId}`);
    }

    function getCommentUrl(commentId) {
        const url = new URL(window.location.href);
        const commentElement = getCommentElement(commentId);
        const commentPath = commentElement?.dataset.commentPath || '';

        if (commentPath) {
            url.searchParams.set('comment_path', commentPath);
        } else {
            url.searchParams.delete('comment_path');
        }

        url.searchParams.set('comment_focus', String(commentId));
        url.hash = `comment-${commentId}`;
        return url.toString();
    }

    function highlightComment(commentId) {
        const commentElement = getCommentElement(commentId);
        if (!commentElement) {
            return;
        }

        commentElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        commentElement.classList.add('highlight-parent');
        window.setTimeout(() => {
            commentElement.classList.remove('highlight-parent');
        }, 2200);
    }

    function resetReplyComposer() {
        if (parentIdInput) {
            parentIdInput.value = '0';
        }

        if (replyName) {
            replyName.textContent = '';
        }

        if (replyBanner) {
            replyBanner.hidden = true;
        }

        if (replyCancelButton) {
            replyCancelButton.hidden = true;
        }

        if (formTitle) {
            formTitle.textContent = defaultTitle;
        }

        if (submitLabel) {
            submitLabel.textContent = 'Comment';
        }

        if (composerHome && composerShell && composerShell.parentElement !== composerHome) {
            composerHome.appendChild(composerShell);
        }
    }

    function moveComposerToReply(commentId, authorName) {
        const replySlot = document.getElementById(`comment-reply-slot-${commentId}`);
        if (!replySlot || !composerShell) {
            return;
        }

        replySlot.appendChild(composerShell);

        if (parentIdInput) {
            parentIdInput.value = String(commentId);
        }

        if (replyName) {
            replyName.textContent = authorName || 'this comment';
        }

        if (replyBanner) {
            replyBanner.hidden = false;
        }

        if (replyCancelButton) {
            replyCancelButton.hidden = false;
        }

        if (formTitle) {
            formTitle.textContent = `Replying to ${authorName || 'this comment'}`;
        }

        if (submitLabel) {
            submitLabel.textContent = 'Reply';
        }

        composerShell.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });

        if (commentMessageInput) {
            commentMessageInput.focus();
        }
    }

    async function sendFormEncoded(url, params) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: params.toString()
        });

        return response.json();
    }

    function updateVoteUI(commentId, data) {
        const likeButton = threadRoot.querySelector(`[data-comment-id="${commentId}"][data-vote-type="like"]`);
        const dislikeButton = threadRoot.querySelector(`[data-comment-id="${commentId}"][data-vote-type="dislike"]`);

        if (likeButton) {
            const likeCount = likeButton.querySelector('[data-comment-like-count]');
            if (likeCount) {
                likeCount.textContent = String(data.data?.likes ?? 0);
            }
            likeButton.classList.toggle('is-active', data.user_choice === 'like');
        }

        if (dislikeButton) {
            const dislikeCount = dislikeButton.querySelector('[data-comment-dislike-count]');
            if (dislikeCount) {
                dislikeCount.textContent = String(data.data?.dislikes ?? 0);
            }
            dislikeButton.classList.toggle('is-active', data.user_choice === 'dislike');
        }
    }

    async function handleVote(commentId, action) {
        const params = new URLSearchParams({
            comment_id: commentId,
            action,
            csrf_token: csrfToken
        });

        try {
            const data = await sendFormEncoded('account/actions/comment-interactions.php', params);
            if (data.status !== 'success') {
                showToast('error', 'Vote failed', data.message || 'Please try again.');
                return;
            }

            updateVoteUI(commentId, data);
        } catch (error) {
            console.error(error);
            showToast('error', 'Vote failed', 'Please try again.');
        }
    }

    async function recordShare(commentId) {
        const params = new URLSearchParams({
            comment_id: commentId,
            action: 'share',
            csrf_token: csrfToken
        });

        try {
            await sendFormEncoded('account/actions/comment-interactions.php', params);
        } catch (error) {
            console.error(error);
        }
    }

    async function shareComment(commentId) {
        const commentUrl = getCommentUrl(commentId);

        try {
            if (navigator.share) {
                await navigator.share({
                    title: document.title,
                    text: 'Join this comment thread.',
                    url: commentUrl
                });
                showToast('success', 'Share sheet opened');
            } else if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(commentUrl);
                showToast('success', 'Comment link copied');
            } else {
                window.prompt('Copy this comment link:', commentUrl);
            }
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }

            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(commentUrl);
                showToast('success', 'Comment link copied');
            } else {
                window.prompt('Copy this comment link:', commentUrl);
            }
        }

        recordShare(commentId);
    }

    function startEdit(commentId) {
        const body = document.getElementById(`comment-content-${commentId}`);
        const form = document.getElementById(`comment-edit-form-${commentId}`);
        const input = document.getElementById(`comment-edit-input-${commentId}`);

        if (!body || !form) {
            return;
        }

        body.hidden = true;
        form.hidden = false;
        if (input) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }
    }

    function cancelEdit(commentId) {
        const body = document.getElementById(`comment-content-${commentId}`);
        const form = document.getElementById(`comment-edit-form-${commentId}`);

        if (body) {
            body.hidden = false;
        }

        if (form) {
            form.hidden = true;
        }
    }

    function ensureEditedChip(commentId) {
        const meta = document.getElementById(`comment-meta-${commentId}`);
        if (!meta || meta.querySelector('.reddit-comment__meta-chip--edit')) {
            return;
        }

        const chip = document.createElement('span');
        chip.className = 'reddit-comment__meta-chip reddit-comment__meta-chip--edit';
        chip.innerHTML = '<i class="bx bx-edit-alt"></i> edited';
        meta.appendChild(chip);
    }

    async function saveEdit(commentId) {
        const input = document.getElementById(`comment-edit-input-${commentId}`);
        if (!input) {
            return;
        }

        const content = input.value.trim();
        if (!content) {
            showToast('warning', 'Comment cannot be empty');
            return;
        }

        const params = new URLSearchParams({
            comment_id: commentId,
            action: 'edit',
            content,
            csrf_token: csrfToken
        });

        try {
            const data = await sendFormEncoded('account/actions/comment-edit-delete.php', params);
            if (data.status !== 'success') {
                showToast('error', 'Update failed', data.message || 'Please try again.');
                return;
            }

            const body = document.getElementById(`comment-content-${commentId}`);
            if (body) {
                body.innerHTML = data.data?.content_html || '';
                body.hidden = false;
            }

            ensureEditedChip(commentId);
            cancelEdit(commentId);
            showToast('success', 'Comment updated');
        } catch (error) {
            console.error(error);
            showToast('error', 'Update failed', 'Please try again.');
        }
    }

    async function deleteComment(commentId) {
        const confirmed = window.Swal
            ? await Swal.fire({
                title: 'Delete this comment?',
                text: 'The thread stays readable, but this comment text will be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Delete comment'
            }).then((result) => result.isConfirmed)
            : window.confirm('Delete this comment?');

        if (!confirmed) {
            return;
        }

        const params = new URLSearchParams({
            comment_id: commentId,
            action: 'delete',
            csrf_token: csrfToken
        });

        try {
            const data = await sendFormEncoded('account/actions/comment-edit-delete.php', params);
            if (data.status !== 'success') {
                showToast('error', 'Delete failed', data.message || 'Please try again.');
                return;
            }

            if (parentIdInput && parentIdInput.value === String(commentId)) {
                resetReplyComposer();
            }

            const commentElement = getCommentElement(commentId);
            if (!commentElement) {
                return;
            }

            if (data.mode === 'soft_delete') {
                commentElement.classList.add('is-deleted');

                const author = commentElement.querySelector('.reddit-comment__author');
                const body = document.getElementById(`comment-content-${commentId}`);
                const votes = commentElement.querySelector('.reddit-comment__votes');
                const replyButton = commentElement.querySelector(`[data-comment-reply="${commentId}"]`);
                const editForm = document.getElementById(`comment-edit-form-${commentId}`);

                if (author) {
                    author.textContent = data.data?.display_name || '[deleted]';
                }

                if (body) {
                    body.hidden = false;
                    body.innerHTML = data.data?.content_html || '<em>Comment deleted by author.</em>';
                }

                votes?.remove();
                replyButton?.remove();
                editForm?.remove();

                commentElement.querySelectorAll(`[data-comment-edit="${commentId}"], [data-comment-delete="${commentId}"]`).forEach((node) => {
                    const parent = node.closest('li');
                    if (parent) {
                        parent.remove();
                    } else {
                        node.remove();
                    }
                });

                const divider = commentElement.querySelector('.dropdown-divider');
                if (divider && !commentElement.querySelector('[data-comment-edit], [data-comment-delete]')) {
                    const dividerParent = divider.closest('li');
                    if (dividerParent) {
                        dividerParent.remove();
                    } else {
                        divider.remove();
                    }
                }

                showToast('success', 'Comment deleted');
                return;
            }

            commentElement.remove();
            showToast('success', 'Comment deleted');
        } catch (error) {
            console.error(error);
            showToast('error', 'Delete failed', 'Please try again.');
        }
    }

    function setToggleState(button, expanded) {
        if (!button) {
            return;
        }

        const shell = button.closest('.reddit-comment__thread-shell');
        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        button.classList.toggle('is-expanded', expanded);
        if (shell) {
            shell.classList.toggle('is-expanded', expanded);
        }
        const labelNode = button.querySelector('[data-comment-toggle-label]');
        if (labelNode) {
            labelNode.textContent = expanded ? (button.dataset.expandedLabel || 'Hide replies') : (button.dataset.collapsedLabel || 'View replies');
        }
    }

    function upsertLoadMoreButton(parentId, container, meta) {
        const existingButton = container.querySelector('[data-comment-load-more]');
        if (!meta?.has_more) {
            existingButton?.remove();
            return;
        }

        const remaining = Math.max(0, Number(meta.total || 0) - Number(meta.next_offset || 0));
        const label = remaining > 0 ? `Load more replies (${remaining} left)` : 'Load more replies';

        const button = existingButton || document.createElement('button');
        button.type = 'button';
        button.className = 'reddit-thread-load-more';
        button.dataset.commentLoadMore = String(parentId);
        button.dataset.offset = String(meta.next_offset || 0);
        button.textContent = label;

        if (!existingButton) {
            container.appendChild(button);
        }
    }

    function appendReplyMarkup(container, html) {
        if (!html) {
            return;
        }

        const parser = document.createElement('div');
        parser.innerHTML = html;

        const incomingList = parser.querySelector('.reddit-thread-list');
        const existingList = container.querySelector('.reddit-thread-list');

        if (incomingList && existingList) {
            Array.from(incomingList.children).forEach((child) => {
                existingList.appendChild(child);
            });
            return;
        }

        if (incomingList) {
            container.appendChild(incomingList);
            return;
        }

        container.insertAdjacentHTML('beforeend', html);
    }

    function findToggleButton(commentId) {
        return threadRoot.querySelector(`[data-comment-toggle="${commentId}"]`);
    }

    async function loadReplies(parentId, button = null, options = {}) {
        const commentElement = getCommentElement(parentId);
        const container = document.getElementById(`comment-children-${parentId}`);
        if (!commentElement || !container) {
            return false;
        }

        if (container.dataset.loading === 'true') {
            return false;
        }

        const toggleButton = button || findToggleButton(parentId);
        const ancestorPath = commentElement.dataset.commentPath || '';
        const level = Number.parseInt(container.dataset.level || toggleButton?.dataset.commentLevel || '1', 10) || 1;
        const offset = Number.parseInt(options.offset ?? container.dataset.nextOffset ?? '0', 10) || 0;

        container.dataset.loading = 'true';
        if (toggleButton) {
            toggleButton.disabled = true;
        }

        const params = new URLSearchParams({
            csrf_token: csrfToken,
            post_id: String(postId),
            parent_id: String(parentId),
            offset: String(offset),
            limit: String(replyPageSize),
            level: String(level),
            sort: 'oldest',
            ancestor_path: ancestorPath
        });

        try {
            const data = await sendFormEncoded(repliesEndpoint, params);
            if (data.status !== 'success') {
                showToast('error', 'Could not load replies', data.message || 'Please try again.');
                return false;
            }

            if (options.append) {
                appendReplyMarkup(container, data.html || '');
            } else {
                container.innerHTML = data.html || '';
            }

            container.dataset.loaded = 'true';
            container.dataset.nextOffset = String(data.meta?.next_offset || 0);
            container.hidden = false;
            upsertLoadMoreButton(parentId, container, data.meta || {});
            setToggleState(toggleButton, true);
            return true;
        } catch (error) {
            console.error(error);
            showToast('error', 'Could not load replies', 'Please try again.');
            return false;
        } finally {
            container.dataset.loading = 'false';
            if (toggleButton) {
                toggleButton.disabled = false;
            }
        }
    }

    async function toggleReplies(commentId, button) {
        const container = document.getElementById(`comment-children-${commentId}`);
        if (!container) {
            return;
        }

        const isExpanded = button.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
            container.hidden = true;
            setToggleState(button, false);
            return;
        }

        if (container.dataset.loaded === 'true') {
            container.hidden = false;
            setToggleState(button, true);
            return;
        }

        await loadReplies(commentId, button, { append: false, offset: 0 });
    }

    async function ensureCommentVisibleInThread(parentId, targetCommentId) {
        if (!parentId || !targetCommentId || getCommentElement(targetCommentId)) {
            return Boolean(getCommentElement(targetCommentId));
        }

        const container = document.getElementById(`comment-children-${parentId}`);
        if (!container) {
            return false;
        }

        while (!getCommentElement(targetCommentId)) {
            const loadMoreButton = container.querySelector('[data-comment-load-more]');
            if (!loadMoreButton) {
                break;
            }

            const loaded = await loadReplies(parentId, null, {
                append: true,
                offset: loadMoreButton.dataset.offset || container.dataset.nextOffset || '0'
            });

            if (!loaded) {
                break;
            }
        }

        return Boolean(getCommentElement(targetCommentId));
    }

    async function revealCommentFromUrl() {
        const url = new URL(window.location.href);
        const focusCommentId = url.searchParams.get('comment_focus') || (window.location.hash.startsWith('#comment-') ? window.location.hash.replace('#comment-', '') : '');
        const commentPathRaw = url.searchParams.get('comment_path') || '';

        if (!focusCommentId) {
            return;
        }

        if (commentPathRaw) {
            const commentPath = commentPathRaw
                .split(',')
                .map((segment) => Number.parseInt(segment, 10))
                .filter((segment) => Number.isFinite(segment) && segment > 0);

            for (const ancestorId of commentPath) {
                const toggleButton = findToggleButton(ancestorId);
                const container = document.getElementById(`comment-children-${ancestorId}`);
                if (!toggleButton || !container) {
                    break;
                }

                if (container.dataset.loaded !== 'true') {
                    const loaded = await loadReplies(ancestorId, toggleButton, { append: false, offset: 0 });
                    if (!loaded) {
                        break;
                    }
                } else if (container.hidden) {
                    container.hidden = false;
                    setToggleState(toggleButton, true);
                }
            }

            const lastAncestorId = commentPath[commentPath.length - 1] || 0;
            if (lastAncestorId > 0 && !getCommentElement(focusCommentId)) {
                await ensureCommentVisibleInThread(lastAncestorId, focusCommentId);
            }
        }

        window.setTimeout(() => {
            highlightComment(focusCommentId);
        }, 120);
    }

    if (replyCancelButton) {
        replyCancelButton.addEventListener('click', resetReplyComposer);
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set('comment_sort', sortSelect.value);
            url.searchParams.delete('comment_page');
            url.hash = 'comments';
            window.location.assign(url.toString());
        });
    }

    threadRoot.addEventListener('click', async (event) => {
        const replyButton = event.target.closest('[data-comment-reply]');
        if (replyButton) {
            moveComposerToReply(replyButton.dataset.commentReply, replyButton.dataset.commentAuthor || 'this comment');
            return;
        }

        const shareButton = event.target.closest('[data-comment-share]');
        if (shareButton) {
            event.preventDefault();
            shareComment(shareButton.dataset.commentShare);
            return;
        }

        const voteButton = event.target.closest('[data-comment-vote]');
        if (voteButton) {
            handleVote(voteButton.dataset.commentId, voteButton.dataset.voteType);
            return;
        }

        const editButton = event.target.closest('[data-comment-edit]');
        if (editButton) {
            event.preventDefault();
            startEdit(editButton.dataset.commentEdit);
            return;
        }

        const editCancelButton = event.target.closest('[data-comment-edit-cancel]');
        if (editCancelButton) {
            event.preventDefault();
            cancelEdit(editCancelButton.dataset.commentEditCancel);
            return;
        }

        const editSaveButton = event.target.closest('[data-comment-edit-save]');
        if (editSaveButton) {
            event.preventDefault();
            saveEdit(editSaveButton.dataset.commentEditSave);
            return;
        }

        const deleteButton = event.target.closest('[data-comment-delete]');
        if (deleteButton) {
            event.preventDefault();
            deleteComment(deleteButton.dataset.commentDelete);
            return;
        }

        const toggleButton = event.target.closest('[data-comment-toggle]');
        if (toggleButton) {
            event.preventDefault();
            await toggleReplies(toggleButton.dataset.commentToggle, toggleButton);
            return;
        }

        const loadMoreButton = event.target.closest('[data-comment-load-more]');
        if (loadMoreButton) {
            event.preventDefault();
            loadMoreButton.disabled = true;
            await loadReplies(loadMoreButton.dataset.commentLoadMore, null, {
                append: true,
                offset: loadMoreButton.dataset.offset || '0'
            });
            loadMoreButton.disabled = false;
        }
    });

    window.shareComment = shareComment;
    revealCommentFromUrl();
});
