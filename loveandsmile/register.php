<?php
session_start();
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    
    // Validate phone number
    if (strlen($phone) != 10 || substr($phone, 0, 2) != '07') {
        $error = "Phone number must be 10 digits starting with 07";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (phone, password, name, is_admin) VALUES (?, ?, ?, 0)");
            $stmt->execute([$phone, $password, $name]);
            
            $_SESSION['success'] = "Registration successful! Please wait for admin approval.";
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Phone number already registered";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Love and Smile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-6">
            <img src="logo.png" alt="Love and Smile Logo" class="h-16 w-16 mx-auto">
            <h1 class="text-2xl font-bold mt-2">Love and Smile</h1>
            <p class="text-gray-600">Create your account</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="name">Full Name</label>
                <input type="text" id="name" name="name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" pattern="07[0-9]{8}" placeholder="07........" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                <p class="text-xs text-gray-500 mt-1">Must be 10 digits starting with 07</p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 mb-2" for="password">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>

            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">Register</button>
        </form>

        <p class="text-center mt-4">
            Already have an account? <a href="login.php" class="text-red-600 hover:underline">Login here</a>
        </p>
    </div>
</body>
</html>