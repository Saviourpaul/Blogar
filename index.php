<?php
session_start();
require 'config/database.php';
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/Blogar'; 
$route = str_replace($base_path, '', $request_uri);

if ($route === '' || $route === '/index.php') {
    $route = '/';
} elseif ($route !== '/') {
    $route = rtrim($route, '/');
}

$routes = [
    // Public Routes
    '/' => 'home.php',
    '/home' => 'home.php',
    '/about' => 'about.php',
    '/features' => 'features.php',
    '/faqs' => 'faqs.php',
    '/pricing' => 'pricing.php',
    '/contact' => 'contact.php',
    '/terms' => 'terms.php',
    '/privacy' => 'privacy.php',
    '/signup' => 'auth/signup.php',
    '/signin' => 'auth/signin.php',
    '/forgot-password' => 'auth/forgot-password.php',
    '/reset-password' => 'auth/reset-password.php',
    '/onboarding' => 'account/onboarding.php',
   

    // Account Routes
    '/dashboard' => 'account/dashboard.php',
    '/addCategory' => 'account/addCategory.php',
    '/AddUser' => 'account/AddUser.php',
    '/CreatePost' => 'account/CreatePost.php',
    '/EditCategory' => 'account/EditCategory.php',
    '/manageCategory' => 'account/manageCategory.php',
    '/managePost' => 'account/managePost.php',
    '/ManageUser' => 'account/ManageUser.php',
    '/postOverview' => 'account/postOverview.php',
    '/post' => 'account/post.php',
    '/notifications' => 'account/notifications.php',
    '/categoryPost' => 'account/categoryPost.php',
    '/UpdatePost' => 'account/UpdatePost.php',
    '/UpdateUser' => 'account/UpdateUser.php',
    '/UserProfile' => 'account/UserProfile.php',
    '/setting' => 'account/setting.php',

    //logic routes
     '/signup-logic' => 'auth/signup-logic.php',
    '/signin-logic' => 'auth/signin-logic.php',
    '/forgot-password-logic' => 'auth/forgot-password-logic.php',
    '/reset-password-logic' => 'auth/reset-password-logic.php',
    '/logout' => 'auth/logout.php',
    '/onboarding-logic' => 'account/controller/onboarding-logic.php',
    '/add-category-logic' => 'account/controller/add-category-logic.php',
    '/edit-category-logic' => 'account/controller/edit-category-logic.php',
    '/add-post-logic' => 'account/controller/add-post-logic.php',
    '/add-user-logic' => 'account/controller/add-user-logic.php',
    '/delete-category' => 'account/controller/delete-category.php',
    '/delete-post' => 'account/controller/delete-post.php',
    '/delete-user' => 'account/controller/delete-user.php',
    '/update-category-logic' => 'account/controller/edit-category-logic.php',
    '/update-post-logic' => 'account/controller/UpdatePost-logic.php',
    '/update-user-logic' => 'account/controller/updateUser-logic.php',
    '/process_interaction' => 'account/controller/process_interaction.php',
    '/comments' => 'account/actions/comments.php',
    '/follow-toggle' => 'account/actions/follow-toggle.php',
    '/notifications-read' => 'account/actions/notifications-read.php',

   
];

if (array_key_exists($route, $routes)) {
    $file_to_load = $routes[$route];
    if (strpos($file_to_load, 'account/') === 0) {
        if (!isset($_SESSION['user-id'])) {
            header("Location: $base_path/");
            exit;
        }
    }

    require $file_to_load;

} else {
    http_response_code(404);
    require '404.php';
}
?>
