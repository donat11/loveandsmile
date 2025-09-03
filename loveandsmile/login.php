<?php
session_start();
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['approved'] == 1) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['approved'] = $user['approved'];
            $_SESSION['is_admin'] = $user['is_admin']; // Add this line
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Your account is pending approval from the administrator.";
        }
    } else {
        $error = "Invalid phone number or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Love and Smile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-6">
            <img src="logo.png" alt="Love and Smile Logo" class="h-16 w-16 mx-auto">
            <h1 class="text-2xl font-bold mt-2">Love and Smile</h1>
            <p class="text-gray-600">Login to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" pattern="07[0-9]{8}" placeholder="07........" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 mb-2" for="password">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>

            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">Login</button>
        </form>

        <p class="text-center mt-4">
            Don't have an account? <a href="register.php" class="text-red-600 hover:underline">Register here</a>
        </p>
    </div>
</body>
</html>