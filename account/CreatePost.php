<?php $pageTitle = 'CreatePost';
require 'includes/header.php';

ensurePostMediaSchema($connection);

$appBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($appBasePath === '/' || $appBasePath === '.') {
    $appBasePath = '';
}
$tinyMceBaseUrl = $appBasePath . '/account/assets/libs/tinymce';



if (!isset($_SESSION['user-id'])) {
    header("Location: signin");
    exit();
}

$postSettingResult = mysqli_query($connection, "SELECT enable_post FROM settings WHERE id = 1 LIMIT 1");
$is_create_post_enabled = true;

if ($postSettingResult && mysqli_num_rows($postSettingResult) > 0) {
    $postSetting = mysqli_fetch_assoc($postSettingResult);
    $is_create_post_enabled = !isset($postSetting['enable_post']) || (int) $postSetting['enable_post'] === 1;
}

if (!$is_create_post_enabled) {
    $_SESSION['add-post'] = "Create post is currently disabled in settings.";
    header("Location: managePost");
    exit();
}

$query = "SELECT * FROM categories";
$categories = mysqli_query($connection, $query);

$draft = $_SESSION['add-post-data'] ?? [];
$title = $draft['title'] ?? '';
$body = $draft['body'] ?? '';
$selected_category = (int) ($draft['category'] ?? 0);
$media_type = normalizePostMediaType($draft['media_type'] ?? 'image');
$video_source = normalizePostVideoSource($draft['video_source'] ?? 'embed');
$video_link = trim((string) ($draft['video_link'] ?? $draft['video_url'] ?? ''));
$is_featured_checked = array_key_exists('is_featured', $draft) ? !empty($draft['is_featured']) : !empty($_SESSION['is_admin']);

unset($_SESSION['add-post-data']);



?>




