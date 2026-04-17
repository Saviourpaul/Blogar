<?php $pageTitle = 'setting';
require 'includes/header.php';
require_once 'includes/helpers.php';


$success_message = "";
$error_message = "";
$_advanced_notice = "";

if (isset($_POST['save_system_settings'])) {
    $title = mysqli_real_escape_string($connection, $_POST['site_title']);
    $url = mysqli_real_escape_string($connection, $_POST['site_url']);
    $keywords = mysqli_real_escape_string($connection, $_POST['meta_keywords']);
    $description = mysqli_real_escape_string($connection, $_POST['meta_description']);

    $update_query = "UPDATE settings SET 
                     site_title = '$title', 
                     site_url = '$url', 
                     meta_keywords = '$keywords', 
                     meta_description = '$description' 
                     WHERE id = 1";

    if (mysqli_query($connection, $update_query)) {
        $success_message = "Site settings updated successfully!";
    } else {
        $error_message = "Error updating settings: " . mysqli_error($connection);
    }
}

if (isset($_POST['save_advanced_settings'])) {
    $advancedColumns = [
        'enable_post',
        'enable_comment',
        'comment_edit_window',
        'enable_delete_post',
        'default_language'
    ];
    $availableColumns = [];
    $missingColumns = [];

    foreach ($advancedColumns as $column) {
        if (dbColumnExists($connection, 'settings', $column)) {
            $availableColumns[] = $column;
        } else {
            $missingColumns[] = $column;
        }
    }

    if (!empty($availableColumns)) {
        $enablePost = isset($_POST['enable_post']) ? 1 : 0;
        $enableComment = isset($_POST['enable_comment']) ? 1 : 0;
        $commentEditWindow = max(1, (int) ($_POST['comment_edit_window'] ?? 15));
        $enableDeletePost = isset($_POST['enable_delete_post']) ? 1 : 0;
        $defaultLanguage = mysqli_real_escape_string($connection, trim($_POST['default_language'] ?? 'en'));

        $advancedValues = [
            'enable_post' => $enablePost,
            'enable_comment' => $enableComment,
            'comment_edit_window' => $commentEditWindow,
            'enable_delete_post' => $enableDeletePost,
            'default_language' => "'" . $defaultLanguage . "'"
        ];

        $updateParts = [];
        foreach ($availableColumns as $column) {
            $updateParts[] = $column . " = " . $advancedValues[$column];
        }

        $advancedQuery = "UPDATE settings SET " . implode(', ', $updateParts) . " WHERE id = 1";

        if (mysqli_query($connection, $advancedQuery)) {
            $success_message = "Advanced settings updated successfully!";
        } else {
            $error_message = "Error updating advanced settings: " . mysqli_error($connection);
        }
    } else {
        $error_message = "Advanced settings could not be saved because the required columns do not exist in the settings table yet.";
    }

    if (!empty($missingColumns)) {
        $_advanced_notice = "Missing settings columns: " . implode(', ', $missingColumns) . ".";
    }
}

