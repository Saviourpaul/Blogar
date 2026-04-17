document.addEventListener('DOMContentLoaded', () => {
    const threadRoot = document.querySelector('[data-comment-thread]');
    if (!threadRoot) {
        return;
    }

    const csrfToken = threadRoot.dataset.csrfToken || '';
    const composerHome = document.getElementById('comment-composer-home');
    const composerShell = document.getElementById('comment-composer-shell');
    const commentForm = document.getElementById('comment-form');
    const parentIdInput = document.getElementById('parent_id');
    const replyBanner = document.getElementById('replying-to-container');
    const replyName = document.getElementById('reply-name');
    const formTitle = document.getElementById('form-title');
    const commentMessageInput = document.getElementById('commentmessage-input');
    const submitLabel = document.getElementById('comment-submit-label');
    const replyCancelButton = document.getElementById('reply-cancel-button');
    const defaultTitle = formTitle?.dataset.defaultTitle || 'Join the conversation';

    function showToast(icon, title, text = '') {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                timer: 2500,
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

    function getCommentUrl(commentId) {
        const url = new URL(window.location.href);
        url.hash = `comment-${commentId}`;
        return url.toString();
    }

    function highlightComment(commentId) {
        const commentElement = document.getElementById(`comment-${commentId}`);
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
        const score = document.getElementById(`comment-score-${commentId}`);
        if (score) {
            score.textContent = String(data.data.score ?? 0);
        }

        const voteButtons = document.querySelectorAll(`[data-comment-id="${commentId}"][data-comment-vote]`);
        voteButtons.forEach((button) => {
            const voteType = button.dataset.voteType;
            button.classList.toggle('is-active', voteType === data.user_choice);
        });
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
                body.innerHTML = data.data.content_html || '';
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
                text: 'The thread stays readable, but your comment text will be removed.',
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

            const commentElement = document.getElementById(`comment-${commentId}`);
            if (!commentElement) {
                return;
            }

            if (data.mode === 'soft_delete') {
                commentElement.classList.add('is-deleted');

                const author = commentElement.querySelector('.reddit-comment__author');
                const body = document.getElementById(`comment-content-${commentId}`);
                const vote = commentElement.querySelector('.reddit-comment__vote');

                if (author) {
                    author.textContent = data.data.display_name || '[deleted]';
                }

                if (body) {
                    body.hidden = false;
                    body.innerHTML = data.data.content_html || '<em>Comment deleted by author.</em>';
                }

                if (vote) {
                    vote.innerHTML = '<span class="reddit-comment__meta-chip">deleted</span>';
                    vote.classList.add('reddit-comment__vote--static');
                }

                cancelEdit(commentId);

                commentElement.querySelectorAll('[data-comment-reply], [data-comment-edit], [data-comment-delete]').forEach((button) => {
                    button.remove();
                });

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

    function toggleReplies(commentId, button) {
        const children = document.getElementById(`comment-children-${commentId}`);
        if (!children) {
            return;
        }

        const expanded = button.getAttribute('aria-expanded') !== 'false';
        children.hidden = expanded;
        button.setAttribute('aria-expanded', expanded ? 'false' : 'true');

        const label = expanded ? button.dataset.collapsedLabel : button.dataset.expandedLabel;
        if (label) {
            const labelNode = button.querySelector('[data-comment-toggle-label]');
            if (labelNode) {
                labelNode.textContent = label;
            }
        }
    }

    if (replyCancelButton) {
        replyCancelButton.addEventListener('click', resetReplyComposer);
    }

    if (commentForm) {
        commentForm.addEventListener('submit', () => {
            resetReplyComposer();
        });
    }

    threadRoot.addEventListener('click', (event) => {
        const replyButton = event.target.closest('[data-comment-reply]');
        if (replyButton) {
            moveComposerToReply(replyButton.dataset.commentReply, replyButton.dataset.commentAuthor || 'this comment');
            return;
        }

        const parentLink = event.target.closest('[data-comment-parent-link]');
        if (parentLink) {
            event.preventDefault();
            highlightComment(parentLink.dataset.commentParentLink);
            return;
        }

        const shareButton = event.target.closest('[data-comment-share]');
        if (shareButton) {
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
            startEdit(editButton.dataset.commentEdit);
            return;
        }

        const editCancelButton = event.target.closest('[data-comment-edit-cancel]');
        if (editCancelButton) {
            cancelEdit(editCancelButton.dataset.commentEditCancel);
            return;
        }

        const editSaveButton = event.target.closest('[data-comment-edit-save]');
        if (editSaveButton) {
            saveEdit(editSaveButton.dataset.commentEditSave);
            return;
        }

        const deleteButton = event.target.closest('[data-comment-delete]');
        if (deleteButton) {
            deleteComment(deleteButton.dataset.commentDelete);
            return;
        }

        const toggleButton = event.target.closest('[data-comment-toggle]');
        if (toggleButton) {
            toggleReplies(toggleButton.dataset.commentToggle, toggleButton);
        }
    });
});
