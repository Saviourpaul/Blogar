<?php
$pageTitle = 'Dashboard';
require 'includes/header.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user-id'])) {
    header("Location: signin");
    exit();
}

$currentUserId = (int) ($_SESSION['user-id'] ?? 0);
$isAdmin = !empty($_SESSION['is_admin']);
$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
if ($displayName === '') {
    $displayName = $user['username'] ?? 'there';
}

function dashboardFetchValue($conn, $sql, $types = '', $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    if ($types !== '' && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_row() : [0];
    $stmt->close();
    return (int) ($row[0] ?? 0);
}

function dashboardFetchRows($conn, $sql, $types = '', $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '' && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function dashboardMonthlySeries($conn, $table, $dateColumn, $whereSql = '', $types = '', $params = [], $monthsBack = 6)
{
    $labels = [];
    $map = [];
    for ($i = $monthsBack - 1; $i >= 0; $i--) {
        $key = date('Y-m', strtotime("-$i months"));
        $labels[] = date('M', strtotime($key . '-01'));
        $map[$key] = 0;
    }

    $sql = "SELECT DATE_FORMAT($dateColumn, '%Y-%m') AS m, COUNT(*) AS total
            FROM $table
            WHERE $dateColumn >= DATE_SUB(CURDATE(), INTERVAL " . (int) ($monthsBack - 1) . " MONTH)";
    if ($whereSql !== '') {
        $sql .= " AND $whereSql";
    }
    $sql .= " GROUP BY m ORDER BY m ASC";

    foreach (dashboardFetchRows($conn, $sql, $types, $params) as $row) {
        if (isset($map[$row['m']])) {
            $map[$row['m']] = (int) $row['total'];
        }
    }

    return ['labels' => $labels, 'values' => array_values($map)];
}

$stats = [];
$trendLabels = [];
$trendSeries = [];
$engagementSeries = [];
$engagementLabels = ['Likes', 'Dislikes', 'Shares'];
$quickLinks = [];
$activityRows = [];
$activityTitle = '';
$tableRows = [];
$tableTitle = '';
$recentNotifications = getUserNotifications($connection, $currentUserId, 5);
$unreadNotifications = getUnreadNotificationCount($connection, $currentUserId);

if ($isAdmin) {
    $stats = [
        ['label' => 'Users', 'value' => dashboardFetchValue($connection, "SELECT COUNT(*) FROM users"), 'icon' => 'mdi-account-group-outline', 'tone' => 'primary'],
        ['label' => 'Posts', 'value' => dashboardFetchValue($connection, "SELECT COUNT(*) FROM posts"), 'icon' => 'mdi-file-document-outline', 'tone' => 'success'],
        ['label' => 'Comments', 'value' => dashboardFetchValue($connection, "SELECT COUNT(*) FROM comments"), 'icon' => 'mdi-comment-processing-outline', 'tone' => 'warning'],
        ['label' => 'Followers', 'value' => dashboardFetchValue($connection, "SELECT COUNT(*) FROM followers"), 'icon' => 'mdi-account-heart-outline', 'tone' => 'info']
    ];

    $postTrend = dashboardMonthlySeries($connection, 'posts', 'created_at');
    $userTrend = dashboardMonthlySeries($connection, 'users', 'created_at');
    $commentTrend = dashboardMonthlySeries($connection, 'comments', 'created_at');
    $trendLabels = $postTrend['labels'];
    $trendSeries = [
        ['name' => 'Posts', 'data' => $postTrend['values']],
        ['name' => 'Users', 'data' => $userTrend['values']],
        ['name' => 'Comments', 'data' => $commentTrend['values']]
    ];

    $engagementSeries = [
        dashboardFetchValue($connection, "SELECT COUNT(*) FROM post_interactions WHERE interaction_type='like'"),
        dashboardFetchValue($connection, "SELECT COUNT(*) FROM post_interactions WHERE interaction_type='dislike'"),
        dashboardFetchValue($connection, "SELECT COUNT(*) FROM post_interactions WHERE interaction_type='share'")
    ];

    $activityTitle = 'Recent Users';
    $activityRows = dashboardFetchRows($connection, "SELECT id, firstname, lastname, username, avatar, status, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $tableTitle = 'Recent Posts';
    $tableRows = dashboardFetchRows($connection, "SELECT p.id, p.title, p.created_at, u.firstname, u.lastname, c.title AS category_title FROM posts p LEFT JOIN users u ON u.id = p.author_id LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC LIMIT 6");
    $quickLinks = [
        ['label' => 'Manage Users', 'href' => 'ManageUser', 'icon' => 'mdi-account-cog-outline'],
        ['label' => 'Manage Posts', 'href' => 'managePost', 'icon' => 'mdi-post-outline'],
        ['label' => 'Categories', 'href' => 'manageCategory', 'icon' => 'mdi-shape-outline'],
        ['label' => 'Settings', 'href' => 'setting', 'icon' => 'mdi-cog-outline']
    ];
} else {
    $stats = [
        ['label' => 'My Posts', 'value' => dashboardFetchValue($connection, "SELECT COUNT(*) FROM posts WHERE author_id = ?", 'i', [$currentUserId]), 'icon' => 'mdi-file-document-outline', 'tone' => 'primary'],
        ['label' => 'My Comments', 'value' => dashboardFetchValue($connection, "SELECT COUNT(*) FROM comments WHERE user_id = ?", 'i', [$currentUserId]), 'icon' => 'mdi-comment-processing-outline', 'tone' => 'success'],
        ['label' => 'Followers', 'value' => getFollowerCount($connection, $currentUserId), 'icon' => 'mdi-account-heart-outline', 'tone' => 'warning'],
        ['label' => 'Following', 'value' => getFollowingCount($connection, $currentUserId), 'icon' => 'mdi-account-arrow-right-outline', 'tone' => 'info']
    ];

    $postTrend = dashboardMonthlySeries($connection, 'posts', 'created_at', 'author_id = ?', 'i', [$currentUserId]);
    $commentTrend = dashboardMonthlySeries($connection, 'comments', 'created_at', 'user_id = ?', 'i', [$currentUserId]);
    $trendLabels = $postTrend['labels'];
    $trendSeries = [
        ['name' => 'My Posts', 'data' => $postTrend['values']],
        ['name' => 'My Comments', 'data' => $commentTrend['values']]
    ];

    $engagementSeries = [
        dashboardFetchValue($connection, "SELECT COUNT(*) FROM post_interactions pi INNER JOIN posts p ON p.id = pi.post_id WHERE p.author_id = ? AND pi.interaction_type='like'", 'i', [$currentUserId]),
        dashboardFetchValue($connection, "SELECT COUNT(*) FROM post_interactions pi INNER JOIN posts p ON p.id = pi.post_id WHERE p.author_id = ? AND pi.interaction_type='dislike'", 'i', [$currentUserId]),
        dashboardFetchValue($connection, "SELECT COUNT(*) FROM post_interactions pi INNER JOIN posts p ON p.id = pi.post_id WHERE p.author_id = ? AND pi.interaction_type='share'", 'i', [$currentUserId])
    ];

    $activityTitle = 'Recent Notifications';
    $activityRows = $recentNotifications;
    $tableTitle = 'My Latest Posts';
    $tableRows = dashboardFetchRows($connection, "SELECT p.id, p.title, p.created_at, c.title AS category_title, (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comment_count, (SELECT COUNT(*) FROM post_interactions pi WHERE pi.post_id = p.id AND pi.interaction_type = 'like') AS like_count FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.author_id = ? ORDER BY p.created_at DESC LIMIT 6", 'i', [$currentUserId]);
    $quickLinks = [
        ['label' => 'Create Post', 'href' => 'CreatePost', 'icon' => 'mdi-plus-box-outline'],
        ['label' => 'Manage My Posts', 'href' => 'managePost', 'icon' => 'mdi-folder-edit-outline'],
        ['label' => 'My Profile', 'href' => 'UserProfile?id=' . $currentUserId, 'icon' => 'mdi-account-outline'],
        ['label' => 'Notifications', 'href' => 'notifications', 'icon' => 'mdi-bell-outline']
    ];
}
?>

<style>
    .dashboard-hero{background:radial-gradient(circle at top left,rgba(13,110,253,.2),transparent 28%),radial-gradient(circle at bottom right,rgba(25,135,84,.18),transparent 24%),linear-gradient(135deg,#fff 0%,#f6f9fc 100%);border:1px solid rgba(15,23,42,.08);overflow:hidden}
    .dashboard-stat-card,.dashboard-panel{border:1px solid rgba(15,23,42,.08);border-radius:1rem;box-shadow:0 16px 40px rgba(15,23,42,.05)}
    .dashboard-stat-card{transition:transform .2s ease,box-shadow .2s ease}
    .dashboard-stat-card:hover{transform:translateY(-2px);box-shadow:0 20px 46px rgba(15,23,42,.08)}
    .dashboard-icon{width:52px;height:52px;border-radius:16px;display:inline-flex;align-items:center;justify-content:center;font-size:1.4rem}
    .dashboard-quick-link{border:1px solid rgba(15,23,42,.08);border-radius:1rem;padding:1rem;text-decoration:none;color:inherit;background:#fff;transition:all .2s ease}
    .dashboard-quick-link:hover{border-color:rgba(13,110,253,.28);transform:translateY(-2px)}
</style>

<body>
<div id="layout-wrapper">
    <?= include 'includes/sidebar.php' ?>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row"><div class="col-12"><div class="card dashboard-hero shadow-sm mb-4"><div class="card-body p-4 p-lg-5"><div class="row align-items-center g-4"><div class="col-lg-8"><span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2 mb-3"><?= $isAdmin ? 'Admin Analytics' : 'Personal Workspace' ?></span><h2 class="mb-2">Welcome back, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></h2><p class="text-muted mb-0"><?= $isAdmin ? 'Track platform growth, publishing activity, and community engagement from one place.' : 'Monitor your publishing momentum, followers, and post engagement in one clean view.' ?></p></div><div class="col-lg-4"><div class="row g-3"><div class="col-6"><div class="rounded-4 bg-white border p-3 h-100"><div class="text-muted small mb-1">Unread Notifications</div><div class="fs-3 fw-bold"><?= $unreadNotifications ?></div></div></div><div class="col-6"><div class="rounded-4 bg-white border p-3 h-100"><div class="text-muted small mb-1"><?= $isAdmin ? 'Platform Followers' : 'Total Following' ?></div><div class="fs-3 fw-bold"><?= $isAdmin ? dashboardFetchValue($connection, "SELECT COUNT(*) FROM followers") : getFollowingCount($connection, $currentUserId) ?></div></div></div></div></div></div></div></div></div></div>

                <div class="row g-3 mb-4">
                    <?php foreach ($stats as $stat): ?>
                        <div class="col-sm-6 col-xl-3"><div class="card dashboard-stat-card mb-0"><div class="card-body"><div class="d-flex align-items-center justify-content-between mb-3"><div><div class="text-muted small text-uppercase mb-1"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></div><div class="fs-2 fw-bold"><?= (int) $stat['value'] ?></div></div><div class="dashboard-icon bg-<?= htmlspecialchars($stat['tone'], ENT_QUOTES, 'UTF-8') ?>-subtle text-<?= htmlspecialchars($stat['tone'], ENT_QUOTES, 'UTF-8') ?>"><i class="mdi <?= htmlspecialchars($stat['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></div></div></div></div></div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-8">
                        <div class="card dashboard-panel mb-0">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="card-title mb-1"><?= $isAdmin ? 'Growth Overview' : 'Publishing Momentum' ?></h5>
                                <p class="text-muted mb-0 small">Last 6 months activity</p>
                            </div>
                            <div class="card-body px-4 pb-4"><div id="dashboardTrendChart" style="min-height:330px;"></div></div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card dashboard-panel mb-0">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="card-title mb-1"><?= $isAdmin ? 'Platform Engagement' : 'My Engagement Mix' ?></h5>
                                <p class="text-muted mb-0 small">Likes, dislikes, and shares</p>
                            </div>
                            <div class="card-body px-4 pb-4"><div id="dashboardEngagementChart" style="min-height:330px;"></div></div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-4">
                        <div class="card dashboard-panel h-100 mb-0">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="card-title mb-1">Quick Actions</h5>
                                <p class="text-muted mb-0 small">Jump into common tasks</p>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="row g-3">
                                    <?php foreach ($quickLinks as $link): ?>
                                        <div class="col-12">
                                            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>" class="dashboard-quick-link d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="dashboard-icon bg-light text-primary"><i class="mdi <?= htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                                    <span class="fw-semibold"><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                                <i class="mdi mdi-chevron-right text-muted"></i>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8">
                        <div class="card dashboard-panel h-100 mb-0">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($activityTitle, ENT_QUOTES, 'UTF-8') ?></h5>
                                <p class="text-muted mb-0 small"><?= $isAdmin ? 'Newest members joining the community' : 'Your latest activity alerts' ?></p>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <?php if (empty($activityRows)): ?>
                                    <div class="text-center py-5 text-muted"><i class="mdi mdi-database-off-outline fs-1 d-block mb-2"></i>Nothing to show yet.</div>
                                <?php elseif ($isAdmin): ?>
                                    <div class="d-grid gap-3">
                                        <?php foreach ($activityRows as $member): ?>
                                            <a href="UserProfile?id=<?= (int) $member['id'] ?>" class="text-reset text-decoration-none border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="avatar avatar-md avatar-circle overflow-hidden">
                                                        <?php if (!empty($member['avatar'])): ?>
                                                            <img src="account/uploads/<?= htmlspecialchars($member['avatar'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid" alt="<?= htmlspecialchars(trim(($member['firstname'] ?? '') . ' ' . ($member['lastname'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?php else: ?>
                                                            <span class="avatar-title bg-soft-primary text-primary fw-semibold"><?= htmlspecialchars(strtoupper(substr(trim(($member['firstname'] ?? '') . ' ' . ($member['lastname'] ?? $member['username'] ?? 'A')), 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <div><div class="fw-semibold"><?= htmlspecialchars(trim(($member['firstname'] ?? '') . ' ' . ($member['lastname'] ?? '')) ?: ($member['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?></div><div class="text-muted small">@<?= htmlspecialchars($member['username'] ?? 'member', ENT_QUOTES, 'UTF-8') ?></div></div>
                                                </div>
                                                <div class="text-end"><div class="badge bg-light text-dark border mb-1"><?= htmlspecialchars(ucfirst((string) ($member['status'] ?? 'active')), ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted"><?= htmlspecialchars(getRelativeTime($member['created_at']), ENT_QUOTES, 'UTF-8') ?></div></div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="d-grid gap-3">
                                        <?php foreach ($activityRows as $notification): ?>
                                            <?php $notificationMeta = getNotificationMeta($notification['type'] ?? ''); ?>
                                            <a href="<?= htmlspecialchars($notification['link'] ?: '#', ENT_QUOTES, 'UTF-8') ?>" class="text-reset text-decoration-none border rounded-4 p-3 d-flex align-items-start gap-3">
                                                <div class="avatar avatar-sm <?= htmlspecialchars($notificationMeta['avatar'], ENT_QUOTES, 'UTF-8') ?>"><span class="rounded fs-18"><i class="mdi <?= htmlspecialchars($notificationMeta['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span></div>
                                                <div class="flex-grow-1"><div class="fw-semibold"><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></div><div class="text-muted small mb-1"><?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted"><?= htmlspecialchars(getRelativeTime(is_numeric($notification['created_value']) ? date('Y-m-d H:i:s', (int) $notification['created_value']) : $notification['created_value']), ENT_QUOTES, 'UTF-8') ?></div></div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card dashboard-panel mb-0">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($tableTitle, ENT_QUOTES, 'UTF-8') ?></h5>
                                <p class="text-muted mb-0 small"><?= $isAdmin ? 'Most recent publishing activity across the platform' : 'Your latest published ideas' ?></p>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <?php if (empty($tableRows)): ?>
                                    <div class="text-center py-5 text-muted"><i class="mdi mdi-notebook-outline fs-1 d-block mb-2"></i>Nothing to display yet.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Title</th>
                                                    <th><?= $isAdmin ? 'Author' : 'Category' ?></th>
                                                    <th>Created</th>
                                                    <th><?= $isAdmin ? 'Category' : 'Engagement' ?></th>
                                                    <th class="text-end">Open</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tableRows as $row): ?>
                                                    <tr>
                                                        <td><a href="postOverview?id=<?= (int) $row['id'] ?>" class="text-body fw-semibold"><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></a></td>
                                                        <td><?= $isAdmin ? htmlspecialchars(trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')) ?: 'Unknown', ENT_QUOTES, 'UTF-8') : htmlspecialchars($row['category_title'] ?: 'General', ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars(getRelativeTime($row['created_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td>
                                                            <?php if ($isAdmin): ?>
                                                                <?= htmlspecialchars($row['category_title'] ?: 'General', ENT_QUOTES, 'UTF-8') ?>
                                                            <?php else: ?>
                                                                <span class="text-muted small"><i class="mdi mdi-comment-outline me-1"></i><?= (int) ($row['comment_count'] ?? 0) ?> <i class="mdi mdi-thumb-up-outline ms-2 me-1"></i><?= (int) ($row['like_count'] ?? 0) ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-end"><a href="postOverview?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-light border">View</a></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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

<script src="account/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="account/assets/libs/jquery/jquery.min.js"></script>
<script src="account/assets/libs/metismenu/metisMenu.min.js"></script>
<script src="account/assets/libs/simplebar/simplebar.min.js"></script>
<script src="account/assets/libs/eva-icons/eva.min.js"></script>
<script src="account/assets/js/scroll-top.init.js"></script>
<script src="account/assets/libs/select2/js/select2.min.js"></script>
<script src="account/assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="account/assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trendEl = document.querySelector('#dashboardTrendChart');
    const engagementEl = document.querySelector('#dashboardEngagementChart');

    if (trendEl && typeof ApexCharts !== 'undefined') {
        new ApexCharts(trendEl, {
            chart: { type: 'area', height: 330, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.04 } },
            dataLabels: { enabled: false },
            series: <?= json_encode($trendSeries) ?>,
            xaxis: { categories: <?= json_encode($trendLabels) ?>, axisBorder: { show: false }, axisTicks: { show: false } },
            grid: { borderColor: 'rgba(148,163,184,0.18)', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'left' },
            colors: ['#0d6efd', '#20c997', '#f59f00']
        }).render();
    }

    if (engagementEl && typeof ApexCharts !== 'undefined') {
        new ApexCharts(engagementEl, {
            chart: { type: 'donut', height: 330 },
            series: <?= json_encode($engagementSeries) ?>,
            labels: <?= json_encode($engagementLabels) ?>,
            legend: { position: 'bottom' },
            dataLabels: { enabled: true },
            colors: ['#198754', '#dc3545', '#0dcaf0'],
            stroke: { width: 0 },
            plotOptions: { pie: { donut: { size: '70%' } } }
        }).render();
    }
});
</script>

</body>
</html>
