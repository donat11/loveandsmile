<?php
session_start();
require_once 'includes/config.php';

// Fetch approved videos
$stmt = $pdo->query("SELECT * FROM videos ORDER BY upload_date DESC");
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Love and Smile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .video-card {
            transition: transform 0.3s ease;
        }
        .video-card:hover {
            transform: scale(1.03);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-red-600 text-white p-4 flex justify-between items-center">
        <div class="flex items-center">
            <img src="logo.png" alt="Love and Smile Logo" class="h-8 w-8 mr-2">
            <span class="font-bold text-xl">Love and Smile</span>
        </div>
        
        <div class="flex-1 mx-4">
            <div class="relative">
                <input type="text" placeholder="Search..." class="w-full py-2 px-4 rounded-full text-gray-800 focus:outline-none">
                <button class="absolute right-0 top-0 h-full px-4 text-gray-600">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <div class="flex items-center">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="bg-red-700 hover:bg-red-800 px-4 py-2 rounded-full ml-2">Logout</a>
            <?php else: ?>
                <a href="login.php" class="bg-red-700 hover:bg-red-800 px-4 py-2 rounded-full">Login</a>
                <a href="register.php" class="bg-white text-red-600 hover:bg-gray-200 px-4 py-2 rounded-full ml-2">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-1/5 bg-white p-4 hidden md:block">
            <ul>
                <li class="mb-2"><a href="#" class="flex items-center p-2 hover:bg-gray-200 rounded"><i class="fas fa-home mr-2"></i> Home</a></li>
                <li class="mb-2"><a href="#" class="flex items-center p-2 hover:bg-gray-200 rounded"><i class="fas fa-fire mr-2"></i> Trending</a></li>
                <li class="mb-2"><a href="#" class="flex items-center p-2 hover:bg-gray-200 rounded"><i class="fas fa-heart mr-2"></i> Subscriptions</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-4/5 p-4">
            <h2 class="text-2xl font-bold mb-4">Videos</h2>
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['approved'] == 1): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach($videos as $video): ?>
                        <div class="video-card bg-white rounded-lg shadow-md overflow-hidden">
                            <a href="watch.php?id=<?= $video['id'] ?>">
                                <div class="relative">
                                    <img src="thumbnails/<?= $video['thumbnail'] ?? 'default.jpg' ?>" alt="<?= $video['title'] ?>" class="w-full h-40 object-cover">
                                    <div class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white px-2 py-1 text-xs rounded">
                                        5:20
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h3 class="font-semibold truncate"><?= $video['title'] ?></h3>
                                    <p class="text-gray-600 text-sm"><?= $video['views'] ?> views</p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif(isset($_SESSION['user_id']) && $_SESSION['approved'] == 0): ?>
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <i class="fas fa-clock text-4xl text-yellow-500 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Waiting for Approval</h3>
                    <p>Your account is pending approval from the administrator. Please check back later.</p>
                </div>
            <?php else: ?>
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <i class="fas fa-video text-4xl text-red-600 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Welcome to Love and Smile</h3>
                    <p class="mb-4">Register or login to watch our videos</p>
                    <div class="flex justify-center space-x-4">
                        <a href="register.php" class="bg-red-600 text-white px-4 py-2 rounded-full">Register</a>
                        <a href="login.php" class="bg-white text-red-600 border border-red-600 px-4 py-2 rounded-full">Login</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/250790111301" target="_blank" class="fixed bottom-4 right-4 bg-green-500 text-white p-3 rounded-full shadow-lg">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
</body>
</html>