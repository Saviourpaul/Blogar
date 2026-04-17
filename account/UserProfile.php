<?php $pageTitle = 'Author Profile';
require 'includes/header.php';

$profileUserId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(dirname($scriptPath), '/');
$isRoutedRequest = basename($scriptPath) === 'index.php';
$followActionUrl = $isRoutedRequest
    ? $scriptDir . '/account/actions/follow-toggle.php'
    : $scriptDir . '/actions/follow-toggle.php';

if (!$profileUserId) {
    http_response_code(404);
    die('Invalid user ID');
}

$userColumns = [
    'id',
    'username',
    'email',
    'firstname',
    'lastname',
    'phone',
    'bio',
    'gender',
    'birthday',
    'address1',
    'address2',
    'country',
    'zip_code',
    'avatar'
];

if (dbColumnExists($connection, 'users', 'created_at')) {
    $userColumns[] = 'created_at';
}

$profileStmt = $connection->prepare(
    "SELECT " . implode(', ', $userColumns) . " FROM users WHERE id = ? LIMIT 1"
);
$profileStmt->bind_param('i', $profileUserId);
$profileStmt->execute();
$profileResult = $profileStmt->get_result();
$profileUser = $profileResult->fetch_assoc();
$profileStmt->close();

if (!$profileUser) {
    http_response_code(404);
    die('Author not found');
}

$safeText = static function ($value, $fallback = '') {
    $value = trim(htmlspecialchars_decode((string) ($value ?? ''), ENT_QUOTES));
    if ($value === '') {
        return $fallback;
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};

$displayNameRaw = trim(
    htmlspecialchars_decode((string) ($profileUser['firstname'] ?? ''), ENT_QUOTES) . ' ' .
    htmlspecialchars_decode((string) ($profileUser['lastname'] ?? ''), ENT_QUOTES)
);

if ($displayNameRaw === '') {
    $displayNameRaw = trim(htmlspecialchars_decode((string) ($profileUser['username'] ?? ''), ENT_QUOTES));
}

if ($displayNameRaw === '') {
    $displayNameRaw = 'Community Member';
}

$displayName = htmlspecialchars($displayNameRaw, ENT_QUOTES, 'UTF-8');
$username = $safeText($profileUser['username'] ?? '', 'member');
$bioText = trim(htmlspecialchars_decode((string) ($profileUser['bio'] ?? ''), ENT_QUOTES));
$bioHtml = $bioText !== ''
    ? nl2br(htmlspecialchars($bioText, ENT_QUOTES, 'UTF-8'))
    : 'This author has not added a bio yet.';

$postExcerpt = static function ($content) {
    $content = trim(strip_tags((string) $content));

    if ($content === '') {
        return '';
    }

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($content, 0, 120, '...');
    }

    return strlen($content) > 120 ? substr($content, 0, 117) . '...' : $content;
};

$genderValue = trim((string) ($profileUser['gender'] ?? ''));
$genderLabel = '-';
if ($genderValue === '0') {
    $genderLabel = 'Male';
} elseif ($genderValue === '1') {
    $genderLabel = 'Female';
} elseif ($genderValue !== '') {
    $genderLabel = ucfirst($genderValue);
}

$joinedAt = !empty($profileUser['created_at']) ? strtotime((string) $profileUser['created_at']) : null;
$joinedLabel = $joinedAt ? date('F Y', $joinedAt) : 'Unknown';
$joinedRelative = $joinedAt ? getRelativeTime(date('Y-m-d H:i:s', $joinedAt)) : null;

$isOwnProfile = (int) $loggedInId === (int) $profileUserId;
$followerCount = getFollowerCount($connection, $profileUserId);
$followingCount = getFollowingCount($connection, $profileUserId);
$isCurrentlyFollowing = !$isOwnProfile && isFollowingUser($connection, $loggedInId, $profileUserId);

$postCount = 0;
$postCountStmt = $connection->prepare("SELECT COUNT(*) AS total FROM posts WHERE author_id = ?");
$postCountStmt->bind_param('i', $profileUserId);
$postCountStmt->execute();
$postCountResult = $postCountStmt->get_result()->fetch_assoc();
$postCount = (int) ($postCountResult['total'] ?? 0);
$postCountStmt->close();

