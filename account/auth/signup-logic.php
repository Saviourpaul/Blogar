<?php
session_start();
require '../config/database.php';



if(isset($_POST['submit'])) {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $create_password = filter_var($_POST['create_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar = $_FILES['avatar'];

    $confirm_password = filter_var($_POST['confirm_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);


    if(!$firstname) {
        $_SESSION['signup'] = "Please enter your First Name";
    } elseif(!$lastname) {
        $_SESSION['signup'] = "Please enter your Last Name";
    } elseif(!$username) {
        $_SESSION['signup'] = "Please enter your Username";
    } elseif(!$email) {
        $_SESSION['signup'] = "Please enter your Email";
    } elseif(strlen($create_password) < 8 || strlen($confirm_password) < 8 ) {
        $_SESSION['signup'] = "Password must be at least 8 characters";
    } elseif($create_password !== $confirm_password) {
        $_SESSION['signup'] = "Passwords do not match";
    } elseif(!$avatar['name']) {
        $_SESSION['signup'] = "Please add  image for your avatar";
    } else {

       if($create_password !== $confirm_password) {
        $_SESSION['signup'] = "Passwords do not match";
       } else {
        //hash password
        $hashed_password = password_hash($create_password, PASSWORD_DEFAULT);

        //check if username or email already exists in database
            $stmt = $connection->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1"
            );

            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['signup'] = "Username or Email already exists";
       
        } else {
            $time = time();
            $avatar_name = $time . $avatar['name'];
            $avatar_tmp_name = $avatar['tmp_name'];
            $avatar_destination_path = '../uploads/' . $avatar_name;

            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extention = explode('.', $avatar_name);
            $extention = end($extention);

            if(in_array($extention, $allowed_files)) {
                if($avatar['size'] < 5000000) {
                    move_uploaded_file($avatar_tmp_name, $avatar_destination_path);

            } else {
                $_SESSION['signup'] = "file size exceeds 5MB limit";
            
            }
            } else {
                $_SESSION['signup'] = "file should be png, jpg or jpeg";
       
            }
    
        }
    }
}

if(isset($_SESSION['signup'])) {
     
    //pass form data to signup page
    $_SESSION['signup-data'] = $_POST;
    header('location: signup.php');
    die();

} else {
    $stmt = $connection->prepare("INSERT INTO users 
        (firstname, lastname, username, email, password, avatar, is_admin) 
        VALUES (?, ?, ?, ?, ?, ?, 0)");

        $stmt->bind_param("ssssss", 
            $firstname, 
            $lastname, 
            $username, 
            $email, 
            $hashed_password, 
            $avatar_name
        );

    $stmt->execute();


    if(!mysqli_errno($connection)){
        $_SESSION['signup-success'] = "Registration Successful. please log in";
        header(header: 'location: signin.php');
        die();

    }
}

} else {
    header('location: signup.php');
    die();
}
           

        
