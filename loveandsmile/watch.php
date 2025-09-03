<?php
session_start();
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$video_id = $_GET['id'];

// Increment view count
$pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$video_id]);

// Get video details
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$video_id]);
$video = $stmt->fetch();

if (!$video) {
    header("Location: index.php");
    exit();
}

// Get comments for this video
$stmt = $pdo->prepare("
    SELECT c.*, u.name 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.video_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$video_id]);
$comments = $stmt->fetchAll();

// Get replies for comments
$commentIds = array_column($comments, 'id');
$placeholders = implode(',', array_fill(0, count($comments), '?'));
$replies = [];

if (!empty($commentIds)) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.name 
        FROM comment_replies r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.comment_id IN ($placeholders) 
        ORDER BY r.created_at ASC
    ");
    $stmt->execute($commentIds);
    $replies = $stmt->fetchAll();
    
    // Group replies by comment ID
    $repliesByComment = [];
    foreach ($replies as $reply) {
        $repliesByComment[$reply['comment_id']][] = $reply;
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO comments (video_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$video_id, $user_id, $comment]);
    
    header("Location: watch.php?id=" . $video_id);
    exit();
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply']) && isset($_POST['comment_id']) && isset($_SESSION['user_id'])) {
    $reply = $_POST['reply'];
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO comment_replies (comment_id, user_id, reply) VALUES (?, ?, ?)");
    $stmt->execute([$comment_id, $user_id, $reply]);
    
    header("Location: watch.php?id=" . $video_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $video['title'] ?> - Love and Smile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-red-600 text-white p-4 flex justify-between items-center">
        <div class="flex items-center">
            <a href="index.php">
                <img src="logo.png" alt="Love and Smile Logo" class="h-8 w-8 mr-2">
            </a>
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

    <div class="flex flex-col lg:flex-row p-4">
        <!-- Video Player -->
        <div class="w-full lg:w-2/3">
            <div class="bg-black rounded-lg overflow-hidden">
                <video controls class="w-full h-auto" poster="thumbnails/<?= $video['thumbnail'] ?? 'default.jpg' ?>">
                    <source src="uploads/<?= $video['filename'] ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            
            <div class="mt-4 bg-white p-4 rounded-lg shadow">
                <h1 class="text-2xl font-bold"><?= $video['title'] ?></h1>
                <p class="text-gray-600 mt-2"><?= $video['views'] ?> views</p>
                <p class="mt-4"><?= $video['description'] ?></p>
                
                <div class="flex mt-4 space-x-4">
                    <button class="flex items-center text-gray-700">
                        <i class="far fa-thumbs-up mr-1"></i> Like
                    </button>
                    <button class="flex items-center text-gray-700">
                        <i class="far fa-thumbs-down mr-1"></i> Dislike
                    </button>
                    <button class="flex items-center text-gray-700">
                        <i class="far fa-share-square mr-1"></i> Share
                    </button>
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="mt-4 bg-white p-4 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Comments</h2>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <form method="POST" class="mb-6">
                        <textarea name="comment" placeholder="Add a public comment..." class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600" rows="3" required></textarea>
                        <div class="flex justify-end mt-2">
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg">Comment</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-gray-600 mb-4">Please <a href="login.php" class="text-red-600">login</a> to comment.</p>
                <?php endif; ?>
                
                <?php foreach($comments as $comment): ?>
                    <div class="border-b border-gray-200 py-4">
                        <div class="flex">
                            <div class="flex-shrink-0 mr-3">
                                <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-user text-red-600"></i>
                                </div>
                            </div>
                            <div class="flex-grow">
                                <div class="flex items-center">
                                    <h3 class="font-semibold"><?= $comment['name'] ?></h3>
                                    <span class="text-gray-500 text-sm ml-2"><?= date('M j, Y', strtotime($comment['created_at'])) ?></span>
                                </div>
                                <p class="mt-1"><?= $comment['comment'] ?></p>
                                
                                <div class="flex mt-2 text-sm text-gray-600">
                                    <button class="flex items-center mr-4 like-btn" data-comment-id="<?= $comment['id'] ?>">
                                        <i class="far fa-thumbs-up mr-1"></i> 12
                                    </button>
                                    <button class="flex items-center mr-4 dislike-btn" data-comment-id="<?= $comment['id'] ?>">
                                        <i class="far fa-thumbs-down mr-1"></i> 2
                                    </button>
                                    <button class="reply-btn" data-comment-id="<?= $comment['id'] ?>">Reply</button>
                                    
                                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                        <form method="POST" action="delete_comment.php" class="ml-4">
                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                            <input type="hidden" name="video_id" value="<?= $video_id ?>">
                                            <button type="submit" class="text-red-600">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Reply form (hidden by default) -->
                                <div id="reply-form-<?= $comment['id'] ?>" class="mt-3 hidden">
                                    <form method="POST">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <textarea name="reply" placeholder="Write a reply..." class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600" rows="2" required></textarea>
                                        <div class="flex justify-end mt-2">
                                            <button type="button" class="cancel-reply-btn bg-gray-300 text-gray-800 px-4 py-2 rounded-lg mr-2" data-comment-id="<?= $comment['id'] ?>">Cancel</button>
                                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg">Reply</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Replies -->
                                <?php if(isset($repliesByComment[$comment['id']])): ?>
                                    <div class="mt-3 ml-6 border-l-2 border-gray-200 pl-3">
                                        <?php foreach($repliesByComment[$comment['id']] as $reply): ?>
                                            <div class="py-3">
                                                <div class="flex">
                                                    <div class="flex-shrink-0 mr-3">
                                                        <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                                            <i class="fas fa-user text-red-600 text-sm"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow">
                                                        <div class="flex items-center">
                                                            <h4 class="font-semibold text-sm"><?= $reply['name'] ?></h4>
                                                            <span class="text-gray-500 text-xs ml-2"><?= date('M j, Y', strtotime($reply['created_at'])) ?></span>
                                                        </div>
                                                        <p class="mt-1 text-sm"><?= $reply['reply'] ?></p>
                                                        
                                                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                                                            <form method="POST" action="delete_reply.php" class="mt-1">
                                                                <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                                                                <input type="hidden" name="video_id" value="<?= $video_id ?>">
                                                                <button type="submit" class="text-red-600 text-xs">Delete</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Suggested Videos Sidebar -->
        <div class="w-full lg:w-1/3 mt-6 lg:mt-0 lg:ml-6">
            <h2 class="text-xl font-bold mb-4">Suggested Videos</h2>
            
            <?php
            $stmt = $pdo->query("SELECT * FROM videos WHERE id != $video_id ORDER BY upload_date DESC LIMIT 5");
            $suggestedVideos = $stmt->fetchAll();
            ?>
            
            <?php foreach($suggestedVideos as $sVideo): ?>
                <a href="watch.php?id=<?= $sVideo['id'] ?>" class="flex mb-4 bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="w-40 h-24 flex-shrink-0">
                        <img src="thumbnails/<?= $sVideo['thumbnail'] ?? 'default.jpg' ?>" alt="<?= $sVideo['title'] ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-2">
                        <h3 class="font-semibold text-sm line-clamp-2"><?= $sVideo['title'] ?></h3>
                        <p class="text-gray-600 text-xs"><?= $sVideo['views'] ?> views</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/250790111301" target="_blank" class="fixed bottom-4 right-4 bg-green-500 text-white p-3 rounded-full shadow-lg">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>

    <script>
        // Show/hide reply form
        document.querySelectorAll('.reply-btn').forEach(button => {
            button.addEventListener('click', () => {
                const commentId = button.getAttribute('data-comment-id');
                document.getElementById(`reply-form-${commentId}`).classList.toggle('hidden');
            });
        });

        // Cancel reply
        document.querySelectorAll('.cancel-reply-btn').forEach(button => {
            button.addEventListener('click', () => {
                const commentId = button.getAttribute('data-comment-id');
                document.getElementById(`reply-form-${commentId}`).classList.add('hidden');
            });
        });
    </script>
</body>
</html>