$commentCount = 0;
if (dbColumnExists($connection, 'comments', 'user_id')) {
    $commentCountStmt = $connection->prepare("SELECT COUNT(*) AS total FROM comments WHERE user_id = ?");
    $commentCountStmt->bind_param('i', $profileUserId);
    $commentCountStmt->execute();
    $commentCountResult = $commentCountStmt->get_result()->fetch_assoc();
    $commentCount = (int) ($commentCountResult['total'] ?? 0);
    $commentCountStmt->close();
}

$likesReceived = 0;
$likesStmt = $connection->prepare("
    SELECT COUNT(*) AS total
    FROM post_interactions pi
    INNER JOIN posts p ON p.id = pi.post_id
    WHERE p.author_id = ? AND pi.interaction_type = 'like'
");
$likesStmt->bind_param('i', $profileUserId);
$likesStmt->execute();
$likesResult = $likesStmt->get_result()->fetch_assoc();
$likesReceived = (int) ($likesResult['total'] ?? 0);
$likesStmt->close();

$categoryCount = 0;
$categoryCountStmt = $connection->prepare("
    SELECT COUNT(DISTINCT category_id) AS total
    FROM posts
    WHERE author_id = ? AND category_id IS NOT NULL
");
$categoryCountStmt->bind_param('i', $profileUserId);
$categoryCountStmt->execute();
$categoryCountResult = $categoryCountStmt->get_result()->fetch_assoc();
$categoryCount = (int) ($categoryCountResult['total'] ?? 0);
$categoryCountStmt->close();

$recentPostsStmt = $connection->prepare("
    SELECT
        p.id,
        p.title,
        p.thumbnail,
        p.body,
        p.created_at,
        c.title AS category_title,
        (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comment_count,
        (SELECT COUNT(*) FROM post_interactions pi WHERE pi.post_id = p.id AND pi.interaction_type = 'like') AS like_count
    FROM posts p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.author_id = ?
    ORDER BY p.created_at DESC
    LIMIT 6
");
$recentPostsStmt->bind_param('i', $profileUserId);
$recentPostsStmt->execute();
$recentPosts = $recentPostsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentPostsStmt->close();

$profileMeta = [
    ['label' => 'Joined', 'value' => $joinedLabel . ($joinedRelative ? ' | ' . $joinedRelative : '')],
    ['label' => 'Country', 'value' => $safeText($profileUser['country'] ?? '', '-')],
    ['label' => 'Phone', 'value' => $safeText($profileUser['phone'] ?? '', '-')],
    ['label' => 'Birthday', 'value' => $safeText($profileUser['birthday'] ?? '', '-')],
    ['label' => 'Gender', 'value' => htmlspecialchars($genderLabel, ENT_QUOTES, 'UTF-8')],
    ['label' => 'Address', 'value' => $safeText(trim(($profileUser['address1'] ?? '') . ' ' . ($profileUser['address2'] ?? '')), '-')],
    ['label' => 'Comments', 'value' => (string) $commentCount],
    ['label' => 'Categories', 'value' => (string) $categoryCount],
];
?>

<style>
    .author-hero {
        background:
            radial-gradient(circle at top left, rgba(13, 110, 253, 0.22), transparent 34%),
            radial-gradient(circle at bottom right, rgba(25, 135, 84, 0.18), transparent 28%),
            linear-gradient(135deg, #ffffff 0%, #f4f7fb 100%);
        border: 1px solid rgba(13, 110, 253, 0.08);
    }

    .author-avatar-xl {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 45px rgba(17, 24, 39, 0.16);
    }

    .author-avatar-fallback {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: #0d6efd;
        background: #e9f2ff;
        border: 4px solid rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 45px rgba(17, 24, 39, 0.16);
    }

    .author-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 1rem;
        padding: 1.1rem 1.2rem;
        height: 100%;
    }

    .author-meta-card,
    .author-post-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
    }

    .author-post-card img {
        height: 180px;
        object-fit: cover;
    }

    .author-bio {
        color: #526071;
        line-height: 1.75;
        white-space: normal;
    }

    .author-follow-btn.is-following {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }
</style>

<body>
<div id="layout-wrapper">
    <?= include 'includes/sidebar.php' ?>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Author Profile</h4>
                        </div>
                    </div>
                </div>

                <div class="card author-hero shadow-sm mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-auto">
                                <?php if (!empty($profileUser['avatar'])): ?>
                                    <img
                                        src="account/uploads/<?= htmlspecialchars($profileUser['avatar'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= $displayName ?>"
                                        class="author-avatar-xl">
                                <?php else: ?>
                                    <div class="author-avatar-fallback">
                                        <?= htmlspecialchars(strtoupper(substr($displayNameRaw, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                                        <i class="mdi mdi-account-outline me-1"></i>@<?= $username ?>
                                    </span>
                                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                        <i class="mdi mdi-calendar-month-outline me-1"></i>Joined <?= htmlspecialchars($joinedLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                                <h2 class="mb-2"><?= $displayName ?></h2>
                                <p class="author-bio mb-3 mb-lg-4"><?= $bioHtml ?></p>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($isOwnProfile): ?>
                                        <a href="UpdateUser?id=<?= (int) $profileUserId ?>" class="btn btn-primary rounded-pill px-4">
                                            Edit Profile
                                        </a>
                                    <?php else: ?>
                                        <button
                                            type="button"
                                            class="btn rounded-pill px-4 author-follow-btn <?= $isCurrentlyFollowing ? 'btn-success is-following' : 'btn-primary' ?>"
                                            id="follow-toggle-btn"
                                            data-target-user-id="<?= (int) $profileUserId ?>">
                                            <?= $isCurrentlyFollowing ? 'Following' : 'Follow' ?>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($postCount > 0 && !empty($recentPosts)): ?>
                                        <a href="#recent-posts" class="btn btn-light rounded-pill px-4 border">
                                            View Posts
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($recentPosts)): ?>
                                        <a href="postOverview?id=<?= (int) $recentPosts[0]['id'] ?>" class="btn btn-light rounded-pill px-4 border">
                                            Latest Story
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="author-stat-card">
                            <div class="text-muted small text-uppercase mb-2">Posts</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-file-document-outline fs-3 text-primary"></i>
                                <div class="fs-3 fw-bold"><?= $postCount ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="author-stat-card">
                            <div class="text-muted small text-uppercase mb-2">Followers</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-account-multiple-plus-outline fs-3 text-success"></i>
                                <div class="fs-3 fw-bold" id="follower-count"><?= $followerCount ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="author-stat-card">
                            <div class="text-muted small text-uppercase mb-2">Following</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-account-arrow-right-outline fs-3 text-warning"></i>
                                <div class="fs-3 fw-bold" id="profile-following-count"><?= $followingCount ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="author-stat-card">
                            <div class="text-muted small text-uppercase mb-2">Likes Received</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-thumb-up-outline fs-3 text-info"></i>
                                <div class="fs-3 fw-bold"><?= $likesReceived ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-4">
                        <div class="card author-meta-card shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="card-title mb-0">About This Author</h5>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="vstack gap-3">
                                    <?php foreach ($profileMeta as $item): ?>
                                        <div class="d-flex justify-content-between gap-3 border rounded-3 px-3 py-3 bg-light bg-opacity-50">
                                            <span class="text-muted"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="fw-semibold text-end"><?= $item['value'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8">
                        <div class="card author-meta-card shadow-sm" id="recent-posts">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Posts</h5>
                                <span class="badge bg-light text-dark border"><?= $postCount ?> total</span>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <?php if (empty($recentPosts)): ?>
                                    <div class="text-center py-5 bg-light rounded-4 border">
                                        <i class="mdi mdi-file-document-outline fs-1 text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-0">This author has not published any posts yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="row g-4">
                                        <?php foreach ($recentPosts as $recentPost): ?>
                                            <div class="col-md-6">
                                                <div class="modern-post-card h-100">
                                                    <div class="modern-post-card__body d-flex flex-column h-100">
                                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                                            <span class="modern-post-chip modern-post-chip--category">
                                                                <i class="bx bx-purchase-tag-alt"></i>
                                                                <?= htmlspecialchars($recentPost['category_title'] ?: 'General', ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                            <span class="modern-post-chip">
                                                                <i class="mdi mdi-clock-outline"></i>
                                                                <?= htmlspecialchars(getRelativeTime($recentPost['created_at']), ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                        </div>

                                                        <h6 class="modern-post-card__title fs-5">
                                                            <a href="postOverview?id=<?= (int) $recentPost['id'] ?>">
                                                                <?= htmlspecialchars($recentPost['title'], ENT_QUOTES, 'UTF-8') ?>
                                                            </a>
                                                        </h6>

                                                        <?php if (!empty($recentPost['thumbnail'])): ?>
                                                            <a href="postOverview?id=<?= (int) $recentPost['id'] ?>" class="modern-post-card__media">
                                                                <img
                                                                    src="account/uploads/<?= htmlspecialchars($recentPost['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                                                                    class="modern-post-card__image"
                                                                    alt="<?= htmlspecialchars($recentPost['title'], ENT_QUOTES, 'UTF-8') ?>">
                                                                <span class="modern-post-card__media-badge">
                                                                    <i class="mdi mdi-arrow-top-right"></i>
                                                                    Open story
                                                                </span>
                                                            </a>
                                                        <?php endif; ?>

                                                        <p class="modern-post-card__excerpt">
                                                            <?= htmlspecialchars($postExcerpt($recentPost['body']), ENT_QUOTES, 'UTF-8') ?>
                                                        </p>

                                                        <div class="modern-post-card__footer mt-auto">
                                                            <div class="modern-post-card__actions">
                                                                <span class="modern-post-action">
                                                                    <i class="mdi mdi-comment-outline"></i>
                                                                    <?= (int) $recentPost['comment_count'] ?>
                                                                </span>
                                                                <span class="modern-post-action">
                                                                    <i class="mdi mdi-thumb-up-outline"></i>
                                                                    <?= (int) $recentPost['like_count'] ?>
                                                                </span>
                                                            </div>

                                                            <a href="postOverview?id=<?= (int) $recentPost['id'] ?>" class="modern-post-readmore">
                                                                Read article
                                                                <i class="mdi mdi-arrow-right"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'includes/footer.php' ?>
    </div>
</div>

<script src="account/assets/js/sweetalert.js"></script>
<script src="account/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="account/assets/libs/jquery/jquery.min.js"></script>
<script src="account/assets/libs/metismenu/metisMenu.min.js"></script>
<script src="account/assets/libs/simplebar/simplebar.min.js"></script>
<script src="account/assets/libs/eva-icons/eva.min.js"></script>
<script src="account/assets/js/scroll-top.init.js"></script>
<script src="account/assets/libs/select2/js/select2.min.js"></script>
<script src="account/assets/js/app.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const followButton = document.getElementById('follow-toggle-btn');

        if (!followButton) {
            return;
        }

        const followerCountElement = document.getElementById('follower-count');
        followButton.addEventListener('click', function () {
            const targetUserId = followButton.getAttribute('data-target-user-id');

            followButton.disabled = true;

            fetch(<?= json_encode($followActionUrl) ?>, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    target_user_id: targetUserId
                }).toString()
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text || 'Could not reach the follow service.');
                        });
                    }

                    return response.json();
                })
                .then(data => {
                    if (data.status !== 'success') {
                        throw new Error(data.message || 'Could not update follow status.');
                    }

                    const isFollowing = data.action === 'followed';
                    followButton.textContent = isFollowing ? 'Following' : 'Follow';
                    followButton.classList.toggle('btn-primary', !isFollowing);
                    followButton.classList.toggle('btn-success', isFollowing);
                    followButton.classList.toggle('is-following', isFollowing);

                    if (followerCountElement) {
                        followerCountElement.textContent = data.follower_count ?? followerCountElement.textContent;
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Follow failed',
                        text: error.message || 'Something went wrong while updating the follow status.',
                        confirmButtonColor: '#d33'
                    });
                })
                .finally(() => {
                    followButton.disabled = false;
                });
        });
    });
</script>

</body>
</html>
