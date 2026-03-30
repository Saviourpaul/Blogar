<?php
session_start();
require '../config/database.php';

if (isset($_POST['submit'])) {

    $username_email = filter_var($_POST['username_email'], FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$username_email || !$password) {

        $_SESSION['alert'] = [
            "type" => "error",
            "message" => "Username/Email and Password are required"
        ];

        header("Location: signin.php");
        exit;
    }

    $stmt = $connection->prepare("
        SELECT id, password, is_admin, login_attempts, last_attempt 
        FROM users
        WHERE username = ? OR email = ?
        LIMIT 1
    ");

    $stmt->bind_param("ss", $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        $attempts = $user['login_attempts'];
        $last_attempt = strtotime($user['last_attempt']);

        /* LOCKOUT CHECK */

        if ($attempts >= 5) {

            $wait_time = 300; // 5 minutes
            $time_diff = time() - $last_attempt;

            if ($time_diff < $wait_time) {

                $_SESSION['alert'] = [
                    "type" => "error",
                    "message" => "Too many failed attempts. Try again in 5 minutes."
                ];

                header("Location: signin.php");
                exit;
            }
        }

        /* PASSWORD CHECK */

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user-id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];

            /* RESET ATTEMPTS */

            $update = $connection->prepare("
                UPDATE users 
                SET login_attempts = 0,
                    last_login = NOW()
                WHERE id = ?
            ");

            $update->bind_param("i", $user['id']);
            $update->execute();

            $_SESSION['alert'] = [
                "type" => "success",
                "message" => "Login Successful!"
            ];

            header("Location: ../dashboard.php");
            exit;

        } else {

            /* INCREASE ATTEMPTS */

            $update = $connection->prepare("
                UPDATE users 
                SET login_attempts = login_attempts + 1,
                    last_attempt = NOW()
                WHERE id = ?
            ");

            $update->bind_param("i", $user['id']);
            $update->execute();

            $_SESSION['alert'] = [
                "type" => "error",
                "message" => "Invalid login credentials"
            ];

            header("Location: signin.php");
            exit;
        }

    } else {

        $_SESSION['alert'] = [
            "type" => "error",
            "message" => "Invalid login credentials"
        ];

        header("Location: signin.php");
        exit;
    }

}