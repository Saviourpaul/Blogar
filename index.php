<?php

$page = $_GET['page'] ?? 'home';

switch ($page) {

    case 'home':
        include  'public/home.php';
        break;

    case 'about':
        include 'public/AboutUs.php';
        break;
    
    case 'post':
        include 'public/post-details.php';
        break;
    
    case 'category':
        include 'public/category-posts.php';
        break;

    case 'author':
        include 'public/author.php';
        break;

    case 'contact':
        include 'public/contact.php';
        break;

   




    default:
        include 'public/404.php';
        break;
}

