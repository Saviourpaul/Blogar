function getInteractionElements(postId) {
    return {
        likeIcon: document.getElementById(`like-icon-${postId}`),
        likeCount: document.getElementById(`likes-${postId}`),
        dislikeIcon: document.getElementById(`dislike-icon-${postId}`),
        dislikeCount: document.getElementById(`dislikes-${postId}`),
        shareCount: document.getElementById(`shares-${postId}`)
    };
}

function updateReactionState(elements, userChoice) {
    const { likeIcon, likeCount, dislikeIcon, dislikeCount } = elements;

    if (likeIcon) {
        likeIcon.className = userChoice === 'like'
            ? 'mdi mdi-thumb-up text-success'
            : 'mdi mdi-thumb-up-outline text-muted';
    }

    if (likeCount) {
        likeCount.className = userChoice === 'like' ? 'text-success' : 'text-muted';
    }

    if (dislikeIcon) {
        dislikeIcon.className = userChoice === 'dislike'
            ? 'mdi mdi-thumb-down text-danger'
            : 'mdi mdi-thumb-down-outline text-muted';
    }

    if (dislikeCount) {
        dislikeCount.className = userChoice === 'dislike' ? 'text-danger' : 'text-muted';
    }
}

function getShareUrl(postId) {
    const basePath = window.location.pathname.replace(/[^/]*$/, '');
    return `${window.location.origin}${basePath}postOverview?id=${postId}`;
}

function getShareText(title) {
    return title ? `Check out this post: ${title}` : 'Check out this post on IdeaHub';
}

function ensureShareModal() {
    let modalElement = document.getElementById('sharePostModal');

    if (modalElement) {
        return modalElement;
    }

    modalElement = document.createElement('div');
    modalElement.className = 'modal fade';
    modalElement.id = 'sharePostModal';
    modalElement.tabIndex = -1;
    modalElement.setAttribute('aria-hidden', 'true');
    modalElement.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Share Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Share this post on your preferred platform.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-share-target="native">Share via Device</button>
                        <button type="button" class="btn btn-outline-secondary" data-share-target="copy">Copy Link</button>
                        <button type="button" class="btn btn-outline-success" data-share-target="whatsapp">WhatsApp</button>
                        <button type="button" class="btn btn-outline-primary" data-share-target="facebook">Facebook</button>
                        <button type="button" class="btn btn-outline-dark" data-share-target="x">X</button>
                        <button type="button" class="btn btn-outline-info" data-share-target="linkedin">LinkedIn</button>
                        <button type="button" class="btn btn-outline-primary" data-share-target="telegram">Telegram</button>
                        <button type="button" class="btn btn-outline-danger" data-share-target="email">Email</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modalElement);
    return modalElement;
}

async function handleInteraction(postId, type) {
    const elements = getInteractionElements(postId);
    const targetIcon = type === 'like' ? elements.likeIcon : elements.dislikeIcon;

    if ((type === 'like' || type === 'dislike') && targetIcon) {
        targetIcon.classList.add('interaction-active');
        setTimeout(() => targetIcon.classList.remove('interaction-active'), 200);
    }

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('action', type);

    try {
        const response = await fetch('process_interaction', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Something went wrong.');
        }

        const payload = data.data || {};

        if (elements.likeCount && payload.likes !== undefined) {
            elements.likeCount.innerText = payload.likes;
        }

        if (elements.dislikeCount && payload.dislikes !== undefined) {
            elements.dislikeCount.innerText = payload.dislikes;
        }

        if (elements.shareCount && payload.shares !== undefined) {
            elements.shareCount.innerText = payload.shares;
        }

        if (type === 'like' || type === 'dislike') {
            updateReactionState(elements, data.user_choice || null);
        }

        return data;
    } catch (error) {
        console.error('Interaction failed:', error);
        alert(error.message || 'Interaction failed. Please try again.');
        return null;
    }
}

async function registerShare(postId) {
    return handleInteraction(postId, 'share');
}

async function copyShareLink(postId, title = '') {
    const shareUrl = getShareUrl(postId);

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(shareUrl);
        } else {
            const fallbackInput = document.createElement('input');
            fallbackInput.value = shareUrl;
            document.body.appendChild(fallbackInput);
            fallbackInput.select();
            document.execCommand('copy');
            document.body.removeChild(fallbackInput);
        }

        await registerShare(postId);
        alert('Share link copied successfully.');
    } catch (error) {
        console.error('Share failed:', error);
        alert('Unable to copy the share link right now.');
    }
}

async function shareToPlatform(platform, postId, title = '') {
    const shareUrl = getShareUrl(postId);
    const shareText = getShareText(title);
    const encodedUrl = encodeURIComponent(shareUrl);
    const encodedText = encodeURIComponent(shareText);
    let targetUrl = '';

    switch (platform) {
        case 'whatsapp':
            targetUrl = `https://wa.me/?text=${encodedText}%20${encodedUrl}`;
            break;
        case 'facebook':
            targetUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
            break;
        case 'x':
            targetUrl = `https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedUrl}`;
            break;
        case 'linkedin':
            targetUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`;
            break;
        case 'telegram':
            targetUrl = `https://t.me/share/url?url=${encodedUrl}&text=${encodedText}`;
            break;
        case 'email':
            targetUrl = `mailto:?subject=${encodeURIComponent(title || 'Interesting post')}&body=${encodedText}%0A%0A${encodedUrl}`;
            break;
        default:
            return;
    }

    window.open(targetUrl, '_blank', 'noopener,noreferrer');
    await registerShare(postId);
}

async function shareViaDevice(postId, title = '') {
    const shareUrl = getShareUrl(postId);
    const shareText = getShareText(title);

    if (!navigator.share) {
        await copyShareLink(postId, title);
        return;
    }

    try {
        await navigator.share({
            title: title || 'IdeaHub Post',
            text: shareText,
            url: shareUrl
        });

        await registerShare(postId);
    } catch (error) {
        if (error && error.name !== 'AbortError') {
            console.error('Native share failed:', error);
        }
    }
}

function openShareOptions(postId, title = '') {
    const modalElement = ensureShareModal();
    const nativeButton = modalElement.querySelector('[data-share-target="native"]');
    nativeButton.style.display = navigator.share ? 'block' : 'none';

    modalElement.querySelectorAll('[data-share-target]').forEach((button) => {
        button.onclick = async () => {
            const target = button.getAttribute('data-share-target');

            if (target === 'copy') {
                await copyShareLink(postId, title);
                return;
            }

            if (target === 'native') {
                await shareViaDevice(postId, title);
                return;
            }

            await shareToPlatform(target, postId, title);
        };
    });

    if (window.bootstrap && window.bootstrap.Modal) {
        const modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalElement);
        modalInstance.show();
    } else {
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
    }
}

window.handleInteraction = handleInteraction;
window.copyShareLink = copyShareLink;
window.openShareOptions = openShareOptions;
