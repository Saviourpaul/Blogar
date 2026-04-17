<?php

if (isset($_POST['submit'])) {

    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$title) {

        $_SESSION['edit-category'] = "Invalid form input";

    } else {

        $query = "UPDATE categories 
SET title='$title', description='$description'
WHERE id=$id  LIMIT 1";

        $result = mysqli_query($connection, $query);

        if (mysqli_errno($connection)) {

            $_SESSION['edit-category'] = "Couldn't update category";

        } else if (mysqli_affected_rows($connection) == 0) {

            $_SESSION['edit-category'] = "No changes were made";

        } else {

            $_SESSION['edit-category-success'] = "$title category updated successfully";

        }

    }
}

header("Location: manageCategory");
exit();

