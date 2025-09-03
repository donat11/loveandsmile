<?php
// Test if uploads directory is writable
$uploadsWritable = is_writable('uploads');
$thumbnailsWritable = is_writable('thumbnails');

echo "Uploads folder writable: " . ($uploadsWritable ? "YES" : "NO") . "<br>";
echo "Thumbnails folder writable: " . ($thumbnailsWritable ? "YES" : "NO") . "<br>";

// Test PHP file upload settings
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post max size: " . ini_get('post_max_size') . "<br>";
?>