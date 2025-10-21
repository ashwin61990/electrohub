<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/Database.php';
require_once '../classes/Admin.php';

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db);

// Verify admin status
if (!$admin->isAdmin($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $uploadDir = '../uploads/products/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $file = $_FILES['image'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Validation
    if ($fileError !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload failed with error code: ' . $fileError]);
        exit();
    }
    
    if (!in_array($fileExt, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)]);
        exit();
    }
    
    if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
        http_response_code(400);
        echo json_encode(['error' => 'File size too large. Maximum 5MB allowed.']);
        exit();
    }
    
    // Verify it's actually an image
    $imageInfo = getimagesize($fileTmpName);
    if ($imageInfo === false) {
        http_response_code(400);
        echo json_encode(['error' => 'File is not a valid image.']);
        exit();
    }
    
    // Generate unique filename
    $newFileName = uniqid('product_', true) . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Resize image if needed (optional)
        resizeImage($uploadPath, $uploadPath, 800, 600);
        
        // Return success response
        $imageUrl = 'uploads/products/' . $newFileName;
        echo json_encode([
            'success' => true,
            'filename' => $newFileName,
            'url' => $imageUrl,
            'size' => $fileSize
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to move uploaded file.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded.']);
}

/**
 * Resize image to fit within specified dimensions while maintaining aspect ratio
 */
function resizeImage($source, $destination, $maxWidth, $maxHeight) {
    $imageInfo = getimagesize($source);
    if (!$imageInfo) return false;
    
    list($originalWidth, $originalHeight, $imageType) = $imageInfo;
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    
    // If image is already smaller, don't resize
    if ($ratio >= 1) return true;
    
    $newWidth = (int)($originalWidth * $ratio);
    $newHeight = (int)($originalHeight * $ratio);
    
    // Create image resource based on type
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) return false;
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize image
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
    
    // Save resized image
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $destination, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $destination, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($newImage, $destination);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($newImage, $destination, 85);
            break;
    }
    
    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return true;
}
?>
