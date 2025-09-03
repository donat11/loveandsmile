<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_id']) && isset($_POST['video_id'])) {
    $reply_id = $_POST['reply_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if the reply belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM comment_replies WHERE id = ? AND user_id = ?");
    $stmt->execute([$reply_id, $user_id]);
    $reply = $stmt->fetch();
    
    if ($reply) {
        // Delete the reply
        $pdo->prepare("DELETE FROM comment_replies WHERE id = ?")->execute([$reply_id]);
    }
    
    header("Location: watch.php?id=" . $_POST['video_id']);
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>