<?php
/**
 * Edit Family Album Photo
 * Admin interface for editing photo details and captions
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Edit Family Photo";
$photoId = (int)($_GET['id'] ?? 0);

if (!$photoId) {
    header('Location: manage.php');
    exit;
}

// Get photo details
$photo = null;
try {
    $db = getDb();
    $stmt = $db->prepare("SELECT * FROM family_album_photos WHERE id = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if (!$photo) {
        header('Location: manage.php?error=Photo not found');
        exit;
    }
} catch (PDOException $e) {
    error_log("Photo fetch error: " . $e->getMessage());
    header('Location: manage.php?error=Database error');
    exit;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = trim($_POST['caption'] ?? '');
    $scriptureRef = trim($_POST['scripture_reference'] ?? '');
    $altText = trim($_POST['alt_text'] ?? '');
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($altText)) {
        $error = 'Alt text is required for accessibility.';
    } else {
        try {
            $stmt = $db->prepare("
                UPDATE family_album_photos 
                SET caption = ?, scripture_reference = ?, alt_text = ?, 
                    display_order = ?, is_featured = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $caption ?: null,
                $scriptureRef ?: null,
                $altText,
                $displayOrder,
                $isFeatured,
                $photoId
            ]);
            
            $message = 'Photo updated successfully.';
            
            // Refresh photo data
            $stmt = $db->prepare("SELECT * FROM family_album_photos WHERE id = ?");
            $stmt->execute([$photoId]);
            $photo = $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Photo update error: " . $e->getMessage());
            $error = 'Failed to update photo.';
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="flex">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 ml-64 pt-20">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="manage.php" class="text-plum hover:text-gold transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-plum font-montserrat">✏️ Edit Family Photo</h1>
                        <p class="text-gray-600 mt-1">Update photo caption, scripture reference, and settings</p>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Photo Preview -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-plum to-plum/90 text-cream">
                        <h2 class="text-xl font-bold">Photo Preview</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="aspect-w-16 aspect-h-12 mb-4">
                            <img 
                                src="/image-proxy.php?path=<?php echo urlencode(basename($photo['file_path'])); ?>" 
                                alt="<?php echo htmlspecialchars($photo['alt_text'] ?? 'Family photo'); ?>"
                                class="w-full h-64 object-cover rounded-lg border"
                                onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 400 300\" fill=\"%23e5e7eb\"><rect width=\"100%25\" height=\"100%25\" fill=\"%23f3f4f6\"/><text x=\"50%25\" y=\"50%25\" text-anchor=\"middle\" dy=\".3em\" fill=\"%236b7280\" font-family=\"system-ui\" font-size=\"16\">Image Preview</text></svg>'"
                            />
                        </div>
                        
                        <!-- File Info -->
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Filename:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($photo['filename']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>File Size:</span>
                                <span class="font-medium"><?php echo number_format($photo['file_size'] / 1024, 1); ?> KB</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Type:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($photo['mime_type']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Uploaded:</span>
                                <span class="font-medium"><?php echo date('M j, Y g:i A', strtotime($photo['created_at'])); ?></span>
                            </div>
                            <?php if ($photo['updated_at'] !== $photo['created_at']): ?>
                                <div class="flex justify-between">
                                    <span>Last Updated:</span>
                                    <span class="font-medium"><?php echo date('M j, Y g:i A', strtotime($photo['updated_at'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Featured Badge -->
                        <?php if ($photo['is_featured']): ?>
                            <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gold/20 text-plum">
                                ⭐ Featured Photo
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gold to-gold/90 text-plum">
                        <h2 class="text-xl font-bold">Edit Details</h2>
                    </div>

                    <form method="POST" class="p-6 space-y-6">
                        <!-- Caption -->
                        <div>
                            <label for="caption" class="block text-sm font-medium text-plum mb-2">
                                Caption or Story
                            </label>
                            <textarea 
                                id="caption"
                                name="caption" 
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                                placeholder="Share the story behind this photo..."
                            ><?php echo htmlspecialchars($photo['caption'] ?? ''); ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">This will be displayed as the main caption for your photo</p>
                        </div>

                        <!-- Scripture Reference -->
                        <div>
                            <label for="scripture_reference" class="block text-sm font-medium text-plum mb-2">
                                Scripture Reference (Optional)
                            </label>
                            <input 
                                type="text" 
                                id="scripture_reference"
                                name="scripture_reference" 
                                value="<?php echo htmlspecialchars($photo['scripture_reference'] ?? ''); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                                placeholder="e.g., Ephesians 5:33 NIV"
                            />
                            <p class="text-xs text-gray-500 mt-1">Include a Bible verse that relates to this photo</p>
                        </div>

                        <!-- Alt Text -->
                        <div>
                            <label for="alt_text" class="block text-sm font-medium text-plum mb-2">
                                Alt Text (Accessibility) *
                            </label>
                            <input 
                                type="text" 
                                id="alt_text"
                                name="alt_text" 
                                value="<?php echo htmlspecialchars($photo['alt_text'] ?? ''); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                                placeholder="Describe what's in the photo for screen readers"
                                required
                            />
                            <p class="text-xs text-gray-500 mt-1">Required for accessibility - describe what's visible in the photo</p>
                        </div>

                        <!-- Display Order -->
                        <div>
                            <label for="display_order" class="block text-sm font-medium text-plum mb-2">
                                Display Order
                            </label>
                            <input 
                                type="number" 
                                id="display_order"
                                name="display_order" 
                                value="<?php echo $photo['display_order']; ?>"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                            />
                            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first. Use 0 for automatic ordering.</p>
                        </div>

                        <!-- Featured Photo -->
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="is_featured"
                                name="is_featured"
                                <?php echo $photo['is_featured'] ? 'checked' : ''; ?>
                                class="h-4 w-4 text-plum focus:ring-plum border-gray-300 rounded"
                            />
                            <label for="is_featured" class="ml-2 block text-sm font-medium text-gray-700">
                                Mark as Featured Photo
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">Featured photos may be highlighted in the gallery</p>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                            <a 
                                href="manage.php" 
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="px-6 py-3 bg-plum text-cream rounded-lg font-medium hover:bg-opacity-90 transition-colors"
                            >
                                Update Photo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Scripture Suggestions -->
            <div class="mt-8 bg-gradient-to-r from-rose to-cream border border-gold/30 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-plum mb-3">💡 Scripture Suggestions</h3>
                <p class="text-sm text-gray-700 mb-4">Click any verse to add it to your scripture reference field:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <button 
                            type="button" 
                            class="scripture-suggestion text-left p-3 bg-white/70 rounded border border-gold/30 hover:border-gold transition-colors w-full"
                            data-reference="Ephesians 5:33 NIV"
                        >
                            <div class="font-medium text-plum text-sm">Ephesians 5:33 NIV</div>
                            <div class="text-xs text-gray-600">"However, each one of you also must love his wife..."</div>
                        </button>
                        
                        <button 
                            type="button" 
                            class="scripture-suggestion text-left p-3 bg-white/70 rounded border border-gold/30 hover:border-gold transition-colors w-full"
                            data-reference="Psalm 127:3 ESV"
                        >
                            <div class="font-medium text-plum text-sm">Psalm 127:3 ESV</div>
                            <div class="text-xs text-gray-600">"Children are a heritage from the Lord..."</div>
                        </button>
                    </div>
                    
                    <div class="space-y-2">
                        <button 
                            type="button" 
                            class="scripture-suggestion text-left p-3 bg-white/70 rounded border border-gold/30 hover:border-gold transition-colors w-full"
                            data-reference="1 Corinthians 13:4-7"
                        >
                            <div class="font-medium text-plum text-sm">1 Corinthians 13:4-7</div>
                            <div class="text-xs text-gray-600">"Love is patient, love is kind..."</div>
                        </button>
                        
                        <button 
                            type="button" 
                            class="scripture-suggestion text-left p-3 bg-white/70 rounded border border-gold/30 hover:border-gold transition-colors w-full"
                            data-reference="Joshua 24:15"
                        >
                            <div class="font-medium text-plum text-sm">Joshua 24:15</div>
                            <div class="text-xs text-gray-600">"But as for me and my house..."</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Scripture suggestion functionality
document.addEventListener('DOMContentLoaded', function() {
    const scriptureField = document.getElementById('scripture_reference');
    const suggestionButtons = document.querySelectorAll('.scripture-suggestion');
    
    suggestionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reference = this.dataset.reference;
            scriptureField.value = reference;
            scriptureField.focus();
            
            // Highlight the button briefly
            this.classList.add('bg-gold/20');
            setTimeout(() => {
                this.classList.remove('bg-gold/20');
            }, 500);
        });
    });
});

// Auto-resize textarea
const textarea = document.getElementById('caption');
textarea.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>

<style>
/* Smooth transitions */
input, textarea, button {
    transition: all 0.2s ease;
}

/* Focus states */
input:focus, textarea:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74, 25, 66, 0.15);
}

/* Scripture suggestion hover effect */
.scripture-suggestion:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(212, 160, 23, 0.15);
}

/* Auto-resize textarea */
textarea {
    resize: vertical;
    min-height: 100px;
}
</style>