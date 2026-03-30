 <div class="sidebar-left">
    
        <div class="sidebar-slide h-100" data-simplebar>
    
            <!--- Sidebar-menu -->
            <div id="sidebar-menu">
                <!-- Left Menu Start -->
                <ul class="left-menu list-unstyled" id="side-menu">
                    <li>
                        <a href="dashboard.php" class="">
                            <i data-eva="compass-outline"></i>
                            <span>Dashboard</span>
                        </a>
                        
                    </li>                   
                    <li>
                        <a href="CreatePost.php">
                            <i class="fab fa-superpowers"></i>
                            <span>Create Post</span>
                        </a>
                    </li>
                 <i class="fa-solid fa-circle-plus"></i>
                    <li>
                        <a href="managePost.php" class="">
                            <i class="fas fa-money-check"></i>
                            <span>Manage Post </span>
                        </a>
                        
                    </li>
                     <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <li>
                        <a href="AddUser.php" class="">
                            <i class="fas fa-user-plus"></i>
                            <span>Add User</span>
                        </a>
                        
                    </li>
                    <li>
                        <a href="ManageUser.php" class="">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                        
                    </li>
                    <li>
                        <a href="AddCategory.php" class="">
                            <i data-eva="credit-card-outline"></i>
                            <span>Add Category</span>
                        </a>
                       
                    </li>
                    <li>
                        <a href="ManageCategory.php" class="">
                            <i data-eva="cube-outline"></i>
                            <span>Manage Categories</span>
                        </a>
                    </li>
                     <li>
                        <a href="subscription.php" class="">
                            <i class=" fas fa-sync-alt"></i>
                            <span>Subscription</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="">
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