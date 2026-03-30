<?php


function getCount($table, $conn) {
    $query = "SELECT COUNT(*) AS total FROM $table";
    $result = mysqli_query($conn, $query);

    if(!$result) {
        die("Query Failed: " . mysqli_error($conn));
    }

    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}