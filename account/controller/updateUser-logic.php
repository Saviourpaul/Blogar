<?php
require '../config/database.php'; 

if (isset($_POST['submit'])) {


    
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $birthday = $_POST['birthday'];
    $phone = htmlspecialchars(trim($_POST['phone']));
    $bio = htmlspecialchars(trim($_POST['bio']));
    $address1 = htmlspecialchars(trim($_POST['address1']));
    $address2 = htmlspecialchars(trim($_POST['address2']));
    $zip_code = htmlspecialchars(trim($_POST['zip_code']));
    $country = htmlspecialchars(trim($_POST['country']));
    $gender = $_POST['gender']; 

    $sql = "UPDATE users SET 
            firstname = ?, lastname = ?, username = ?, email = ?, 
            birthday = ?, phone = ?, bio = ?, address1 = ?, 
            address2 = ?, zip_code = ?, country = ?, gender = ?";

    // Define the parameter types (12 strings)
    $types = "ssssssssssss"; 
    
    // Put all base parameters into an array
    $params = [
        $firstname, $lastname, $username, $email, 
        $birthday, $phone, $bio, $address1, 
        $address2, $zip_code, $country, $gender
    ];

    if (isset($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/'; 
        $file_tmp = $_FILES['avatar']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['avatar']['name']); 
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $sql .= ", avatar = ?";
            $types .= "s"; 
            $params[] = $file_name;
        }
    }

    $current_password = $_POST['current_password'];
    $create_password = $_POST['create_password'];
    $confirm_password = $_POST['confirm_password'];

   // Only run this if the user actually filled out the password fields
if (!empty($current_password) || !empty($create_password) || !empty($confirm_password)) {

    // 1. Check if any fields are missing
    if (empty($current_password) || empty($create_password) || empty($confirm_password)) {
        $_SESSION['update-user'] = "Please fill in all password fields to change your password.";
        header("Location: ../UpdateUser.php");
        exit;
    }

    // 2. Check if New Password and Confirm Password match
    if ($create_password !== $confirm_password) {
        $_SESSION['update-user'] = "New passwords do not match.";
        header("Location: ../UpdateUser.php");
        exit;
    }

    // 3. Check Password Strength (Letters and Numbers)
    if (!preg_match('/[A-Za-z]/', $create_password) || !preg_match('/[0-9]/', $create_password)) {
        $_SESSION['update-user'] = "New password must include both letters and numbers.";
        header("Location: ../UpdateUser.php");
        exit;
    }

    // 4. Verify Current Password against Database
    $stmt_pass = $connection->prepare("SELECT password FROM users WHERE id = ?");
    $stmt_pass->bind_param("i", $id);
    $stmt_pass->execute();
    $stmt_pass->bind_result($db_password);
    $stmt_pass->fetch();
    $stmt_pass->close();

    if (password_verify($current_password, $db_password)) {
        // 5. Success: Hash new password and add to the UPDATE query
        $hashed_password = password_hash($create_password, PASSWORD_DEFAULT);
        
        $sql .= ", password = ?";
        $types .= "s";
        $params[] = $hashed_password;
    } else {
        // Current password entered does not match DB
        $_SESSION['update-user'] = "Current password is incorrect.";
        header("Location: ../UpdateUser.php");
        exit;
    }
}
   

    $sql .= " WHERE id = ?";
    $types .= "i"; 
    $params[] = $id;

    $stmt = $connection->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['update-success'] = "Profile updated successfully!";
        } else {
            $_SESSION['update-user'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['update-user'] = "Statement preparation failed: " . $connection->error;
    }

    header("Location: ../UpdateUser.php");
    exit;

} else {
    header("Location: ../UpdateUser.php");
    exit;
}
?>