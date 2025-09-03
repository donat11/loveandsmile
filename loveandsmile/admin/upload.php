<?php
session_start();
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: access_denied.php");
    exit();
}

$message = '';

// Create uploads directory if it doesn't exist
if (!file_exists('../uploads')) {
    mkdir('../uploads', 0777, true);
}

// Create thumbnails directory if it doesn't exist
if (!file_exists('../thumbnails')) {
    mkdir('../thumbnails', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    // Handle video upload
    if (isset($_FILES['video']) && $_FILES['video']['error'] == UPLOAD_ERR_OK) {
        $videoFile = $_FILES['video'];
        $videoFileName = time() . '_' . basename($videoFile['name']);
        $videoTargetPath = "../uploads/" . $videoFileName;
        
        // Handle thumbnail upload
        $thumbnailFileName = '';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
            $thumbnailFile = $_FILES['thumbnail'];
            $thumbnailFileName = time() . '_' . basename($thumbnailFile['name']);
            $thumbnailTargetPath = "../thumbnails/" . $thumbnailFileName;
        }
        
        // Check file types
        $videoFileType = strtolower(pathinfo($videoTargetPath, PATHINFO_EXTENSION));
        $allowedVideoTypes = array('mp4', 'avi', 'mov', 'wmv', 'mpeg', 'mkv');
        
        if (!in_array($videoFileType, $allowedVideoTypes)) {
            $message = "Error: Only MP4, AVI, MOV, WMV, MPEG, and MKV files are allowed for videos.";
        } elseif ($videoFile['size'] > 500000000) { // 500MB limit
            $message = "Error: Video file is too large. Maximum size is 500MB.";
        } else {
            // Try to upload files
            if (move_uploaded_file($videoFile['tmp_name'], $videoTargetPath)) {
                if (!empty($thumbnailFileName)) {
                    move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailTargetPath);
                }
                
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO videos (title, description, filename, thumbnail, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $videoFileName, $thumbnailFileName, $_SESSION['user_id']]);
                
                $message = "Video uploaded successfully!";
            } else {
                $message = "Error uploading video. Please check folder permissions.";
            }
        }
    } else {
        $message = "Error: Please select a video file to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video - Love and Smile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
                <a href="index.php" class="block py-2 px-4 hover:bg-red-700">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="upload.php" class="block py-2 px-4 bg-red-700">
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
            <h1 class="text-2xl font-bold mb-6">Upload Video</h1>
            
            <?php if ($message): ?>
                <div class="<?php echo strpos($message, 'Error') === 0 ? 'alert-error' : 'alert-success'; ?> mb-6">
                    <?= $message ?>
                    <?php if (strpos($message, 'permissions') !== false): ?>
                        <p class="mt-2">Please make sure the 'uploads' and 'thumbnails' folders exist and have write permissions.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label class="block text-gray-700 mb-2" for="title">Video Title *</label>
                        <input type="text" id="title" name="title" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                    </div>
                    
                    <div class="form-group">
                        <label class="block text-gray-700 mb-2" for="description">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="block text-gray-700 mb-2" for="video">Video File *</label>
                        <input type="file" id="video" name="video" accept=".mp4,.avi,.mov,.wmv,.mpeg,.mkv" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: MP4, AVI, MOV, WMV, MPEG, MKV. Max size: 500MB</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="block text-gray-700 mb-2" for="thumbnail">Thumbnail (Optional)</label>
                        <input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, PNG</p>
                    </div>
                    
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">Upload Video</button>
                </form>
            </div>

            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Troubleshooting Tips</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Make sure the <code>uploads</code> and <code>thumbnails</code> folders exist in your project directory</li>
                    <li>Check that these folders have write permissions</li>
                    <li>Try using a smaller video file for testing (under 10MB)</li>
                    <li>Use MP4 format for best compatibility</li>
                    <li>Check your PHP error logs for any specific error messages</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const videoFile = document.getElementById('video').files[0];
            const maxSize = 500 * 1024 * 1024; // 500MB in bytes
            
            if (videoFile && videoFile.size > maxSize) {
                e.preventDefault();
                alert('Error: Video file is too large. Maximum size is 500MB.');
                return false;
            }
        });
    </script>
</body>
</html>