<?php
/**
 * Family Album Management
 * Admin panel for managing photos and access control
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Family Album Management";
$message = '';
$error = '';

// Handle redirect messages from upload
if (isset($_GET['uploaded'])) {
    $uploadedCount = (int)$_GET['uploaded'];
    $errorCount = (int)($_GET['errors'] ?? 0);
    
    if ($uploadedCount > 0 && $errorCount === 0) {
        $message = "Successfully uploaded {$uploadedCount} " . ($uploadedCount === 1 ? 'photo' : 'photos') . ".";
    } elseif ($uploadedCount > 0 && $errorCount > 0) {
        $message = "Uploaded {$uploadedCount} photos successfully. {$errorCount} failed.";
    }
}

// Handle photo deletion
if (isset($_POST['delete_photo'])) {
    $photoId = (int)$_POST['photo_id'];
    
    try {
        $db = getDb();
        
        // Get photo details first for file cleanup
        $stmt = $db->prepare("SELECT file_path FROM family_album_photos WHERE id = ?");
        $stmt->execute([$photoId]);
        $photo = $stmt->fetch();
        
        if ($photo) {
            // Delete from database
            $deleteStmt = $db->prepare("DELETE FROM family_album_photos WHERE id = ?");
            $deleteStmt->execute([$photoId]);
            
            // Delete file if it exists
            $filePath = __DIR__ . '/../../' . $photo['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $message = "Photo deleted successfully.";
        } else {
            $error = "Photo not found.";
        }
        
    } catch (PDOException $e) {
        error_log("Photo deletion error: " . $e->getMessage());
        $error = "Failed to delete photo.";
    }
}

// Handle display order update
if (isset($_POST['update_order'])) {
    try {
        $db = getDb();
        
        foreach ($_POST['photo_orders'] as $photoId => $order) {
            $stmt = $db->prepare("UPDATE family_album_photos SET display_order = ? WHERE id = ?");
            $stmt->execute([(int)$order, (int)$photoId]);
        }
        
        $message = "Display order updated successfully.";
        
    } catch (PDOException $e) {
        error_log("Order update error: " . $e->getMessage());
        $error = "Failed to update display order.";
    }
}

// Get all photos
$photos = [];
try {
    $db = getDb();
    $stmt = $db->query("
        SELECT * FROM family_album_photos 
        ORDER BY display_order ASC, created_at DESC
    ");
    $photos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Photos fetch error: " . $e->getMessage());
    $error = "Failed to load photos.";
}

// Get access requests summary
$accessStats = [];
try {
    $stmt = $db->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM family_album_access 
        GROUP BY status
    ");
    while ($row = $stmt->fetch()) {
        $accessStats[$row['status']] = $row['count'];
    }
} catch (PDOException $e) {
    $accessStats = [];
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="flex">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 ml-64 pt-20">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-plum font-montserrat flex items-center gap-3">
                            <svg class="w-8 h-8 text-plum" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Family Album Management
                        </h1>
                        <p class="text-gray-600 mt-1">Manage family photos and access control</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="upload.php" class="bg-plum text-cream px-4 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Upload Photos
                        </a>
                        <a href="access-control.php" class="bg-gold text-plum px-4 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Access Control
                        </a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-plum to-plum/90 text-cream p-6 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-cream/70 text-sm">Total Photos</p>
                                <p class="text-2xl font-bold"><?php echo count($photos); ?></p>
                            </div>
                            <svg class="w-8 h-8 text-cream/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-gold to-gold/90 text-plum p-6 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-plum/70 text-sm">Pending Requests</p>
                                <p class="text-2xl font-bold"><?php echo $accessStats['pending'] ?? 0; ?></p>
                            </div>
                            <svg class="w-8 h-8 text-plum/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/70 text-sm">Approved Access</p>
                                <p class="text-2xl font-bold"><?php echo $accessStats['approved'] ?? 0; ?></p>
                            </div>
                            <svg class="w-8 h-8 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-rose to-rose/90 text-plum p-6 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-plum/70 text-sm">Featured Photos</p>
                                <p class="text-2xl font-bold"><?php echo count(array_filter($photos, fn($p) => $p['is_featured'])); ?></p>
                            </div>
                            <svg class="w-8 h-8 text-plum/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-green-700"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($photos)): ?>
                <!-- No Photos State -->
                <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Photos Yet</h3>
                    <p class="text-gray-500 mb-6">Start building your family album by uploading your first photo.</p>
                    <a href="upload.php" class="bg-plum text-cream px-6 py-3 rounded-lg font-medium hover:bg-opacity-90 transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Upload First Photo
                    </a>
                </div>
            <?php else: ?>
                <!-- Photos Management -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-plum to-plum/90 text-cream flex items-center justify-between">
                        <h2 class="text-xl font-bold">Photo Gallery Management</h2>
                        <span class="text-cream/70 text-sm"><?php echo count($photos); ?> photos</span>
                    </div>

                    <div class="p-6">
                        <form method="POST" id="orderForm">
                            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                                <?php foreach ($photos as $index => $photo): ?>
                                    <div class="photo-card bg-gray-50 rounded-lg overflow-hidden border border-gray-200 hover:border-gold transition-colors">
                                        <!-- Photo Preview -->
                                        <div class="aspect-w-16 aspect-h-12 bg-gray-100">
                                            <?php 
                                                $imagePath = "../../" . htmlspecialchars($photo['file_path']);
                                                $imageExists = file_exists(__DIR__ . '/../../' . $photo['file_path']);
                                                
                                                if ($imageExists): 
                                            ?>
                                                <img 
                                                    src="<?php echo $imagePath; ?>" 
                                                    alt="<?php echo htmlspecialchars($photo['alt_text'] ?? 'Family photo'); ?>"
                                                    class="w-full h-48 object-cover"
                                                    onerror="this.src='/image-proxy.php?path=<?php echo urlencode(basename($photo['file_path'])); ?>'"
                                                />
                                            <?php else: ?>
                                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                                    <div class="text-center">
                                                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span class="text-xs">Image Missing</span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Photo Info -->
                                        <div class="p-4">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <h3 class="font-medium text-plum truncate"><?php echo htmlspecialchars($photo['filename']); ?></h3>
                                                    <p class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($photo['created_at'])); ?></p>
                                                </div>
                                                
                                                <?php if ($photo['is_featured']): ?>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gold/20 text-plum">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                        </svg>
                                                        Featured
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Caption Preview -->
                                            <?php if ($photo['caption']): ?>
                                                <div class="mb-3">
                                                    <p class="text-sm text-gray-700 line-clamp-2"><?php echo htmlspecialchars($photo['caption']); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Scripture Reference -->
                                            <?php if ($photo['scripture_reference']): ?>
                                                <div class="mb-3">
                                                    <p class="text-xs text-gold font-medium"><?php echo htmlspecialchars($photo['scripture_reference']); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Display Order -->
                                            <div class="flex items-center gap-2 mb-3">
                                                <label class="text-xs text-gray-600">Order:</label>
                                                <input 
                                                    type="number" 
                                                    name="photo_orders[<?php echo $photo['id']; ?>]" 
                                                    value="<?php echo $photo['display_order']; ?>"
                                                    class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-plum"
                                                    min="0"
                                                />
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                                <div class="flex gap-2">
                                                    <a 
                                                        href="edit.php?id=<?php echo $photo['id']; ?>" 
                                                        class="text-plum hover:text-gold text-sm font-medium transition-colors"
                                                        title="Edit Photo"
                                                    >
                                                        Edit
                                                    </a>
                                                </div>

                                                <form method="POST" class="inline" onsubmit="return confirm('Delete this photo permanently?');">
                                                    <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                                    <button 
                                                        type="submit" 
                                                        name="delete_photo"
                                                        class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors"
                                                        title="Delete Photo"
                                                    >
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Update Order Button -->
                            <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end">
                                <button 
                                    type="submit" 
                                    name="update_order"
                                    class="bg-gold text-plum px-6 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors"
                                >
                                    Update Display Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-r from-rose to-cream p-6 rounded-lg border border-gold/30">
                    <h3 class="text-lg font-semibold text-plum mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        View Album
                    </h3>
                    <p class="text-sm text-gray-700 mb-4">See how visitors experience your family album</p>
                    <a 
                        href="/family_album/access" 
                        target="_blank"
                        class="inline-flex items-center gap-2 bg-plum text-cream px-4 py-2 rounded-lg text-sm font-medium hover:bg-opacity-90 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Preview Album
                    </a>
                </div>

                <div class="bg-gradient-to-r from-cream to-rose p-6 rounded-lg border border-gold/30">
                    <h3 class="text-lg font-semibold text-plum mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Run Migration
                    </h3>
                    <p class="text-sm text-gray-700 mb-4">Set up database tables if not done yet</p>
                    <a 
                        href="../migrations/002_family_album.php"
                        class="inline-flex items-center gap-2 bg-gold text-plum px-4 py-2 rounded-lg text-sm font-medium hover:bg-opacity-90 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        Database Setup
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<style>
/* Line clamp utility for older browsers */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>