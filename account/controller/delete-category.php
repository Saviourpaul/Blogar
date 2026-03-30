<?php
require '../config/database.php';


if(isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $update_query = "UPDATE posts SET category_id=17 WHERE category_id=$id";
    $update_result = mysqli_query($connection, $update_query);

    if(!mysqli_errno($connection)) {

      $query = "DELETE FROM categories WHERE id=$id LIMIT 1";
      $result = mysqli_query($connection, $query);
      $_SESSION['delete-category-success'] = "$title Category deleted successfully";
    }
}

header("Location: ../ManageCategory.php");
die();