<body>
    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- Start topbar -->

        <!-- End topbar -->
        <!-- ========== Left Sidebar Start ========== -->
        <?= include 'includes/sidebar.php' ?>
        

        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ========== Left Sidebar Start ========== -->

        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->

                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">post  Information</h4>
                                </div>
                                <div class="card-body">
                                        <form action="add-post-logic" enctype="multipart/form-data"
                                            method="POST" id="create-post-form">
                                            <div class="col-lg-8">
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <label for="title" class="form-label">Title</label>
                                                        <input type="text" name="title" id="title"
                                                            value="<?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?>"
                                                            class="form-control" placeholder="Enter Title" required>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="category" class="form-label">Category *</label>
                                                        <select name="category" id="category" class="form-select" required>
                                                            <option value="" disabled <?= $selected_category <= 0 ? 'selected' : '' ?>>Select Category</option>
                                                            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                                                <option value="<?= (int) $category['id'] ?>" <?= $selected_category === (int) $category['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php endwhile ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="media_type" class="form-label">Post format *</label>
                                                        <select name="media_type" id="media_type" class="form-select" required>
                                                            <option value="image" <?= $media_type === 'image' ? 'selected' : '' ?>>Image post</option>
                                                            <option value="video" <?= $media_type === 'video' ? 'selected' : '' ?>>Video post</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6" id="video-source-group" <?= $media_type === 'video' ? '' : 'hidden' ?>>
                                                        <label for="video_source" class="form-label">Video source *</label>
                                                        <select name="video_source" id="video_source" class="form-select">
                                                            <option value="embed" <?= $video_source === 'embed' ? 'selected' : '' ?>>URL</option>
                                                            <option value="upload" <?= $video_source === 'upload' ? 'selected' : '' ?>>Upload video file</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-12">
                                                        <label for="post-body-editor" class="form-label">Description</label>
                                                        <textarea class="form-control" id="post-body-editor" rows="10" name="body" placeholder="Description"><?= htmlspecialchars((string) $body, ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>

                                                    <div class="col-12" id="video-link-group" <?= $media_type === 'video' && $video_source === 'embed' ? '' : 'hidden' ?>>
                                                        <label for="video_link" class="form-label">YouTube or Vimeo URL</label>
                                                        <input
                                                            type="url"
                                                            name="video_link"
                                                            id="video_link"
                                                            value="<?= htmlspecialchars($video_link, ENT_QUOTES, 'UTF-8') ?>"
                                                            class="form-control"
                                                            placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/...">
                                                        <div class="form-text">Paste a YouTube or Vimeo link. We store the provider and video ID for clean embeds.</div>
                                                    </div>

                                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                                        <div class="col-12">
                                                            <div class="form-check form-switch pt-2">
                                                                <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    name="is_featured"
                                                                    value="1"
                                                                    id="is_featured"
                                                                    <?= $is_featured_checked ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="is_featured">Featured post</label>
                                                            </div>
                                                        </div>
                                                    <?php endif ?>

                                                    <div class="col-md-6">
                                                        <label for="thumbnail-input"
                                                            class="form-label border h-28 d-flex justify-content-center align-items-center flex-column gap-1 bg-body-tertiary rounded-2 cursor-pointer text-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24"
                                                                class="eva eva-cloud-upload-outline">
                                                                <g data-name="Layer 2">
                                                                    <g data-name="cloud-upload">
                                                                        <rect width="24" height="24" opacity="0"></rect>
                                                                        <path
                                                                            d="M12.71 11.29a1 1 0 0 0-1.4 0l-3 2.9a1 1 0 1 0 1.38 1.44L11 14.36V20a1 1 0 0 0 2 0v-5.59l1.29 1.3a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42z">
                                                                        </path>
                                                                        <path
                                                                            d="M17.67 7A6 6 0 0 0 6.33 7a5 5 0 0 0-3.08 8.27A1 1 0 1 0 4.75 14 3 3 0 0 1 7 9h.1a1 1 0 0 0 1-.8 4 4 0 0 1 7.84 0 1 1 0 0 0 1 .8H17a3 3 0 0 1 2.25 5 1 1 0 0 0 .09 1.42 1 1 0 0 0 .66.25 1 1 0 0 0 .75-.34A5 5 0 0 0 17.67 7z">
                                                                        </path>
                                                                    </g>
                                                                </g>
                                                            </svg>
                                                            <h6 id="thumbnail-upload-title">Upload thumbnail image</h6>
                                                            <p class="mb-0 text-muted small px-3" id="thumbnail-upload-hint">Required for image posts. For video posts this becomes the poster image shown before play.</p>
                                                            <input
                                                                type="file"
                                                                name="thumbnail"
                                                                accept="image/png,image/jpeg"
                                                                class="d-none"
                                                                id="thumbnail-input">
                                                        </label>
                                                    </div>

                                                    <div class="col-md-6" id="video-upload-group" <?= $media_type === 'video' && $video_source === 'upload' ? '' : 'hidden' ?>>
                                                        <label for="video-file-input"
                                                            class="form-label border h-28 d-flex justify-content-center align-items-center flex-column gap-1 bg-body-tertiary rounded-2 cursor-pointer text-center">
                                                            <i class="mdi mdi-video-outline fs-2"></i>
                                                            <h6>Upload your video file</h6>
                                                            <p class="mb-0 text-muted small px-3">MP4, WebM, or OGG up to 50MB.</p>
                                                            <input
                                                                type="file"
                                                                name="video_file"
                                                                accept="video/mp4,video/webm,video/ogg"
                                                                class="d-none"
                                                                id="video-file-input">
                                                        </label>
                                                    </div>
                                                </div>

                                                <br>

                                                <div class="md-4">
                                                    <button type="submit" name="submit"
                                                        class="btn btn-secondary">Add Post</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- container-fluid -->
            </div><!-- End Page-content -->

            <!-- Begin Footer -->
                     <?php include 'includes/footer.php' ?> 

        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->

    <?php if (isset($_SESSION['add-post'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: " Failed",
                    text: "<?= $_SESSION['add-post'] ?>",
                    confirmButtonColor: "#d33"
                });

            });

        </script>

        <?php unset($_SESSION['add-post']); ?>

    <?php endif; ?>

     
   
    <script src="account/assets/js/sweetalert.js"></script>
    <script src="<?= htmlspecialchars($tinyMceBaseUrl, ENT_QUOTES, 'UTF-8') ?>/tinymce.min.js"></script>



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
    <!-- Bootstrap datepicker -->
    <script src="account/assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <!-- select2 -->
    <script src="account/assets/libs/select2/js/select2.min.js"></script>

    <!-- Init js -->
    <script src="account/assets/js/apps/add-patient-init.js"></script>

    <!-- App js -->
    <script src="account/assets/js/app.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof tinymce === "undefined") {
                return;
            }

            tinymce.init({
                selector: "#post-body-editor",
                base_url: <?= json_encode($tinyMceBaseUrl) ?>,
                suffix: ".min",
                license_key: "gpl",
                height: 380,
                min_height: 320,
                menubar: "edit insert format tools",
                branding: false,
                promotion: false,
                plugins: "advlist autolink lists link preview code wordcount quickbars",
                toolbar: "undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | link blockquote | removeformat code preview",
                block_formats: "Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4",
                toolbar_sticky: true,
                statusbar: true,
                quickbars_selection_toolbar: "bold italic underline | quicklink blockquote bullist numlist",
                link_default_target: "_blank",
                link_assume_external_targets: true,
                skin_url: <?= json_encode($tinyMceBaseUrl . '/skins/ui/oxide') ?>,
                content_css: <?= json_encode($tinyMceBaseUrl . '/skins/content/default/content.min.css') ?>,
                content_style: "body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; } body.mce-content-body { padding: 12px; } a { color: #0d6efd; text-decoration: underline; } ul, ol { padding-left: 1.5rem; margin: 0 0 1rem; } blockquote { border-left: 4px solid #d0d7de; margin: 1rem 0; padding-left: 1rem; color: #495057; } pre { background: #f8f9fa; border-radius: 8px; padding: 12px; }",
                relative_urls: false,
                convert_urls: false
            });

            const form = document.getElementById("create-post-form");
            if (form) {
                form.addEventListener("submit", function () {
                    tinymce.triggerSave();
                });
            }

            const mediaTypeInput = document.getElementById("media_type");
            const videoSourceInput = document.getElementById("video_source");
            const videoSourceGroup = document.getElementById("video-source-group");
            const videoLinkGroup = document.getElementById("video-link-group");
            const videoUploadGroup = document.getElementById("video-upload-group");
            const videoLinkInput = document.getElementById("video_link");
            const videoFileInput = document.getElementById("video-file-input");
            const thumbnailInput = document.getElementById("thumbnail-input");
            const thumbnailTitle = document.getElementById("thumbnail-upload-title");
            const thumbnailHint = document.getElementById("thumbnail-upload-hint");

            function syncMediaForm() {
                const isVideo = mediaTypeInput && mediaTypeInput.value === "video";
                const source = isVideo && videoSourceInput ? videoSourceInput.value : "";

                if (videoSourceGroup) {
                    videoSourceGroup.hidden = !isVideo;
                }

                if (videoLinkGroup) {
                    videoLinkGroup.hidden = !isVideo || source !== "embed";
                }

                if (videoUploadGroup) {
                    videoUploadGroup.hidden = !isVideo || source !== "upload";
                }

                if (videoLinkInput) {
                    videoLinkInput.required = isVideo && source === "embed";
                }

                if (videoFileInput) {
                    videoFileInput.required = isVideo && source === "upload";
                }

                if (thumbnailInput) {
                    thumbnailInput.required = !isVideo;
                }

                if (thumbnailTitle) {
                    thumbnailTitle.textContent = isVideo ? "Upload poster image" : "Upload thumbnail image";
                }

                if (thumbnailHint) {
                    thumbnailHint.textContent = isVideo
                        ? "Optional, but recommended. This poster image appears before the video starts."
                        : "Required for image posts. JPG and PNG only.";
                }
            }

            mediaTypeInput?.addEventListener("change", syncMediaForm);
            videoSourceInput?.addEventListener("change", syncMediaForm);
            syncMediaForm();
        });
    </script>

</body>



</html>
