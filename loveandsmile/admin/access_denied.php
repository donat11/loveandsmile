<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Love and Smile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
        <div class="text-red-500 text-6xl mb-4">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1 class="text-2xl font-bold mb-4">Access Denied</h1>
        <p class="text-gray-600 mb-6">You don't have permission to access the admin panel.</p>
        <a href="../index.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Return to Home</a>
    </div>
</body>
</html>