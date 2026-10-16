<?php
/**
 * Secure Image Proxy for Family Album
 * Serves images only to authenticated users or admins
 */

session_start();
require_once __DIR__ . '/config/db.php';

// Check if user has valid family album access OR is an admin
$hasAccess = false;

// Check admin access first
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $hasAccess = true;
} elseif (isset($_SESSION['family_member_id'])) {
    // Check family member access
    try {
        $db = getDb();
        $stmt = $db->prepare("
            SELECT id FROM family_members 
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$_SESSION['family_member_id']]);
        $hasAccess = $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Image Proxy Family Member Check Error: " . $e->getMessage());
    }
} elseif (isset($_SESSION['family_album_access'])) {
    // Legacy: Check old family album access for regular users (for backward compatibility)
    try {
        $db = getDb();
        $stmt = $db->prepare("
            SELECT id FROM family_album_access 
            WHERE access_code = ? AND status = 'approved' 
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$_SESSION['family_album_access']]);
        $hasAccess = $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Image Proxy Access Check Error: " . $e->getMessage());
    }
}

// Deny access if not authenticated
if (!$hasAccess) {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo 'Access denied';
    exit;
}

// Get requested file path
$requestedPath = $_GET['path'] ?? '';

if (empty($requestedPath)) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'No file specified';
    exit;
}

// Security: Only allow files from the family album upload directory
$uploadDir = __DIR__ . '/uploads/family_album/';
$fullPath = realpath($uploadDir . basename($requestedPath));

// Verify path is within allowed directory
if (!$fullPath || !str_starts_with($fullPath, realpath($uploadDir))) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'File not found';
    exit;
}

// Check if file exists
if (!file_exists($fullPath)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'File not found';
    exit;
}

// Verify it's an image file
$imageInfo = getimagesize($fullPath);
if ($imageInfo === false) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Invalid image file';
    exit;
}

// Set appropriate headers
$mimeType = $imageInfo['mime'];
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

// Output the image
readfile($fullPath);
exit;
?>