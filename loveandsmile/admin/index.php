<?php
session_start();
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: access_denied.php");
    exit();
}

// Get pending users
$stmt = $pdo->query("SELECT * FROM users WHERE approved = 0 ORDER BY created_at DESC");
$pendingUsers = $stmt->fetchAll();

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$allUsers = $stmt->fetchAll();

// Get all videos
$stmt = $pdo->query("SELECT v.*, u.name as uploader_name FROM videos v JOIN users u ON v.uploaded_by = u.id ORDER BY v.upload_date DESC");
$videos = $stmt->fetchAll();

// Handle user approval
if (isset($_GET['approve_user'])) {
    $userId = $_GET['approve_user'];
    $pdo->prepare("UPDATE users SET approved = 1 WHERE id = ?")->execute([$userId]);
    header("Location: index.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $userId = $_GET['delete_user'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    header("Location: index.php");
    exit();
}

// Handle video deletion
if (isset($_GET['delete_video'])) {
    $videoId = $_GET['delete_video'];
    
    // Get video filename to delete the file
    $stmt = $pdo->prepare("SELECT filename, thumbnail FROM videos WHERE id = ?");
    $stmt->execute([$videoId]);
    $video = $stmt->fetch();
    
    if ($video) {
        // Delete video file
        if (file_exists("../uploads/" . $video['filename'])) {
            unlink("../uploads/" . $video['filename']);
        }
        
        // Delete thumbnail if exists
        if ($video['thumbnail'] && file_exists("../thumbnails/" . $video['thumbnail'])) {
            unlink("../thumbnails/" . $video['thumbnail']);
        }
        
        // Delete from database
        $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([$videoId]);
    }
    
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Love and Smile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-red-800 text-white h-screen fixed">
            <div class="p-4">
                <img src="../logo.png" alt="Love and Smile Logo" class="h-10 w-10 mx-auto">
                <h1 class="text-xl font-bold text-center mt-2">Love and Smile</h1>
                <p class="text-center text-red-200">Admin Panel</p>
            </div>
            
            <nav class="mt-6">
                <a href="index.php" class="block py-2 px-4 bg-red-700">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="upload.php" class="block py-2 px-4 hover:bg-red-700">
                    <i class="fas fa-upload mr-2"></i> Upload Video
                </a>
                <a href="../index.php" class="block py-2 px-4 hover:bg-red-700">
                    <i class="fas fa-home mr-2"></i> View Site
                </a>
                <a href="../logout.php" class="block py-2 px-4 hover:bg-red-700">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64 flex-1 p-8">
            <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600">Total Users</h2>
                            <p class="text-2xl font-bold"><?= count($allUsers) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-video text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600">Total Videos</h2>
                            <p class="text-2xl font-bold"><?= count($videos) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600">Pending Users</h2>
                            <p class="text-2xl font-bold"><?= count($pendingUsers) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Users -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">Pending User Approvals</h2>
                </div>
                <div class="p-4">
                    <?php if (count($pendingUsers) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($pendingUsers as $user): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= $user['name'] ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= $user['phone'] ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="?approve_user=<?= $user['id'] ?>" class="text-green-600 hover:text-green-900 mr-3">Approve</a>
                                                <a href="?delete_user=<?= $user['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">No pending user approvals.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- All Users -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">All Users</h2>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($allUsers as $user): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= $user['name'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= $user['phone'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $user['approved'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                <?= $user['approved'] ? 'Approved' : 'Pending' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $user['is_admin'] ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= $user['is_admin'] ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!$user['approved']): ?>
                                                <a href="?approve_user=<?= $user['id'] ?>" class="text-green-600 hover:text-green-900 mr-3">Approve</a>
                                            <?php endif; ?>
                                            <?php if ($user['is_admin'] != 1): // Prevent deleting admin users ?>
                                                <a href="?delete_user=<?= $user['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Videos -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">Videos</h2>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploader</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($videos as $video): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= $video['title'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= $video['uploader_name'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= $video['views'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= date('M j, Y', strtotime($video['upload_date'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="../watch.php?id=<?= $video['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3" target="_blank">View</a>
                                            <a href="?delete_video=<?= $video['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this video?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>