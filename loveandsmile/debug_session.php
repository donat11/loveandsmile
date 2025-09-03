<?php
session_start();
require_once 'includes/config.php';

echo "<h2>Session Values</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>User Info from Database</h2>";
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    echo "<pre>";
    print_r($user);
    echo "</pre>";
} else {
    echo "Not logged in.";
}

echo '<p><a href="login.php">Login</a> | <a href="admin/">Admin Panel</a></p>';
?>