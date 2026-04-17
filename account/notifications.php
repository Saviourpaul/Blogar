<?php $pageTitle = 'Notifications';
require 'includes/header.php';

$filter = $_GET['filter'] ?? 'all';
$allNotifications = getUserNotifications($connection, $loggedInId, 50);

if ($filter === 'unread') {
    $allNotifications = array_values(array_filter($allNotifications, function ($notification) {
        return empty($notification['is_read']);
    }));
} elseif ($filter === 'read') {
    $allNotifications = array_values(array_filter($allNotifications, function ($notification) {
        return !empty($notification['is_read']);
    }));
}

if (isset($_GET['mark']) && $_GET['mark'] === 'all') {
    markNotificationsRead($connection, $loggedInId);
    header('Location: notifications');
    exit;
}
?>

<body>
    <div id="layout-wrapper">
        <?= include 'includes/sidebar.php' ?>
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <div class="sidebar-left horizontal-sidebar"></div>
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Notifications</h4>
                                <div class="d-flex gap-2">
                                    <a href="notifications?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-secondary' : 'btn-light'; ?>">All</a>
                                    <a href="notifications?filter=unread" class="btn btn-sm <?= $filter === 'unread' ? 'btn-secondary' : 'btn-light'; ?>">Unread</a>
                                    <a href="notifications?filter=read" class="btn btn-sm <?= $filter === 'read' ? 'btn-secondary' : 'btn-light'; ?>">Read</a>
                                    <a href="notifications?mark=all" class="btn btn-sm btn-outline-primary">Mark all read</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($allNotifications)): ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($allNotifications as $notification): ?>
                                        <?php $notificationMeta = getNotificationMeta($notification['type'] ?? ''); ?>
                                        <a href="<?= htmlspecialchars($notification['link'] ?: '#'); ?>" class="text-reset border rounded-3 p-3 d-block <?= empty($notification['is_read']) ? 'bg-light' : ''; ?>">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="avatar avatar-sm <?= htmlspecialchars($notificationMeta['avatar']); ?>">
                                                    <span class="rounded fs-18">
                                                        <i class="mdi <?= htmlspecialchars($notificationMeta['icon']); ?>"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                                        <h5 class="mb-0 fs-15"><?= htmlspecialchars($notification['title']); ?></h5>
                                                        <?php if (empty($notification['is_read'])): ?>
                                                            <span class="badge bg-danger-subtle text-danger">Unread</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="text-muted mb-2"><?= htmlspecialchars($notification['message']); ?></p>
                                                    <p class="mb-0 text-muted fs-12">
                                                        <i class="mdi mdi-clock-outline"></i>
                                                        <?= getRelativeTime(is_numeric($notification['created_value']) ? date('Y-m-d H:i:s', (int) $notification['created_value']) : $notification['created_value']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="mdi mdi-bell-outline fs-1 d-block mb-3"></i>
                                    No notifications found for this view.
                                </div>
                            <?php endif; ?>
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
    <script src="account/assets/js/app.js"></script>
</body>
</html>
