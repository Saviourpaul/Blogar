<?php $pageTitle = 'all Post';
require 'includes/header.php';


if (isset($_GET['id'])) {




    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM posts WHERE id=$id";
    $result = mysqli_query($connection, $query);

    $post = mysqli_fetch_assoc($result);
    $author_id = $post['author_id'];
    $author_query = "SELECT * FROM users WHERE id=$author_id";
    $author_result = mysqli_query($connection, $author_query);
    $author = mysqli_fetch_assoc($author_result);
    $category_id = $post['category_id'];
    $category_query = "SELECT * FROM categories WHERE id=$category_id";
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);



    // Generate CSRF token if it doesn’t exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];



    $post_id = (int) $_GET['id'];

    $stmt = $connection->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $comments = $res->fetch_all(MYSQLI_ASSOC);

    // Build tree
    $commentTree = [];
    foreach ($comments as $c) {
        $commentTree[$c['parent_id']][] = $c;
    }
}


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
        <div class="sidebar-left horizontal-sidebar">


        </div>
        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Post Details</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="pt-3">
                                            <div class="row justify-content-center">
                                                <div class="col-xl-8">
                                                    <div>
                                                        <div class="text-center">
                                                            <div class="mb-4">
                                                                <a href="javascript: void(0);" class="badge bg-light font-size-12">
                                                                    <i class="bx bx-purchase-tag-alt align-middle text-muted me-1"></i><?= htmlspecialchars($category['title']) ?>
                                                                </a>
                                                            </div>
                                                            <h4><?= htmlspecialchars($post['title']) ?></h4>
                                                        </div>

                                                        <hr>
                                                        
                                                        <hr>

                                                        <div class="my-5">
                                                            <img src="uploads/<?= htmlspecialchars($post['thumbnail']) ?>" alt="" class="img-thumbnail mx-auto d-block">
                                                        </div>

                                                        <hr>
                                                           <div class="d-flex flex-wrap gap-3 mb-6">
                                                <div class="avatar avatar-md avatar-circle overflow-hidden">
                                                    <img src="uploads/<?= htmlspecialchars($author['avatar']) ?>" alt="Avatar Image"
                                                        class="avatar-xs">
                                                </div>
                                                <div class="mt-1">
                                                    <a href="#!" class="text-body fw-semibold mb-1 d-block"><?= htmlspecialchars("{$author['firstname']} {$author['lastname']}") ?><span class="text-muted fw-normal fs-12"> - <?= getRelativeTime($post['created_at']) ?></span></a>
                                                    <div class="d-flex gap-5">
                                                        <ul class="list-inline border-bottom pb-2">                

                                                            <li class="list-inline-item me-3" onclick="handleInteraction(<?= $post['id'] ?>, 'like')" style="cursor:pointer"> 
                                                                <i class="mdi mdi-thumb-up-outline text-success me-1"></i>
                                                                <span id="likes-<?= $post['id'] ?>"><?= $post['likes'] ?? 0 ?></span>
                                                            </li>

                                                            <li class="list-inline-item me-3" onclick="handleInteraction(<?= $post['id'] ?>, 'dislike')" style="cursor:pointer">
                                                                <i class="mdi mdi-thumb-down-outline text-danger me-1"></i>
                                                                <span id="dislikes-<?= $post['id'] ?>"><?= $post['dislikes'] ?? 0 ?></span>
                                                            </li>                                                          
                                                            <li class="list-inline-item" onclick="copyShareLink(<?= $post['id'] ?>)" style="cursor:pointer">
                                                                <i class="mdi mdi-share text-primary me-1"></i>
                                                                <span id="shares-<?= $post['id'] ?>"><?= $post['share_count'] ?? 0 ?> shares </span>
                                                            </li>
                                                         </ul>
                                                         <a href="javascript: void(0);" class="text-muted">
                                                                            <i class="bx bx-comment-dots align-middle text-muted me-1"></i><?= count($comments); ?>  Comments
                                                              </a>
                                                              
                                                        
                                                    </div>
                                                </div>

                                                        <div class="mt-4">
                                                            <div class="text-muted font-size-14">
                                                                <p><?= htmlspecialchars($post['body']) ?></p>
                                                            
                                                 

                                                            </div>

                                                            <hr>

                                                            <div class="mt-5">
                                                                <h5 class="font-size-15"><i class="bx bx-message-dots text-muted align-middle me-1"></i> Comments :</h5>
                                                                <p id="replying-to-text" style="display:none; color: #ff3a59; cursor:pointer;"
                                                                        onclick="cancelReply()">
                                                                        Cancel Reply ✖
                                                                    </p>
                                                                
                                                              <?php
                                                        function renderComments($tree, $parent_id = 0, $level = 0)
                                                        {
                                                            if (!isset($tree[$parent_id])) {
                                                                return;
                                                            }

                                                            $index = 0; 

                                                            foreach ($tree[$parent_id] as $c) {
                                                                $id = $c['id'];
                                                                $name = htmlspecialchars($c['name']);
                                                                $message = nl2br(htmlspecialchars($c['message']));
                                                                $date = getRelativeTime($c['created_at']);

                                                                if ($level === 0) {
                                                                    $containerClass = ($index === 0) ? 'd-flex py-3' : 'd-flex py-3 border-top';
                                                                } else {
                                                                    $containerClass = 'd-flex pt-3';
                                                                }

                                                                $avatarHtml = isset($c['avatar']) && !empty($c['avatar']) 
                                                                    ? '<img src="' . htmlspecialchars($c['avatar']) . '" alt="" class="img-fluid d-block rounded-circle">'
                                                                    : '<div class="avatar-title rounded-circle bg-light text-primary"><i class="bx bxs-user"></i></div>';

                                                                echo '<div class="' . $containerClass . '">';
                                                                echo '  <div class="flex-shrink-0 me-3">';
                                                                echo '      <div class="avatar-xs">';
                                                                echo            $avatarHtml;
                                                                echo '      </div>';
                                                                echo '  </div>';
                                                                echo '  <div class="flex-grow-1">';
                                                                echo '      <h5 class="font-size-14 mb-1">' . $name . ' <small class="text-muted float-end">' . $date . '</small></h5>';
                                                                echo '      <p class="text-muted">' . $message . '</p>';
                                                                echo '      <div>';
                                                                
                                                                echo '          <a href="javascript: void(0);" class="text-success" onclick="setReplyId(' . $id . ', \'' . addslashes($name) . '\')">';
                                                                echo '              <i class="mdi mdi-reply"></i> Reply';
                                                                echo '          </a>';
                                                                echo '      </div>';

                                                                renderComments($tree, $id, $level + 1);

                                                                echo '  </div>'; 
                                                                echo '</div>'; 

                                                                $index++;
                                                            }
                                                        }
                                                        ?>
                                                            </div>
                                                             <div class="axil-comment-area mt--40">
                                                                <ul class="comment-list">
                                                                    <?php renderComments($commentTree); ?>
                                                                </ul>
                                                            </div>
                                                            <div class="mt-4">
                                                                <h5 class="font-size-16 mb-3">Leave a Message</h5>
                                                                <form action="actions/comments.php" method="post">
                                                                    <div class="row">
                                                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                                    <input type="hidden" name="parent_id" id="parent_id" value="0">
                                                                    
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="commentname-input" class="form-label">Name</label>
                                                                                <input type="text" class="form-control" name="name" id="commentname-input" placeholder="Enter name" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="commentemail-input" class="form-label">Email</label>
                                                                                <input type="email" class="form-control" name="email" id="commentemail-input" placeholder="Enter email" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                
                                                                    <div class="mb-3">
                                                                        <label for="commentmessage-input" class="form-label">Message</label>
                                                                        <textarea class="form-control" name="message" id="commentmessage-input" placeholder="Your message..." rows="3" required></textarea>
                                                                    </div>
                
                                                                    <div class="text-end">
                                                                        <button type="submit" name="submit" class="btn btn-success w-sm">Submit</button>
                                                                    </div>
                                                                </form>

                                                                
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div>
                </div><!-- container-fluid -->
            </div><!-- End Page-content -->

            <!-- Begin Footer -->
                    <?php include 'includes/footer.php' ?> 

            <!-- END scroll top -->
        </div><!-- end main content-->

    </div><!-- END layout-wrapper -->



 <?php if (isset($_SESSION['comments-success'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "success",
                    title: " success",
                    text: "<?= $_SESSION['comments-success'] ?>",
                    confirmButtonColor: "#3085d6"
                });

            });

        </script>

        <?php unset($_SESSION['comments-success']); ?>
        
       
    <?php endif;   ?>
     <?php if (isset($_SESSION['comments-error'])): ?>

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "<?= $_SESSION['comments-error'] ?>",
                    confirmButtonColor: "red"
                });

            });

        </script>

        <?php unset($_SESSION['comments-error']); ?>
        
       
    <?php endif;   ?>


