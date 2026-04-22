<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$projectAutoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (file_exists($projectAutoload)) {
    require_once $projectAutoload;
}


function getCount($table, $conn) {
    $query = "SELECT COUNT(*) AS total FROM $table";
    $result = mysqli_query($conn, $query);

    if(!$result) {
        die("Query Failed: " . mysqli_error($conn));
    }

    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function dbColumnExists($conn, $table, $column) {
    static $columnCache = [];

    $cacheKey = $table . '.' . $column;
    if (array_key_exists($cacheKey, $columnCache)) {
        return $columnCache[$cacheKey];
    }

    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $safeColumn = mysqli_real_escape_string($conn, $column);

    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$safeTable` LIKE '$safeColumn'");
    $columnCache[$cacheKey] = $result && mysqli_num_rows($result) > 0;

    return $columnCache[$cacheKey];
}

function dbTableExists($conn, $table) {
    static $tableCache = [];

    if (array_key_exists($table, $tableCache)) {
        return $tableCache[$table];
    }

    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$safeTable'");
    $tableCache[$table] = $result && mysqli_num_rows($result) > 0;

    return $tableCache[$table];
}

function ensureUserOnboardingSchema($conn) {
    static $checked = null;

    if (!$conn || !dbTableExists($conn, 'users')) {
        return false;
    }

    if ($checked !== null) {
        return $checked;
    }

    $columnDefinitions = [
        'account_type' => "ALTER TABLE users ADD COLUMN account_type VARCHAR(20) NULL DEFAULT NULL AFTER email",
        'profile_role' => "ALTER TABLE users ADD COLUMN profile_role VARCHAR(30) NULL DEFAULT NULL AFTER account_type",
        'engagement_stage' => "ALTER TABLE users ADD COLUMN engagement_stage VARCHAR(20) NULL DEFAULT NULL AFTER profile_role",
        'preferred_category_ids' => "ALTER TABLE users ADD COLUMN preferred_category_ids TEXT NULL AFTER engagement_stage",
        'onboarding_completed_at' => "ALTER TABLE users ADD COLUMN onboarding_completed_at DATETIME NULL DEFAULT NULL AFTER preferred_category_ids"
    ];

    $checked = true;

    foreach ($columnDefinitions as $column => $sql) {
        if (!dbColumnExists($conn, 'users', $column) && !mysqli_query($conn, $sql)) {
            $checked = false;
        }
    }

    return $checked;
}

function ensurePostMediaSchema($conn) {
    static $checked = null;

    if (!$conn || !dbTableExists($conn, 'posts')) {
        return false;
    }

    if ($checked !== null) {
        return $checked;
    }

    $columnDefinitions = [
        'media_type' => "ALTER TABLE posts ADD COLUMN media_type ENUM('image','video') NULL DEFAULT 'image' AFTER is_featured",
        'video_source' => "ALTER TABLE posts ADD COLUMN video_source ENUM('embed','upload') NULL DEFAULT NULL AFTER media_type",
        'video_provider' => "ALTER TABLE posts ADD COLUMN video_provider VARCHAR(20) NULL DEFAULT NULL AFTER video_source",
        'video_url' => "ALTER TABLE posts ADD COLUMN video_url VARCHAR(255) NULL DEFAULT NULL AFTER video_provider"
    ];

    $checked = true;

    foreach ($columnDefinitions as $column => $sql) {
        if (!dbColumnExists($conn, 'posts', $column) && !mysqli_query($conn, $sql)) {
            $checked = false;
        }
    }

    if (dbColumnExists($conn, 'posts', 'media_type')) {
        mysqli_query($conn, "ALTER TABLE posts MODIFY COLUMN media_type ENUM('image','video') NULL DEFAULT 'image'");
    }

    return $checked;
}

function normalizeOnboardingAccountType($value) {
    $value = strtolower(trim((string) $value));

    $allowed = ['poster', 'seeker', 'both'];
    return in_array($value, $allowed, true) ? $value : null;
}

function normalizeOnboardingRole($value) {
    $value = strtolower(trim((string) $value));

    $allowed = ['founder', 'developer', 'investor', 'creative'];
    return in_array($value, $allowed, true) ? $value : null;
}

function normalizeOnboardingStage($value) {
    $value = strtolower(trim((string) $value));

    $allowed = ['exploring', 'ready', 'urgent'];
    return in_array($value, $allowed, true) ? $value : null;
}

function parsePreferredCategoryIds($value, $limit = 4) {
    $rawIds = is_array($value) ? $value : explode(',', (string) $value);
    $categoryIds = [];

    foreach ($rawIds as $rawId) {
        $id = (int) $rawId;
        if ($id > 0) {
            $categoryIds[$id] = $id;
        }
    }

    $categoryIds = array_values($categoryIds);

    if ($limit > 0) {
        $categoryIds = array_slice($categoryIds, 0, $limit);
    }

    return $categoryIds;
}

function getOnboardingAccountTypeLabel($value) {
    $map = [
        'poster' => 'Poster',
        'seeker' => 'Seeker',
        'both' => 'Both'
    ];

    return $map[normalizeOnboardingAccountType($value)] ?? null;
}

function getOnboardingRoleLabel($value) {
    $map = [
        'founder' => 'Founder',
        'developer' => 'Developer',
        'investor' => 'Investor',
        'creative' => 'Creative'
    ];

    return $map[normalizeOnboardingRole($value)] ?? null;
}

function getOnboardingStageLabel($value) {
    $map = [
        'exploring' => 'Exploring ideas',
        'ready' => 'Ready to connect',
        'urgent' => 'Urgent'
    ];

    return $map[normalizeOnboardingStage($value)] ?? null;
}

function getOnboardingState($user) {
    $categoryIds = parsePreferredCategoryIds($user['preferred_category_ids'] ?? '');
    $accountType = normalizeOnboardingAccountType($user['account_type'] ?? null);
    $profileRole = normalizeOnboardingRole($user['profile_role'] ?? null);
    $engagementStage = normalizeOnboardingStage($user['engagement_stage'] ?? null);

    return [
        'account_type' => $accountType,
        'account_type_label' => getOnboardingAccountTypeLabel($accountType),
        'profile_role' => $profileRole,
        'profile_role_label' => getOnboardingRoleLabel($profileRole),
        'engagement_stage' => $engagementStage,
        'engagement_stage_label' => getOnboardingStageLabel($engagementStage),
        'preferred_category_ids' => $categoryIds,
        'category_count' => count($categoryIds),
        'completed_at' => $user['onboarding_completed_at'] ?? null,
    ];
}

function isUserOnboardingComplete($user) {
    if (empty($user) || !empty($user['is_admin'])) {
        return true;
    }

    $state = getOnboardingState($user);

    return $state['account_type'] !== null
        && $state['profile_role'] !== null
        && $state['engagement_stage'] !== null
        && $state['category_count'] > 0
        && !empty($state['completed_at']);
}

function getSettingsRow($conn) {
    static $settingsCache = null;

    if (!$conn) {
        return [];
    }

    if ($settingsCache === null) {
        $settingsCache = [];
        $result = mysqli_query($conn, "SELECT * FROM settings WHERE id = 1 LIMIT 1");

        if ($result && mysqli_num_rows($result) > 0) {
            $settingsCache = mysqli_fetch_assoc($result);
        }
    }

    return $settingsCache;
}

function getSettingValue($conn, $column, $default = null) {
    $settings = getSettingsRow($conn);
    return $settings[$column] ?? $default;
}

function isSettingEnabled($conn, $column, $default = true) {
    $value = getSettingValue($conn, $column, $default ? 1 : 0);
    return (int) $value === 1;
}

function normalizePostMediaType($value) {
    $value = strtolower(trim((string) $value));
    return in_array($value, ['image', 'video'], true) ? $value : 'image';
}

function normalizePostVideoSource($value) {
    $value = strtolower(trim((string) $value));
    return in_array($value, ['embed', 'upload'], true) ? $value : '';
}

function normalizePostVideoProvider($value) {
    $value = strtolower(trim((string) $value));
    return in_array($value, ['youtube', 'vimeo', 'upload'], true) ? $value : '';
}

function sanitizeExternalHttpUrl($value) {
    $value = trim((string) $value);

    if ($value === '' || !filter_var($value, FILTER_VALIDATE_URL)) {
        return '';
    }

    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return '';
    }

    return $value;
}

function extractYouTubeVideoId($value) {
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value) === 1) {
        return $value;
    }

    $url = sanitizeExternalHttpUrl($value);
    if ($url === '') {
        return null;
    }

    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    $query = (string) parse_url($url, PHP_URL_QUERY);

    if (strpos($host, 'youtu.be') !== false) {
        $segments = explode('/', $path);
        $candidate = $segments[0] ?? '';
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) === 1 ? $candidate : null;
    }

    if (strpos($host, 'youtube.com') === false) {
        return null;
    }

    if ($path === 'watch') {
        parse_str($query, $params);
        $candidate = trim((string) ($params['v'] ?? ''));
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) === 1 ? $candidate : null;
    }

    $segments = array_values(array_filter(explode('/', $path)));
    if (count($segments) >= 2 && in_array($segments[0], ['embed', 'shorts', 'live'], true)) {
        $candidate = trim((string) $segments[1]);
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) === 1 ? $candidate : null;
    }

    return null;
}

function extractVimeoVideoId($value) {
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    if (preg_match('/^\d+$/', $value) === 1) {
        return $value;
    }

    $url = sanitizeExternalHttpUrl($value);
    if ($url === '') {
        return null;
    }

    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    if (strpos($host, 'vimeo.com') === false) {
        return null;
    }

    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    $segments = array_values(array_filter(explode('/', $path)));

    for ($i = count($segments) - 1; $i >= 0; $i--) {
        if (preg_match('/^\d+$/', $segments[$i]) === 1) {
            return $segments[$i];
        }
    }

    return null;
}

function parseExternalVideoReference($value) {
    $youtubeId = extractYouTubeVideoId($value);
    if ($youtubeId !== null) {
        return [
            'is_valid' => true,
            'provider' => 'youtube',
            'video_id' => $youtubeId,
            'canonical_url' => 'https://www.youtube.com/watch?v=' . rawurlencode($youtubeId),
            'embed_url' => 'https://www.youtube.com/embed/' . rawurlencode($youtubeId) . '?rel=0&modestbranding=1',
            'poster_url' => 'https://i.ytimg.com/vi/' . rawurlencode($youtubeId) . '/hqdefault.jpg',
            'provider_label' => 'YouTube',
        ];
    }

    $vimeoId = extractVimeoVideoId($value);
    if ($vimeoId !== null) {
        return [
            'is_valid' => true,
            'provider' => 'vimeo',
            'video_id' => $vimeoId,
            'canonical_url' => 'https://vimeo.com/' . rawurlencode($vimeoId),
            'embed_url' => 'https://player.vimeo.com/video/' . rawurlencode($vimeoId),
            'poster_url' => '',
            'provider_label' => 'Vimeo',
        ];
    }

    return [
        'is_valid' => false,
        'provider' => '',
        'video_id' => '',
        'canonical_url' => '',
        'embed_url' => '',
        'poster_url' => '',
        'provider_label' => '',
    ];
}

function getPostUploadsBaseDirectory() {
    return dirname(__DIR__) . '/uploads';
}

function normalizePostUploadPath($value) {
    $value = trim(str_replace('\\', '/', (string) $value));

    if ($value === '') {
        return '';
    }

    if (strpos($value, 'account/uploads/') === 0) {
        $value = substr($value, strlen('account/uploads/'));
    }

    $value = ltrim($value, '/');

    if ($value === '' || strpos($value, '..') !== false) {
        return '';
    }

    return $value;
}

function getPostUploadFilesystemPath($value) {
    $relativePath = normalizePostUploadPath($value);
    if ($relativePath === '') {
        return '';
    }

    return getPostUploadsBaseDirectory() . '/' . $relativePath;
}

function getPostUploadUrl($value) {
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/^(https?:)?\/\//i', $value)) {
        return $value;
    }

    $relativePath = normalizePostUploadPath($value);
    return $relativePath !== '' ? 'account/uploads/' . $relativePath : '';
}

function deletePostUploadAsset($value) {
    $path = getPostUploadFilesystemPath($value);
    if ($path === '' || !file_exists($path)) {
        return false;
    }

    return unlink($path);
}

function detectUploadedFileMimeType($tmpPath) {
    if (!is_string($tmpPath) || $tmpPath === '' || !file_exists($tmpPath)) {
        return '';
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);

            if (is_string($mime)) {
                return $mime;
            }
        }
    }

    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($tmpPath);
        return is_string($mime) ? $mime : '';
    }

    return '';
}

function storePostUpload(array $file, $subdirectory, $prefix, array $allowedMimeExtensions, $maxSizeBytes) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }

    if (($file['size'] ?? 0) > $maxSizeBytes) {
        throw new RuntimeException('Uploaded file exceeds the allowed size.');
    }

    $mime = detectUploadedFileMimeType((string) ($file['tmp_name'] ?? ''));
    if ($mime === '' || !isset($allowedMimeExtensions[$mime])) {
        throw new RuntimeException('Uploaded file format is not supported.');
    }

    $subdirectory = trim(str_replace('\\', '/', (string) $subdirectory), '/');
    $baseDirectory = getPostUploadsBaseDirectory();
    $targetDirectory = $subdirectory !== '' ? $baseDirectory . '/' . $subdirectory : $baseDirectory;

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
        throw new RuntimeException('Could not prepare upload directory.');
    }

    $extension = $allowedMimeExtensions[$mime];
    $filename = uniqid($prefix, true) . '.' . $extension;
    $destination = $targetDirectory . '/' . $filename;

    if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not store uploaded file.');
    }

    return [
        'stored_path' => ($subdirectory !== '' ? $subdirectory . '/' : '') . $filename,
        'filesystem_path' => $destination,
        'public_url' => 'account/uploads/' . ($subdirectory !== '' ? $subdirectory . '/' : '') . $filename,
        'mime' => $mime,
    ];
}

function getPostMediaDetails($post) {
    $mediaType = normalizePostMediaType($post['media_type'] ?? 'image');
    $posterUrl = getPostUploadUrl($post['thumbnail'] ?? '');
    $videoSource = normalizePostVideoSource($post['video_source'] ?? '');
    $videoProvider = normalizePostVideoProvider($post['video_provider'] ?? '');
    $rawVideoValue = trim((string) ($post['video_url'] ?? ''));
    $videoEmbedUrl = '';
    $videoFileUrl = '';
    $canonicalUrl = '';
    $videoId = '';

    if ($mediaType === 'video') {
        if (($videoSource === 'upload' || $videoProvider === 'upload') && $rawVideoValue !== '') {
            $videoSource = 'upload';
            $videoProvider = 'upload';
            $videoFileUrl = getPostUploadUrl($rawVideoValue);
            $canonicalUrl = $videoFileUrl;
        } else {
            if ($videoSource === 'embed' && in_array($videoProvider, ['youtube', 'vimeo'], true) && $rawVideoValue !== '') {
                $videoId = $rawVideoValue;
            } else {
                $parsedVideo = parseExternalVideoReference($rawVideoValue);
                if ($parsedVideo['is_valid']) {
                    $videoSource = 'embed';
                    $videoProvider = $parsedVideo['provider'];
                    $videoId = $parsedVideo['video_id'];
                    $videoEmbedUrl = $parsedVideo['embed_url'];
                    $canonicalUrl = $parsedVideo['canonical_url'];

                    if ($posterUrl === '' && $parsedVideo['poster_url'] !== '') {
                        $posterUrl = $parsedVideo['poster_url'];
                    }
                } elseif ($rawVideoValue !== '' && getPostUploadUrl($rawVideoValue) !== '') {
                    $videoSource = 'upload';
                    $videoProvider = 'upload';
                    $videoFileUrl = getPostUploadUrl($rawVideoValue);
                    $canonicalUrl = $videoFileUrl;
                }
            }

            if ($videoId !== '' && $videoEmbedUrl === '') {
                if ($videoProvider === 'youtube') {
                    $videoEmbedUrl = 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?rel=0&modestbranding=1';
                    $canonicalUrl = 'https://www.youtube.com/watch?v=' . rawurlencode($videoId);
                    if ($posterUrl === '') {
                        $posterUrl = 'https://i.ytimg.com/vi/' . rawurlencode($videoId) . '/hqdefault.jpg';
                    }
                } elseif ($videoProvider === 'vimeo') {
                    $videoEmbedUrl = 'https://player.vimeo.com/video/' . rawurlencode($videoId);
                    $canonicalUrl = 'https://vimeo.com/' . rawurlencode($videoId);
                }
            }
        }
    }

    $providerLabels = [
        'youtube' => 'YouTube',
        'vimeo' => 'Vimeo',
        'upload' => 'Uploaded video',
    ];

    return [
        'media_type' => $mediaType,
        'is_video' => $mediaType === 'video',
        'is_image' => $mediaType === 'image',
        'poster_url' => $posterUrl,
        'has_visual_preview' => $posterUrl !== '' || $mediaType === 'video',
        'video_source' => $videoSource,
        'video_provider' => $videoProvider,
        'video_provider_label' => $providerLabels[$videoProvider] ?? ($mediaType === 'video' ? 'Video' : ''),
        'video_id' => $videoId,
        'video_embed_url' => $videoEmbedUrl,
        'video_file_url' => $videoFileUrl,
        'canonical_url' => $canonicalUrl,
        'is_embed' => $videoEmbedUrl !== '',
        'is_uploaded_video' => $videoFileUrl !== '',
    ];
}

function getPostVideoInputValue($post) {
    $media = getPostMediaDetails($post);
    return $media['is_embed'] ? $media['canonical_url'] : '';
}

function renderPostMediaPreview($post, $linkUrl, $title, $imageBadgeText = 'Open story', $videoBadgeText = 'Watch video') {
    $media = getPostMediaDetails($post);
    if (!$media['has_visual_preview']) {
        return '';
    }

    $safeTitle = htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8');
    $safeLink = htmlspecialchars((string) $linkUrl, ENT_QUOTES, 'UTF-8');
    $badgeText = $media['is_video'] ? $videoBadgeText : $imageBadgeText;
    $badgeIcon = $media['is_video'] ? 'mdi mdi-play-circle-outline' : 'mdi mdi-arrow-top-right';

    if ($media['poster_url'] !== '') {
        $safePoster = htmlspecialchars($media['poster_url'], ENT_QUOTES, 'UTF-8');

        return '<a href="' . $safeLink . '" class="modern-post-card__media">'
            . '<img src="' . $safePoster . '" class="modern-post-card__image" alt="' . $safeTitle . '">'
            . '<span class="modern-post-card__media-badge"><i class="' . $badgeIcon . '"></i>' . htmlspecialchars($badgeText, ENT_QUOTES, 'UTF-8') . '</span>'
            . '</a>';
    }

    if (!$media['is_video']) {
        return '';
    }

    $fallbackLabel = htmlspecialchars($media['video_provider_label'], ENT_QUOTES, 'UTF-8');

    return '<a href="' . $safeLink . '" class="modern-post-card__media">'
        . '<div class="modern-post-card__image" style="display:flex;align-items:center;justify-content:center;min-height:220px;background:linear-gradient(135deg,#0f172a 0%,#2563eb 100%);color:#fff;padding:1.5rem;text-align:center;">'
        . '<div><i class="mdi mdi-play-circle-outline" style="font-size:2.5rem;display:block;margin-bottom:0.5rem;"></i><span style="font-weight:700;">' . ($fallbackLabel !== '' ? $fallbackLabel : 'Video') . '</span></div>'
        . '</div>'
        . '<span class="modern-post-card__media-badge"><i class="' . $badgeIcon . '"></i>' . htmlspecialchars($videoBadgeText, ENT_QUOTES, 'UTF-8') . '</span>'
        . '</a>';
}

function getEmailTemplateDefaults() {
    return [
        'tpl_welcome_subject' => 'Welcome to {{site_title}}',
        'tpl_welcome_body' => "Hi {{firstname}},\n\nWelcome to {{site_title}}. We're glad to have you on board.\n\nYou can get started here: {{site_url}}",
        'tpl_password_reset_subject' => 'Reset your {{site_title}} password',
        'tpl_password_reset_body' => "Hi {{firstname}},\n\nWe received a password reset request for your account.\n\nReset link: {{reset_link}}",
        'tpl_new_post_subject' => 'Your post was submitted on {{site_title}}',
        'tpl_new_post_body' => "Hi {{firstname}},\n\nYour post \"{{post_title}}\" has been submitted on {{site_title}}.\n\nView post: {{post_link}}",
        'tpl_reply_subject' => 'New reply to your comment on {{site_title}}',
        'tpl_reply_body' => "Hi {{firstname}},\n\n{{actor_name}} replied to your comment on \"{{post_title}}\".\n\nView discussion: {{post_link}}",
        'tpl_comment_subject' => 'New comment on your post on {{site_title}}',
        'tpl_comment_body' => "Hi {{firstname}},\n\n{{actor_name}} commented on your post \"{{post_title}}\".\n\nView discussion: {{post_link}}",
        'tpl_admin_new_user_subject' => 'New user signup on {{site_title}}',
        'tpl_admin_new_user_body' => "Hello Admin,\n\nA new user has signed up.\n\nName: {{fullname}}\nEmail: {{email}}\nUsername: {{username}}",
        'tpl_admin_new_post_subject' => 'New post submission on {{site_title}}',
        'tpl_admin_new_post_body' => "Hello Admin,\n\nA new post has been submitted.\n\nTitle: {{post_title}}\nAuthor: {{actor_name}}\nView post: {{post_link}}"
    ];
}

function renderSettingTemplate($content, $variables = []) {
    $replacements = [];

    foreach ($variables as $key => $value) {
        $replacements['{{' . $key . '}}'] = (string) $value;
    }

    return strtr($content, $replacements);
}

function sanitizeRichTextHtml($html) {
    $html = trim((string) $html);

    if ($html === '') {
        return '';
    }

    if (!class_exists('DOMDocument')) {
        return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote><code><pre>');
    }

    $allowedTags = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        'ul',
        'ol',
        'li',
        'a',
        'h2',
        'h3',
        'h4',
        'blockquote',
        'code',
        'pre'
    ];

    $allowedAttributes = [
        'a' => ['href', 'target', 'rel']
    ];

    $document = new DOMDocument('1.0', 'UTF-8');
    $previousLibxmlSetting = libxml_use_internal_errors(true);
    $document->loadHTML(
        '<?xml encoding="utf-8" ?><body>' . $html . '</body>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previousLibxmlSetting);

    $body = $document->getElementsByTagName('body')->item(0);
    if (!$body) {
        return '';
    }

    $sanitizeNode = function ($node) use (&$sanitizeNode, $allowedTags, $allowedAttributes) {
        if (!$node || !$node->hasChildNodes()) {
            return;
        }

        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);

            if ($child instanceof DOMElement) {
                $tagName = strtolower($child->tagName);

                if (!in_array($tagName, $allowedTags, true)) {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }

                $allowedForTag = $allowedAttributes[$tagName] ?? [];
                if ($child->hasAttributes()) {
                    for ($j = $child->attributes->length - 1; $j >= 0; $j--) {
                        $attribute = $child->attributes->item($j);
                        if ($attribute) {
                            $attributeName = strtolower((string) $attribute->nodeName);

                            if (!in_array($attributeName, $allowedForTag, true)) {
                                $child->removeAttribute($attribute->nodeName);
                            }
                        }
                    }
                }

                if ($tagName === 'a') {
                    $href = trim((string) $child->getAttribute('href'));

                    if ($href === '') {
                        $child->removeAttribute('href');
                    } elseif (!preg_match('/^(https?:|mailto:|tel:|\/|#)/i', $href)) {
                        $child->removeAttribute('href');
                    }

                    $target = trim((string) $child->getAttribute('target'));
                    if ($target !== '_blank') {
                        $child->removeAttribute('target');
                    }

                    if ($child->hasAttribute('target')) {
                        $child->setAttribute('rel', 'noopener noreferrer');
                    } else {
                        $child->removeAttribute('rel');
                    }
                }

                $sanitizeNode($child);
            }
        }
    };

    $sanitizeNode($body);

    $cleanHtml = '';
    foreach ($body->childNodes as $childNode) {
        $cleanHtml .= $document->saveHTML($childNode);
    }

    return trim($cleanHtml);
}

function sendConfiguredEmail($conn, $toEmail, $toName, $subject, $htmlBody, $altBody = '') {
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL) || !class_exists(PHPMailer::class)) {
        return false;
    }

    $smtpHost = trim((string) getSettingValue($conn, 'smtp_host', ''));
    $smtpPort = (int) getSettingValue($conn, 'smtp_port', 587);
    $smtpUsername = trim((string) getSettingValue($conn, 'smtp_username', ''));
    $smtpPassword = (string) getSettingValue($conn, 'smtp_password', '');
    $smtpEncryption = trim((string) getSettingValue($conn, 'smtp_encryption', 'tls'));
    $smtpFromEmail = trim((string) getSettingValue($conn, 'smtp_from_email', $smtpUsername));
    $smtpFromName = trim((string) getSettingValue($conn, 'smtp_from_name', getSettingValue($conn, 'site_title', 'IdeaHub')));

    if ($smtpHost === '' || $smtpPort <= 0 || $smtpFromEmail === '') {
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort;
        $mail->SMTPAuth = $smtpUsername !== '' || $smtpPassword !== '';
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;

        if (in_array($smtpEncryption, ['tls', 'ssl'], true)) {
            $mail->SMTPSecure = $smtpEncryption;
        }

        $mail->setFrom($smtpFromEmail, $smtpFromName);
        $mail->addAddress($toEmail, $toName ?: $toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody !== '' ? $altBody : trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
        $mail->send();

        return true;
    } catch (Exception $exception) {
        error_log('Mail send failed: ' . $exception->getMessage());
        return false;
    }
}

function sendEventEmail($conn, $eventKey, $toEmail, $toName = '', $variables = []) {
    $eventMap = [
        'welcome_email' => ['toggle' => 'notify_welcome_email', 'subject' => 'tpl_welcome_subject', 'body' => 'tpl_welcome_body'],
        'password_reset' => ['toggle' => 'notify_password_reset_email', 'subject' => 'tpl_password_reset_subject', 'body' => 'tpl_password_reset_body'],
        'new_post_notification' => ['toggle' => 'notify_new_post_email', 'subject' => 'tpl_new_post_subject', 'body' => 'tpl_new_post_body'],
        'reply_notification' => ['toggle' => 'notify_reply_email', 'subject' => 'tpl_reply_subject', 'body' => 'tpl_reply_body'],
        'comment_notification' => ['toggle' => 'notify_comment_email', 'subject' => 'tpl_comment_subject', 'body' => 'tpl_comment_body'],
        'admin_new_user' => ['toggle' => 'notify_admin_new_user', 'subject' => 'tpl_admin_new_user_subject', 'body' => 'tpl_admin_new_user_body'],
        'admin_new_post' => ['toggle' => 'notify_admin_new_post', 'subject' => 'tpl_admin_new_post_subject', 'body' => 'tpl_admin_new_post_body']
    ];

    if (!isset($eventMap[$eventKey]) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $event = $eventMap[$eventKey];
    if (!isSettingEnabled($conn, $event['toggle'], false)) {
        return false;
    }

    $defaults = getEmailTemplateDefaults();
    $variables = array_merge([
        'site_title' => getSettingValue($conn, 'site_title', 'IdeaHub'),
        'site_url' => getSettingValue($conn, 'site_url', ''),
        'firstname' => $toName,
        'fullname' => $toName,
        'email' => $toEmail
    ], $variables);

    $subject = renderSettingTemplate(getSettingValue($conn, $event['subject'], $defaults[$event['subject']] ?? ''), $variables);
    $bodyText = renderSettingTemplate(getSettingValue($conn, $event['body'], $defaults[$event['body']] ?? ''), $variables);
    $bodyHtml = nl2br(htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8'));

    return sendConfiguredEmail($conn, $toEmail, $toName, $subject, $bodyHtml, $bodyText);
}

function getAdminNotificationRecipients($conn) {
    $configuredEmail = trim((string) getSettingValue($conn, 'admin_notification_email', ''));
    $configuredName = trim((string) getSettingValue($conn, 'smtp_from_name', 'Admin'));

    if ($configuredEmail !== '' && filter_var($configuredEmail, FILTER_VALIDATE_EMAIL)) {
        return [
            ['email' => $configuredEmail, 'name' => $configuredName]
        ];
    }

    $recipients = [];
    $result = mysqli_query($conn, "SELECT firstname, lastname, email FROM users WHERE is_admin = 1 AND email <> ''");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $recipients[] = [
                    'email' => $row['email'],
                    'name' => trim($row['firstname'] . ' ' . $row['lastname'])
                ];
            }
        }
    }

    return $recipients;
}

function sendAdminEventEmail($conn, $eventKey, $variables = []) {
    $sent = false;

    foreach (getAdminNotificationRecipients($conn) as $recipient) {
        $sent = sendEventEmail(
            $conn,
            $eventKey,
            $recipient['email'],
            $recipient['name'],
            $variables
        ) || $sent;
    }

    return $sent;
}

function createNotification($conn, $userId, $type, $title, $message, $link = '#', $actorUserId = null) {
    if (!$conn || !$userId || !dbTableExists($conn, 'notifications')) {
        return false;
    }

    $hasActor = dbColumnExists($conn, 'notifications', 'actor_user_id');

    $columns = ['user_id', 'type', 'title', 'message', 'link'];
    $values = [$userId, $type, $title, $message, $link];
    $types = 'issss';

    if ($hasActor) {
        $columns[] = 'actor_user_id';
        $values[] = $actorUserId;
        $types .= 'i';
    }

    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $sql = "INSERT INTO notifications (" . implode(', ', $columns) . ") VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $bindValues = [];
    foreach ($values as $index => $value) {
        $bindValues[$index] = $value;
    }

    $stmt->bind_param($types, ...$bindValues);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function ensureFollowersTable($conn) {
    static $checked = null;

    if (!$conn) {
        return false;
    }

    if ($checked !== null) {
        return $checked;
    }

    $sql = "
        CREATE TABLE IF NOT EXISTS followers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            follower_id INT UNSIGNED NOT NULL,
            following_id INT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_follow_pair (follower_id, following_id),
            KEY idx_following_id (following_id),
            KEY idx_follower_id (follower_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $checked = (bool) mysqli_query($conn, $sql);

    return $checked;
}

function getFollowerCount($conn, $userId) {
    if (!$conn || !$userId || !ensureFollowersTable($conn)) {
        return 0;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM followers WHERE following_id = ?");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : ['total' => 0];
    $stmt->close();

    return (int) ($row['total'] ?? 0);
}

function getFollowingCount($conn, $userId) {
    if (!$conn || !$userId || !ensureFollowersTable($conn)) {
        return 0;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM followers WHERE follower_id = ?");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : ['total' => 0];
    $stmt->close();

    return (int) ($row['total'] ?? 0);
}

function isFollowingUser($conn, $followerId, $followingId) {
    if (
        !$conn ||
        !$followerId ||
        !$followingId ||
        $followerId === $followingId ||
        !ensureFollowersTable($conn)
    ) {
        return false;
    }

    $stmt = $conn->prepare("
        SELECT 1
        FROM followers
        WHERE follower_id = ? AND following_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $followerId, $followingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $isFollowing = $result && $result->num_rows > 0;
    $stmt->close();

    return $isFollowing;
}

function toggleFollowUser($conn, $followerId, $followingId) {
    if (
        !$conn ||
        !$followerId ||
        !$followingId ||
        $followerId === $followingId ||
        !ensureFollowersTable($conn)
    ) {
        return ['success' => false, 'action' => null];
    }

    if (isFollowingUser($conn, $followerId, $followingId)) {
        $stmt = $conn->prepare("
            DELETE FROM followers
            WHERE follower_id = ? AND following_id = ?
        ");

        if (!$stmt) {
            return ['success' => false, 'action' => null];
        }

        $stmt->bind_param('ii', $followerId, $followingId);
        $success = $stmt->execute();
        $stmt->close();

        return ['success' => $success, 'action' => $success ? 'unfollowed' : null];
    }

    $stmt = $conn->prepare("
        INSERT INTO followers (follower_id, following_id)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        return ['success' => false, 'action' => null];
    }

    $stmt->bind_param('ii', $followerId, $followingId);
    $success = $stmt->execute();
    $stmt->close();

    return ['success' => $success, 'action' => $success ? 'followed' : null];
}

function getUserNotifications($conn, $userId, $limit = 8) {
    if (!$conn || !$userId || !dbTableExists($conn, 'notifications')) {
        return [];
    }

    $limit = max(1, (int) $limit);
    $hasReadAt = dbColumnExists($conn, 'notifications', 'read_at');
    $readExpr = $hasReadAt ? "CASE WHEN read_at IS NULL THEN 0 ELSE 1 END AS is_read" : "0 AS is_read";
    $createdColumn = dbColumnExists($conn, 'notifications', 'created_at') ? 'created_at' : 'id';

    $stmt = $conn->prepare("
        SELECT id, type, title, message, link, $readExpr, $createdColumn AS created_value
        FROM notifications
        WHERE user_id = ?
        ORDER BY $createdColumn DESC
        LIMIT ?
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $notifications;
}

function getUnreadNotificationCount($conn, $userId) {
    if (!$conn || !$userId || !dbTableExists($conn, 'notifications')) {
        return 0;
    }

    if (!dbColumnExists($conn, 'notifications', 'read_at')) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ?");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND read_at IS NULL");
    }

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : ['total' => 0];
    $stmt->close();

    return (int) ($row['total'] ?? 0);
}

function getNotificationMeta($type) {
    $map = [
        'welcome' => ['icon' => 'mdi-account-plus-outline', 'avatar' => 'avatar-label-primary'],
        'new_post' => ['icon' => 'mdi-file-document-outline', 'avatar' => 'avatar-label-secondary'],
        'comment' => ['icon' => 'mdi-comment-processing-outline', 'avatar' => 'avatar-label-success'],
        'reply' => ['icon' => 'mdi-reply-outline', 'avatar' => 'avatar-label-info'],
        'follow' => ['icon' => 'mdi-account-heart-outline', 'avatar' => 'avatar-label-primary'],
        'security' => ['icon' => 'mdi-shield-check-outline', 'avatar' => 'avatar-label-dark'],
        'admin_new_user' => ['icon' => 'mdi-account-group-outline', 'avatar' => 'avatar-label-warning'],
        'admin_new_post' => ['icon' => 'mdi-bell-badge-outline', 'avatar' => 'avatar-label-danger']
    ];

    return $map[$type] ?? ['icon' => 'mdi-bell-outline', 'avatar' => 'avatar-label-secondary'];
}

function markNotificationsRead($conn, $userId, $notificationIds = []) {
    if (
        !$conn ||
        !$userId ||
        !dbTableExists($conn, 'notifications') ||
        !dbColumnExists($conn, 'notifications', 'read_at')
    ) {
        return false;
    }

    if (empty($notificationIds)) {
        $stmt = $conn->prepare("
            UPDATE notifications
            SET read_at = NOW()
            WHERE user_id = ? AND read_at IS NULL
        ");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $userId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    $ids = array_values(array_filter(array_map('intval', $notificationIds)));
    if (empty($ids)) {
        return false;
    }

    $placeholders = implode(', ', array_fill(0, count($ids), '?'));
    $types = 'i' . str_repeat('i', count($ids));
    $sql = "
        UPDATE notifications
        SET read_at = NOW()
        WHERE user_id = ? AND id IN ($placeholders) AND read_at IS NULL
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $params = array_merge([$userId], $ids);
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
