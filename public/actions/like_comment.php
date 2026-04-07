<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Login required']);
    exit;
}

$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($comment_id <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid comment']);
    exit;
}

// Check if already liked
$stmt = $connection->prepare("SELECT id FROM comment_likes WHERE comment_id=? AND user_id=?");
$stmt->bind_param("ii",$comment_id,$user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows>0) {
    $del = $connection->prepare("DELETE FROM comment_likes WHERE comment_id=? AND user_id=?");
    $del->bind_param("ii",$comment_id,$user_id);
    $del->execute();
    $liked = false;
} else {
    $ins = $connection->prepare("INSERT INTO comment_likes (comment_id,user_id) VALUES (?,?)");
    $ins->bind_param("ii",$comment_id,$user_id);
    $ins->execute();
    $liked = true;
}

// Count total likes
$stmt2 = $connection->prepare("SELECT COUNT(*) as total FROM comment_likes WHERE comment_id=?");
$stmt2->bind_param("i",$comment_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
$count = $res2->fetch_assoc()['total'];

echo json_encode(['status'=>'success','liked'=>$liked,'count'=>$count]);
?>