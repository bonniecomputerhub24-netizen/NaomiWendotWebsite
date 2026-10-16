<?php
/**
 * Family Album Photo Upload
 * Admin interface for uploading new family photos with captions
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Upload Family Photos";
$message = '';
$error = '';

// Enhanced photo upload with validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    $uploadDir = __DIR__ . '/../../uploads/family_album/';
    
    // Ensure upload directory exists with proper permissions
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $error = "Failed to create upload directory.";
        }
    }
    
    if (!$error) {
        $uploadedCount = 0;
        $errorCount = 0;
        $errors = [];
        
        try {
            $db = getDb();
            
            // Get the highest display order for new photos
            $stmt = $db->query("SELECT COALESCE(MAX(display_order), 0) as max_order FROM family_album_photos");
            $maxOrder = $stmt->fetch()['max_order'];
            
            foreach ($_FILES['photos']['tmp_name'] as $index => $tmpName) {
                if (!$tmpName || $_FILES['photos']['error'][$index] !== UPLOAD_ERR_OK) {
                    if ($_FILES['photos']['error'][$index] !== UPLOAD_ERR_NO_FILE) {
                        $errors[] = "Upload error for " . ($_FILES['photos']['name'][$index] ?? "file {$index}");
                        $errorCount++;
                    }
                    continue;
                }
                
                $originalName = $_FILES['photos']['name'][$index];
                $fileSize = $_FILES['photos']['size'][$index];
                $mimeType = $_FILES['photos']['type'][$index];
                
                // Enhanced file validation
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detectedMimeType = $finfo->file($tmpName);
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($detectedMimeType, $allowedTypes)) {
                    $errors[] = "{$originalName}: Invalid file type";
                    $errorCount++;
                    continue;
                }
                
                // Validate file size (max 5MB)
                if ($fileSize > 5 * 1024 * 1024) {
                    $errors[] = "{$originalName}: File too large (max 5MB)";
                    $errorCount++;
                    continue;
                }
                
                // Validate image integrity
                $imageInfo = getimagesize($tmpName);
                if ($imageInfo === false) {
                    $errors[] = "{$originalName}: Corrupted image file";
                    $errorCount++;
                    continue;
                }
                
                // Generate unique filename with sanitization
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $safeBasename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $filename = 'family_' . date('Ymd_His') . '_' . $index . '_' . $safeBasename . '.' . $extension;
                $filePath = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $filePath)) {
                    // Set proper file permissions
                    chmod($filePath, 0644);
                    
                    // Get form data for this photo
                    $caption = trim($_POST['captions'][$index] ?? '');
                    $scriptureRef = trim($_POST['scripture_references'][$index] ?? '');
                    $altText = trim($_POST['alt_texts'][$index] ?? '');
                    $isFeatured = isset($_POST['is_featured'][$index]) ? 1 : 0;
                    
                    // Validate and sanitize inputs
                    $caption = $caption ? htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') : null;
                    $scriptureRef = $scriptureRef ? htmlspecialchars($scriptureRef, ENT_QUOTES, 'UTF-8') : null;
                    $altText = $altText ? htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') : 'Family photo';
                    
                    // Insert into database
                    $stmt = $db->prepare("
                        INSERT INTO family_album_photos 
                        (filename, file_path, file_size, mime_type, caption, scripture_reference, alt_text, 
                         display_order, is_featured, uploaded_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $originalName,
                        'uploads/family_album/' . $filename,
                        $fileSize,
                        $detectedMimeType,
                        $caption,
                        $scriptureRef,
                        $altText,
                        ++$maxOrder,
                        $isFeatured,
                        $_SESSION['admin_username'] ?? 'admin'
                    ]);
                    
                    $uploadedCount++;
                } else {
                    $errors[] = "{$originalName}: Failed to save file";
                    $errorCount++;
                }
            }
            
            // Set message based on results
            if ($uploadedCount > 0 && $errorCount === 0) {
                $message = "Successfully uploaded {$uploadedCount} " . ($uploadedCount === 1 ? 'photo' : 'photos') . ".";
                // Redirect to manage page after successful upload
                header("Location: manage.php?uploaded={$uploadedCount}");
                exit;
            } elseif ($uploadedCount > 0 && $errorCount > 0) {
                $message = "Uploaded {$uploadedCount} photos successfully. {$errorCount} failed.";
                if (!empty($errors)) {
                    $error = "Errors: " . implode(', ', array_slice($errors, 0, 3));
                }
                // Still redirect on partial success
                header("Location: manage.php?uploaded={$uploadedCount}&errors={$errorCount}");
                exit;
            } elseif ($uploadedCount === 0 && $errorCount > 0) {
                $error = "Failed to upload photos: " . implode(', ', array_slice($errors, 0, 3));
            } else {
                $error = "No valid photos were selected for upload.";
            }
            
        } catch (Exception $e) {
            error_log("Photo upload error: " . $e->getMessage());
            $error = "Upload failed due to a system error: " . $e->getMessage();
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
                        <h1 class="text-3xl font-bold text-plum font-montserrat flex items-center gap-3">
                            <svg class="w-8 h-8 text-plum" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Upload Family Photos
                        </h1>
                        <p class="text-gray-600 mt-1">Add new photos to your family album with captions and scripture</p>
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

            <!-- Upload Guidelines -->
            <div class="bg-gradient-to-r from-cream to-rose border border-gold/30 rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-plum mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Upload Guidelines
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Supported formats: JPG, PNG, GIF, WebP</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Maximum file size: 5MB per photo</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Multiple photos can be uploaded at once</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            <span>Add meaningful captions and scripture references</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            <span>Use alt text for accessibility</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            <span>Mark special photos as featured</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-plum to-plum/90 text-cream">
                    <h2 class="text-xl font-bold">Upload New Photos</h2>
                </div>

                <form method="POST" enctype="multipart/form-data" class="p-6">
                    <!-- File Selection -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-plum mb-3">
                            Select Photos *
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-plum transition-colors">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-gray-600 mb-2">Click to select photos or drag and drop</p>
                            <p class="text-sm text-gray-500">JPG, PNG, GIF, WebP up to 5MB each</p>
                            <input 
                                type="file" 
                                name="photos[]" 
                                id="photo-input"
                                accept="image/*" 
                                multiple 
                                required
                                class="hidden"
                                onchange="handleFileSelect(this.files)"
                            />
                            <button 
                                type="button" 
                                onclick="document.getElementById('photo-input').click()"
                                class="mt-4 bg-plum text-cream px-6 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors"
                            >
                                Select Photos
                            </button>
                        </div>
                    </div>

                    <!-- Selected Photos Preview -->
                    <div id="photo-previews" class="mb-8 hidden">
                        <h3 class="text-lg font-semibold text-plum mb-4">Selected Photos</h3>
                        <div id="preview-container" class="space-y-6"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4">
                        <a href="manage.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            id="submit-btn"
                            class="px-6 py-3 bg-plum text-cream rounded-lg font-medium hover:bg-opacity-90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                        >
                            Upload Photos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
function handleFileSelect(files) {
    const previewsDiv = document.getElementById('photo-previews');
    const previewContainer = document.getElementById('preview-container');
    const submitBtn = document.getElementById('submit-btn');
    
    if (files.length === 0) {
        previewsDiv.classList.add('hidden');
        submitBtn.disabled = true;
        return;
    }
    
    previewsDiv.classList.remove('hidden');
    previewContainer.innerHTML = '';
    submitBtn.disabled = false;
    
    Array.from(files).forEach((file, index) => {
        const photoDiv = document.createElement('div');
        photoDiv.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200';
        
        // Create image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            photoDiv.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Image Preview -->
                    <div class="md:col-span-1">
                        <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded-lg border">
                        <p class="text-xs text-gray-500 mt-1 truncate">${file.name}</p>
                        <p class="text-xs text-gray-400">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                    
                    <!-- Form Fields -->
                    <div class="md:col-span-2 space-y-4">
                        <!-- Caption -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Caption or Story
                            </label>
                            <textarea 
                                name="captions[${index}]" 
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                                placeholder="Share the story behind this photo..."
                            ></textarea>
                        </div>
                        
                        <!-- Scripture Reference -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Scripture Reference (Optional)
                            </label>
                            <input 
                                type="text" 
                                name="scripture_references[${index}]" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                                placeholder="e.g., Ephesians 5:33 NIV"
                            />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Alt Text -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Alt Text (Accessibility)
                                </label>
                                <input 
                                    type="text" 
                                    name="alt_texts[${index}]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                                    placeholder="Describe what's in the photo"
                                />
                            </div>
                            
                            <!-- Featured -->
                            <div class="flex items-center h-full">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="is_featured[${index}]" 
                                        class="text-plum focus:ring-plum border-gray-300 rounded"
                                    />
                                    <span class="text-sm font-medium text-gray-700">Mark as Featured</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
        
        previewContainer.appendChild(photoDiv);
    });
}

// Example captions for inspiration
const exampleCaptions = [
    "Does marriage work? Yes. Let your marriage be founded in God's word. Rooted in Christ, built in Christ and remaining steadfast in Christ.",
    "However, each one of you also must love his wife as he loves himself, and the wife must respect her husband.",
    "A family that prays together stays together.",
    "Children are a heritage from the Lord, offspring a reward from him."
];

// Add example caption suggestions
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .example-caption {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .example-caption:hover {
            background-color: rgba(212, 160, 23, 0.1);
            border-color: #D4A017;
        }
    `;
    document.head.appendChild(style);
});
</script>

<style>
/* Custom file input styling */
input[type="file"] {
    display: none;
}

/* Preview grid responsiveness */
@media (max-width: 768px) {
    .preview-grid {
        grid-template-columns: 1fr;
    }
}

/* Smooth transitions for all interactive elements */
button, input, textarea {
    transition: all 0.2s ease;
}

/* Focus states */
input:focus, textarea:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74, 25, 66, 0.15);
}
</style>