if (isset($_POST['save_email_notification_settings'])) {
    $emailColumns = [
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_email',
        'smtp_from_name',
        'admin_notification_email',
        'notify_welcome_email',
        'notify_password_reset_email',
        'notify_new_post_email',
        'notify_reply_email',
        'notify_comment_email',
        'notify_admin_new_user',
        'notify_admin_new_post',
        'tpl_welcome_subject',
        'tpl_welcome_body',
        'tpl_password_reset_subject',
        'tpl_password_reset_body',
        'tpl_new_post_subject',
        'tpl_new_post_body',
        'tpl_reply_subject',
        'tpl_reply_body',
        'tpl_comment_subject',
        'tpl_comment_body',
        'tpl_admin_new_user_subject',
        'tpl_admin_new_user_body',
        'tpl_admin_new_post_subject',
        'tpl_admin_new_post_body'
    ];

    $availableColumns = [];
    $missingColumns = [];

    foreach ($emailColumns as $column) {
        if (dbColumnExists($connection, 'settings', $column)) {
            $availableColumns[] = $column;
        } else {
            $missingColumns[] = $column;
        }
    }

    if (!empty($availableColumns)) {
        $templateDefaults = getEmailTemplateDefaults();
        $emailValues = [
            'smtp_host' => "'" . mysqli_real_escape_string($connection, trim($_POST['smtp_host'] ?? '')) . "'",
            'smtp_port' => (int) ($_POST['smtp_port'] ?? 587),
            'smtp_username' => "'" . mysqli_real_escape_string($connection, trim($_POST['smtp_username'] ?? '')) . "'",
            'smtp_password' => "'" . mysqli_real_escape_string($connection, trim($_POST['smtp_password'] ?? '')) . "'",
            'smtp_encryption' => "'" . mysqli_real_escape_string($connection, trim($_POST['smtp_encryption'] ?? 'tls')) . "'",
            'smtp_from_email' => "'" . mysqli_real_escape_string($connection, trim($_POST['smtp_from_email'] ?? '')) . "'",
            'smtp_from_name' => "'" . mysqli_real_escape_string($connection, trim($_POST['smtp_from_name'] ?? '')) . "'",
            'admin_notification_email' => "'" . mysqli_real_escape_string($connection, trim($_POST['admin_notification_email'] ?? '')) . "'",
            'notify_welcome_email' => isset($_POST['notify_welcome_email']) ? 1 : 0,
            'notify_password_reset_email' => isset($_POST['notify_password_reset_email']) ? 1 : 0,
            'notify_new_post_email' => isset($_POST['notify_new_post_email']) ? 1 : 0,
            'notify_reply_email' => isset($_POST['notify_reply_email']) ? 1 : 0,
            'notify_comment_email' => isset($_POST['notify_comment_email']) ? 1 : 0,
            'notify_admin_new_user' => isset($_POST['notify_admin_new_user']) ? 1 : 0,
            'notify_admin_new_post' => isset($_POST['notify_admin_new_post']) ? 1 : 0
        ];

        foreach ($templateDefaults as $column => $defaultValue) {
            $emailValues[$column] = "'" . mysqli_real_escape_string(
                $connection,
                trim($_POST[$column] ?? $defaultValue)
            ) . "'";
        }

        $updateParts = [];
        foreach ($availableColumns as $column) {
            $updateParts[] = $column . " = " . $emailValues[$column];
        }

        $emailQuery = "UPDATE settings SET " . implode(', ', $updateParts) . " WHERE id = 1";

        if (mysqli_query($connection, $emailQuery)) {
            $success_message = "Email and notification settings updated successfully!";
        } else {
            $error_message = "Error updating email settings: " . mysqli_error($connection);
        }
    } else {
        $error_message = "Email settings could not be saved because the required columns do not exist in the settings table yet.";
    }

    if (!empty($missingColumns)) {
        $_advanced_notice = "Missing settings columns: " . implode(', ', $missingColumns) . ".";
    }
}

$query = "SELECT * FROM settings WHERE id = 1";
$result = mysqli_query($connection, $query);

$current_title = "";
$current_url = "";
$current_keywords = "";
$current_description = "";
$current_enable_post = 1;
$current_enable_comment = 1;
$current_comment_edit_window = 15;
$current_enable_delete_post = 0;
$current_default_language = 'en';
$emailDefaults = [
    'smtp_host' => '',
    'smtp_port' => 587,
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
    'smtp_from_email' => '',
    'smtp_from_name' => '',
    'admin_notification_email' => '',
    'notify_welcome_email' => 0,
    'notify_password_reset_email' => 0,
    'notify_new_post_email' => 0,
    'notify_reply_email' => 0,
    'notify_comment_email' => 0,
    'notify_admin_new_user' => 0,
    'notify_admin_new_post' => 0
];
$templateDefaults = getEmailTemplateDefaults();
$emailSettings = array_merge($emailDefaults, $templateDefaults);