<script>
async function handleInteraction(postId, action) {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('action', action);

    try {
        const response = await fetch('controller/process_interaction.php', { method: 'POST', body: formData });
        const res = await response.json();
        
        if (res.status === 'success') {
            document.getElementById(`likes-${postId}`).innerText = res.data.likes;
            document.getElementById(`dislikes-${postId}`).innerText = res.data.dislikes;
            document.getElementById(`shares-${postId}`).innerText = res.data.shares;
        } else {
            alert(res.message);
        }
    } catch (err) { console.error("Interaction failed", err); }
}

function copyShareLink(postId) {
    const url = window.location.origin + '/post.php?id=' + postId ;
    navigator.clipboard.writeText(url).then(() => {
        alert("Link copied to clipboard!");
        handleInteraction(postId, 'share'); // Track the share
    });
}
</script>



   <script>
    function setReplyId(id, name) {
        document.getElementById('parent_id').value = id;
        const text = document.getElementById('replying-to-text');
        text.style.display = 'block';
        text.innerText = "Replying to " + name + " (Cancel ✖)";
    }

    function cancelReply() {
        document.getElementById('parent_id').value = 0;
        const text = document.getElementById('replying-to-text');
        text.style.display = 'none';
    }
     </script>
                            

    <!-- Switcher -->
    
    <!-- Bootstrap bundle js -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Layouts main js -->
    <script src="assets/libs/jquery/jquery.min.js"></script>

    <!-- Metimenu js -->
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>

    <!-- simplebar js -->
    <script src="assets/libs/simplebar/simplebar.min.js"></script>

    <script src="assets/libs/eva-icons/eva.min.js"></script>

    <!-- Scroll Top init -->
    <script src="assets/js/scroll-top.init.js"></script>
    <!-- touchspin js -->
    <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>

    <!-- slick-carousel js -->
    <script src="assets/libs/slick-carousel/slick/slick.min.js"></script>

    <!-- Product overview init js -->
    <script src="assets/js/apps/product-overview.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>


</html>