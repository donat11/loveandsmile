<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_id']) && isset($_POST['video_id'])) {
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if the comment belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $user_id]);
    $comment = $stmt->fetch();
    
    if ($comment) {
        // Delete the comment
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$comment_id]);
    }
    
    header("Location: watch.php?id=" . $_POST['video_id']);
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>