if ($result && mysqli_num_rows($result) > 0) {
    $settings = mysqli_fetch_assoc($result);
    $current_title = $settings['site_title'];
    $current_url = $settings['site_url'];
    $current_keywords = $settings['meta_keywords'];
    $current_description = $settings['meta_description'];
    $current_enable_post = isset($settings['enable_post']) ? (int) $settings['enable_post'] : $current_enable_post;
    $current_enable_comment = isset($settings['enable_comment']) ? (int) $settings['enable_comment'] : $current_enable_comment;
    $current_comment_edit_window = isset($settings['comment_edit_window']) ? max(1, (int) $settings['comment_edit_window']) : $current_comment_edit_window;
    $current_enable_delete_post = isset($settings['enable_delete_post']) ? (int) $settings['enable_delete_post'] : $current_enable_delete_post;
    $current_default_language = !empty($settings['default_language']) ? $settings['default_language'] : $current_default_language;

    foreach ($emailSettings as $key => $defaultValue) {
        if (array_key_exists($key, $settings) && $settings[$key] !== null && $settings[$key] !== '') {
            $emailSettings[$key] = $settings[$key];
        }
    }
}
?>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- Start topbar -->
        <?= include 'includes/sidebar.php' ?>
        <!-- End topbar -->
        <!-- ========== Left Sidebar Start ========== -->

        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ========== Left Sidebar Start ========== -->
        <div class="sidebar-left horizontal-sidebar">

            <div class="sidebar-slide h-100">

                <!--- Sidebar-menu -->

                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">setting</h4>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">system setting</h3>
                                </div>
                                <div class="card-body">

                                    <?php if (!empty($success_message)): ?>
                                        <div class="alert alert-success"><?= $success_message; ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($error_message)): ?>
                                        <div class="alert alert-danger"><?= $error_message; ?></div>
                                    <?php endif; ?>

                                    <form action="" method="POST">
                                        <div class="d-grid gap-3">

                                            <div class="row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end" for="site-title">Site
                                                        Title</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <input type="text" class="form-control" name="site_title"
                                                        id="site-title" maxlength="60" placeholder="Type here..."
                                                        value="<?= htmlspecialchars($current_title); ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end" for="site-url">Site
                                                        URL</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="mb-2">
                                                        <input type="url" class="form-control" name="site_url"
                                                            id="site-url" maxlength="255"
                                                            placeholder="https://example.com"
                                                            value="<?= htmlspecialchars($current_url); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end" for="meta-keywords">Meta
                                                        Keywords</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="mb-2">
                                                        <input type="text" class="form-control" name="meta_keywords"
                                                            id="meta-keywords" maxlength="150"
                                                            placeholder="blog, news, tech"
                                                            value="<?= htmlspecialchars($current_keywords); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end"
                                                        for="meta-description">Meta Description</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="meta_description"
                                                            id="meta-description" rows="3" maxlength="160"
                                                            placeholder="Type here..."><?= htmlspecialchars($current_description); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8 mt-4">
                                                <button type="submit" name="save_system_settings" class="btn btn-secondary">Save
                                                    Update</button>
                                            </div>

                                        </div>
                                    </form>

                                </div>


                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Email & Notification Settings</h3>
                                </div>
                                <div class="card-body">
                                    <form action="" method="POST">
                                        <div class="d-grid gap-4">
                                            <div>
                                                <h5 class="mb-3">SMTP Configuration</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="smtp-host">SMTP Host</label>
                                                        <input type="text" class="form-control" id="smtp-host" name="smtp_host"
                                                            value="<?= htmlspecialchars($emailSettings['smtp_host']); ?>"
                                                            placeholder="smtp.example.com">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="smtp-port">SMTP Port</label>
                                                        <input type="number" class="form-control" id="smtp-port" name="smtp_port"
                                                            value="<?= htmlspecialchars((string) $emailSettings['smtp_port']); ?>"
                                                            placeholder="587">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="smtp-encryption">Encryption</label>
                                                        <select class="form-select" id="smtp-encryption" name="smtp_encryption">
                                                            <option value="tls" <?= $emailSettings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                                            <option value="ssl" <?= $emailSettings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                            <option value="" <?= $emailSettings['smtp_encryption'] === '' ? 'selected' : ''; ?>>None</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="smtp-username">SMTP Username</label>
                                                        <input type="text" class="form-control" id="smtp-username" name="smtp_username"
                                                            value="<?= htmlspecialchars($emailSettings['smtp_username']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="smtp-password">SMTP Password</label>
                                                        <input type="password" class="form-control" id="smtp-password" name="smtp_password"
                                                            value="<?= htmlspecialchars($emailSettings['smtp_password']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="smtp-from-email">From Email</label>
                                                        <input type="email" class="form-control" id="smtp-from-email" name="smtp_from_email"
                                                            value="<?= htmlspecialchars($emailSettings['smtp_from_email']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="smtp-from-name">From Name</label>
                                                        <input type="text" class="form-control" id="smtp-from-name" name="smtp_from_name"
                                                            value="<?= htmlspecialchars($emailSettings['smtp_from_name']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="admin-notification-email">Admin Notification Email</label>
                                                        <input type="email" class="form-control" id="admin-notification-email"
                                                            name="admin_notification_email"
                                                            value="<?= htmlspecialchars($emailSettings['admin_notification_email']); ?>"
                                                            placeholder="admin@example.com">
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <h5 class="mb-3">Notification Events</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="notify-welcome-email"
                                                                name="notify_welcome_email" value="1"
                                                                <?= (int) $emailSettings['notify_welcome_email'] === 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="notify-welcome-email">Welcome Email</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="notify-password-reset-email"
                                                                name="notify_password_reset_email" value="1"
                                                                <?= (int) $emailSettings['notify_password_reset_email'] === 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="notify-password-reset-email">Password Reset Email</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="notify-new-post-email"
                                                                name="notify_new_post_email" value="1"
                                                                <?= (int) $emailSettings['notify_new_post_email'] === 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="notify-new-post-email">New Post Notification</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="notify-reply-email"
                                                                name="notify_reply_email" value="1"
                                                                <?= (int) $emailSettings['notify_reply_email'] === 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="notify-reply-email">Reply Notification</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="notify-comment-email"
                                                                name="notify_comment_email" value="1"
                                                                <?= (int) $emailSettings['notify_comment_email'] === 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="notify-comment-email">Comment Notification</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="notify-admin-new-user"
                                                                name="notify_admin_new_user" value="1"
                                                                <?= (int) $emailSettings['notify_admin_new_user'] === 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="notify-admin-new-user">Admin: New User Signup</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="notify-admin-new-post"
                                                                name="notify_admin_new_post" value="1"
                                                                <?= (int) $emailSettings['notify_admin_new_post'] === 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="notify-admin-new-post">Admin: New Post Submission</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <h5 class="mb-3">Email Templates</h5>
                                                <div class="row g-4">
                                                    <div class="col-12">
                                                        <label class="form-label">Welcome Email Subject</label>
                                                        <input type="text" class="form-control" name="tpl_welcome_subject"
                                                            value="<?= htmlspecialchars($emailSettings['tpl_welcome_subject']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Welcome Email Body</label>
                                                        <textarea class="form-control" rows="4" name="tpl_welcome_body"><?= htmlspecialchars($emailSettings['tpl_welcome_body']); ?></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Password Reset Subject</label>
                                                        <input type="text" class="form-control" name="tpl_password_reset_subject"
                                                            value="<?= htmlspecialchars($emailSettings['tpl_password_reset_subject']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Password Reset Body</label>
                                                        <textarea class="form-control" rows="4" name="tpl_password_reset_body"><?= htmlspecialchars($emailSettings['tpl_password_reset_body']); ?></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">New Post Subject</label>
                                                        <input type="text" class="form-control" name="tpl_new_post_subject"
                                                            value="<?= htmlspecialchars($emailSettings['tpl_new_post_subject']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">New Post Body</label>
                                                        <textarea class="form-control" rows="4" name="tpl_new_post_body"><?= htmlspecialchars($emailSettings['tpl_new_post_body']); ?></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Reply Notification Subject</label>
                                                        <input type="text" class="form-control" name="tpl_reply_subject"
                                                            value="<?= htmlspecialchars($emailSettings['tpl_reply_subject']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Reply Notification Body</label>
                                                        <textarea class="form-control" rows="4" name="tpl_reply_body"><?= htmlspecialchars($emailSettings['tpl_reply_body']); ?></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Comment Notification Subject</label>
                                                        <input type="text" class="form-control" name="tpl_comment_subject"
                                                            value="<?= htmlspecialchars($emailSettings['tpl_comment_subject']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Comment Notification Body</label>
                                                        <textarea class="form-control" rows="4" name="tpl_comment_body"><?= htmlspecialchars($emailSettings['tpl_comment_body']); ?></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Admin New User Subject</label>
                                                        <input type="text" class="form-control" name="tpl_admin_new_user_subject"
                                                            value="<?= htmlspecialchars($emailSettings['tpl_admin_new_user_subject']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Admin New User Body</label>
                                                        <textarea class="form-control" rows="4" name="tpl_admin_new_user_body"><?= htmlspecialchars($emailSettings['tpl_admin_new_user_body']); ?></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Admin New Post Subject</label>
                                                        <input type="text" class="form-control" name="tpl_admin_new_post_subject"
                                                            value="<?= htmlspecialchars($emailSettings['tpl_admin_new_post_subject']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Admin New Post Body</label>
                                                        <textarea class="form-control" rows="4" name="tpl_admin_new_post_body"><?= htmlspecialchars($emailSettings['tpl_admin_new_post_body']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-text mt-3">
                                                    Supported placeholders: `{{site_title}}`, `{{site_url}}`, `{{firstname}}`, `{{fullname}}`, `{{email}}`, `{{username}}`, `{{post_title}}`, `{{post_link}}`, `{{actor_name}}`, `{{reset_link}}`.
                                                </div>
                                            </div>

                                            <div class="col-md-8 mt-2">
                                                <button type="submit" name="save_email_notification_settings"
                                                    class="btn btn-secondary">Save Email Settings</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Advance setting</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($_advanced_notice)): ?>
                                        <div class="alert alert-warning"><?= htmlspecialchars($_advanced_notice); ?></div>
                                    <?php endif; ?>

                                    <form action="" method="POST">
                                        <div class="d-grid gap-3">
                                            <div class="row align-items-center">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end" for="enable-post">Enable Post
                                                    </label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            id="enable-post" name="enable_post" value="1"
                                                            <?= $current_enable_post === 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="enable-post">Allow authors to publish
                                                            new posts</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end" for="enable-comment">Enable
                                                        Comment</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            id="enable-comment" name="enable_comment" value="1"
                                                            <?= $current_enable_comment === 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="enable-comment">Let visitors submit
                                                            comments</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end" for="enable-delete-post">Enable
                                                        Delete Post</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            id="enable-delete-post" name="enable_delete_post" value="1"
                                                            <?= $current_enable_delete_post === 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="enable-delete-post">Allow post deletion
                                                            from the dashboard</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end" for="comment-edit-window">Comment
                                                        Edit Window</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="input-group">
                                                        <input type="number" min="1" max="240" class="form-control"
                                                            id="comment-edit-window" name="comment_edit_window"
                                                            value="<?= (int) $current_comment_edit_window; ?>">
                                                        <span class="input-group-text">minutes</span>
                                                    </div>
                                                    <div class="form-text">Authors can edit their own comments for this long
                                                        after posting.</div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label class="col-form-label text-sm-end"
                                                        for="default-language">Default Language</label>
                                                </div>
                                                <div class="col-sm-5 col-lg-5">
                                                    <div class="mb-2">
                                                        <select class="form-select" id="default-language"
                                                            name="default_language">
                                                            <option value="en" <?= $current_default_language === 'en' ? 'selected' : ''; ?>>
                                                                English</option>
                                                            <option value="fr" <?= $current_default_language === 'fr' ? 'selected' : ''; ?>>
                                                                French</option>
                                                            <option value="es" <?= $current_default_language === 'es' ? 'selected' : ''; ?>>
                                                                Spanish</option>
                                                            <option value="de" <?= $current_default_language === 'de' ? 'selected' : ''; ?>>
                                                                German</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8 mt-4">
                                                <button type="submit" name="save_advanced_settings"
                                                    class="btn btn-secondary">Update</button>
                                            </div>
                                        </div>
                                    </form>

                                </div>


                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->
                    </div>

                    <!-- end row -->

                </div><!-- container-fluid -->
            </div><!-- End Page-content -->

            <!-- Begin Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © Aquiry.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="http://codebucks.in/"
                                    target="_blank" class="text-muted">Codebucks</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- END Footer -->
            <!-- Begin scroll top -->

            <!-- END scroll top -->
        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->



    <!-- Bootstrap bundle js -->
    <script src="account/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Layouts main js -->
    <script src="account/assets/libs/jquery/jquery.min.js"></script>

    <!-- Metimenu js -->
    <script src="account/assets/libs/metismenu/metisMenu.min.js"></script>

    <!-- simplebar js -->
    <script src="account/assets/libs/simplebar/simplebar.min.js"></script>

    <script src="account/assets/libs/eva-icons/eva.min.js"></script>

    <!-- Scroll Top init -->
    <script src="account/assets/js/scroll-top.init.js"></script>
    <!-- bs custom file input plugin -->
    <script src="account/assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>

    <!-- form element init js -->
    <script src="account/assets/js/forms/form-bs-maxlength.init.js"></script>

    <!-- App js -->
    <script src="account/assets/js/app.js"></script>

</body>


</html>
