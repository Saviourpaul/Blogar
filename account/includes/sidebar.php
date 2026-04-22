<?php
$postSettingResult = mysqli_query($connection, "SELECT enable_post FROM settings WHERE id = 1 LIMIT 1");
$is_create_post_enabled = true;

if ($postSettingResult && mysqli_num_rows($postSettingResult) > 0) {
    $postSetting = mysqli_fetch_assoc($postSettingResult);
    $is_create_post_enabled = !isset($postSetting['enable_post']) || (int) $postSetting['enable_post'] === 1;
}
?>

 <div class="sidebar-left">
    
        <div class="sidebar-slide h-100" data-simplebar>
    
            <!--- Sidebar-menu -->
            <div id="sidebar-menu">
                <!-- Left Menu Start -->
                <ul class="left-menu list-unstyled" id="side-menu">
                    <li>
                        <a href="dashboard" class="">
                            <i data-eva="compass-outline"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="post">
                            <i class="fab fas fa-lightbulb"></i>
                            <span>Ideas</span>
                        </a>
                    </li>                  
                    <?php if (empty($_SESSION['is_admin'])): ?>
                        <li>
                            <a href="onboarding" class="">
                                <i class="mdi mdi-tune-variant"></i>
                                <span>Preferences</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <?php if ($is_create_post_enabled): ?>
                            <a href="CreatePost">
                                <i class="fab fa-superpowers"></i>
                                <span>Create Post</span>
                            </a>
                        <?php else: ?>
                            <a href="javascript:void(0)" class="disabled" aria-disabled="true" title="Create post is disabled in settings">
                                <i class="fab fa-superpowers"></i>
                                <span>Create Post Disabled</span>
                            </a>
                        <?php endif; ?>
                    </li>
                 <i class="fa-solid fa-circle-plus"></i>
                    <li>
                        <a href="managePost" class="">
                            <i class="fas fa-money-check"></i>
                            <span>Manage Post </span>
                        </a>
                        
                    </li>
                     <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li>
                        <a href="pendingPost">
                            <i class="fab fa-superpowers"></i>
                            <span>Pending Post</span>
                        </a>
                    </li>
                    <li>
                        <a href="AddUser" class="">
                            <i class="fas fa-user-plus"></i>
                            <span>Add User</span>
                        </a>
                        
                    </li>
                    <li>
                        <a href="ManageUser" class="">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                        
                    </li>
                    <li>
                        <a href="addCategory" class="">
                            <i data-eva="credit-card-outline"></i>
                            <span>Add Category</span>
                        </a>
                       
                    </li>
                    <li>
                        <a href="manageCategory" class="">
                            <i data-eva="cube-outline"></i>
                            <span>Manage Categories</span>
                        </a>
                    </li>
                     <li>
                        <a href="subscription" class="">
                            <i class=" fas fa-sync-alt"></i>
                            <span>Subscription</span>
                        </a>
                    </li>
                    <li>
                        <a href="setting" class="">
                            <i data-eva="settings-2-outline"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                     <?php endif; ?>
                        
                   
                </ul>
            </div>
            <!-- Sidebar -->
        </div>
    </div>
