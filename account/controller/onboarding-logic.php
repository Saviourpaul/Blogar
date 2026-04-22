<?php

require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: onboarding');
    exit;
}

if (empty($_SESSION['user-id'])) {
    header('Location: signin');
    exit;
}

ensureUserOnboardingSchema($connection);

$currentUserId = (int) $_SESSION['user-id'];
$userStmt = $connection->prepare("
    SELECT id, is_admin, account_type
    FROM users
    WHERE id = ?
    LIMIT 1
");
$userStmt->bind_param('i', $currentUserId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$currentUser = $userResult ? $userResult->fetch_assoc() : null;
$userStmt->close();

if (!$currentUser || !empty($currentUser['is_admin'])) {
    header('Location: dashboard');
    exit;
}

$accountType = normalizeOnboardingAccountType($_POST['account_type'] ?? ($currentUser['account_type'] ?? ''));
$profileRole = normalizeOnboardingRole($_POST['profile_role'] ?? '');
$engagementStage = normalizeOnboardingStage($_POST['engagement_stage'] ?? '');
$preferredCategoryIds = parsePreferredCategoryIds($_POST['preferred_categories'] ?? []);

$_SESSION['onboarding-data'] = [
    'account_type' => $accountType,
    'profile_role' => $profileRole,
    'engagement_stage' => $engagementStage,
    'preferred_categories' => $preferredCategoryIds,
];

if ($accountType === null) {
    $_SESSION['onboarding-error'] = 'Choose whether you want a poster, seeker, or hybrid experience.';
    header('Location: onboarding');
    exit;
}

if ($profileRole === null) {
    $_SESSION['onboarding-error'] = 'Choose the role you want displayed on your profile.';
    header('Location: onboarding');
    exit;
}

if ($engagementStage === null) {
    $_SESSION['onboarding-error'] = 'Choose how fast you want to move right now.';
    header('Location: onboarding');
    exit;
}

if (count($preferredCategoryIds) === 0) {
    $_SESSION['onboarding-error'] = 'Pick at least one category so we can personalize your feed.';
    header('Location: onboarding');
    exit;
}

if (count($preferredCategoryIds) > 4) {
    $_SESSION['onboarding-error'] = 'Choose no more than 4 categories.';
    header('Location: onboarding');
    exit;
}

if (!dbTableExists($connection, 'categories')) {
    $_SESSION['onboarding-error'] = 'Categories are not available yet. Please try again after categories are set up.';
    header('Location: onboarding');
    exit;
}

$placeholders = implode(', ', array_fill(0, count($preferredCategoryIds), '?'));
$types = str_repeat('i', count($preferredCategoryIds));
$categoryStmt = $connection->prepare("SELECT id FROM categories WHERE id IN ($placeholders)");

if (!$categoryStmt) {
    $_SESSION['onboarding-error'] = 'We could not save your setup just now. Please try again.';
    header('Location: onboarding');
    exit;
}

$categoryStmt->bind_param($types, ...$preferredCategoryIds);
$categoryStmt->execute();
$validCategoryResult = $categoryStmt->get_result();
$validCategoryIds = [];

if ($validCategoryResult) {
    while ($row = $validCategoryResult->fetch_assoc()) {
        $validCategoryIds[] = (int) $row['id'];
    }
}

$categoryStmt->close();

if (empty($validCategoryIds)) {
    $_SESSION['onboarding-error'] = 'Choose at least one valid category to personalize the feed.';
    header('Location: onboarding');
    exit;
}

$preferredCategoryValue = implode(',', $validCategoryIds);
$updateStmt = $connection->prepare("
    UPDATE users
    SET account_type = ?,
        profile_role = ?,
        engagement_stage = ?,
        preferred_category_ids = ?,
        onboarding_completed_at = NOW()
    WHERE id = ?
    LIMIT 1
");

if (!$updateStmt) {
    $_SESSION['onboarding-error'] = 'We could not save your setup just now. Please try again.';
    header('Location: onboarding');
    exit;
}

$updateStmt->bind_param(
    'ssssi',
    $accountType,
    $profileRole,
    $engagementStage,
    $preferredCategoryValue,
    $currentUserId
);
$saved = $updateStmt->execute();
$updateStmt->close();

if (!$saved) {
    $_SESSION['onboarding-error'] = 'We could not save your setup just now. Please try again.';
    header('Location: onboarding');
    exit;
}

unset($_SESSION['onboarding-data'], $_SESSION['onboarding-error']);

header('Location: dashboard');
exit;
