<?php
session_start();
require_once 'includes/config.php';

// Make the first user an admin (if exists)
$stmt = $pdo->query("SELECT id FROM users ORDER BY id LIMIT 1");
$user = $stmt->fetch();

if ($user) {
    $pdo->prepare("UPDATE users SET is_admin = 1, approved = 1 WHERE id = ?")->execute([$user['id']]);
    echo "User ID " . $user['id'] . " has been set as admin.<br>";
    
    // Show login info
    $stmt = $pdo->prepare("SELECT phone, name FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $userInfo = $stmt->fetch();
    
    echo "Login with phone: " . $userInfo['phone'] . " and your password.<br>";
    echo '<a href="login.php">Login here</a>';
} else {
    echo "No users found in database. <a href='register.php'>Register first</a>.";
}

// Delete this file after use for security
echo "<br><br><strong>Important:</strong> Delete this file after use for security reasons.";
?>