<?php
/**
 * Family Member Photo Upload
 * Allows family members to upload photos to share
 */

session_start();

// Debug path information
$configPath = __DIR__ . '/../config/db.php';
$functionsPath = __DIR__ . '/../includes/functions.php';

if (!file_exists($configPath)) {
    die("Config file not found at: " . $configPath);
}

if (!file_exists($functionsPath)) {
    die("Functions file not found at: " . $functionsPath);
}

require_once $configPath;
require_once $functionsPath;

// Check if user has valid family member login
$hasAccess = false;
$currentMember = null;

if (isset($_SESSION['family_member_id'])) {
    try {
        $db = getDb();
        $stmt = $db->prepare("
            SELECT * FROM family_members 
            WHERE id = ? AND is_active = 1 AND can_upload = 1
        ");
        $stmt->execute([$_SESSION['family_member_id']]);
        $currentMember = $stmt->fetch();
        
        if ($currentMember) {
            $hasAccess = true;
        }
    } catch (PDOException $e) {
        error_log("Family Album Member Check Error: " . $e->getMessage());
    }
}

// If no access, redirect to login page
if (!$hasAccess) {
    header('Location: access');
    exit;
}

$pageTitle = "Upload Family Photos";
$message = '';
$error = '';

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    $uploadDir = __DIR__ . '/../uploads/family_album/';
    
    // Ensure upload directory exists
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
                
                // Generate unique filename
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $safeBasename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $filename = 'member_' . $currentMember['id'] . '_' . date('Ymd_His') . '_' . $index . '_' . $safeBasename . '.' . $extension;
                $filePath = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $filePath)) {
                    chmod($filePath, 0644);
                    
                    // Get form data for this photo
                    $caption = trim($_POST['captions'][$index] ?? '');
                    $scriptureRef = trim($_POST['scripture_references'][$index] ?? '');
                    $altText = trim($_POST['alt_texts'][$index] ?? '');
                    $isFeatured = isset($_POST['is_featured'][$index]) ? 1 : 0;
                    
                    // Validate and sanitize inputs
                    $caption = $caption ? htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') : null;
                    $scriptureRef = $scriptureRef ? htmlspecialchars($scriptureRef, ENT_QUOTES, 'UTF-8') : null;
                    $altText = $altText ? htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') : 'Family photo uploaded by ' . $currentMember['full_name'];
                    
                    // Insert into database
                    $stmt = $db->prepare("
                        INSERT INTO family_album_photos 
                        (filename, file_path, file_size, mime_type, caption, scripture_reference, alt_text, 
                         display_order, is_featured, uploaded_by, uploaded_by_member, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
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
                        $currentMember['username'],
                        $currentMember['id']
                    ]);
                    
                    $uploadedCount++;
                } else {
                    $errors[] = "{$originalName}: Failed to save file";
                    $errorCount++;
                }
            }
            
            // Set message based on results
            if ($uploadedCount > 0 && $errorCount === 0) {
                $message = "Successfully uploaded {$uploadedCount} " . ($uploadedCount === 1 ? 'photo' : 'photos') . "!";
            } elseif ($uploadedCount > 0 && $errorCount > 0) {
                $message = "Uploaded {$uploadedCount} photos successfully. {$errorCount} failed.";
                if (!empty($errors)) {
                    $error = "Errors: " . implode(', ', array_slice($errors, 0, 3));
                }
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> — Naomi Wendot</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        plum: '#4A1942',
                        gold: '#D4A017',
                        cream: '#FFFDF5',
                        rose: '#FDEAEA',
                        charcoal: '#1C1C1C'
                    },
                    fontFamily: {
                        montserrat: ['Montserrat', 'sans-serif'],
                        century: ['Century Gothic', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Century Gothic', sans-serif;
            background: linear-gradient(135deg, #FFFDF5 0%, #FDEAEA 100%);
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <header class="bg-plum shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="index" class="text-cream hover:text-gold mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-cream font-montserrat flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Share Family Photos
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-gold text-sm font-medium"><?php echo htmlspecialchars($currentMember['full_name']); ?></div>
                        <div class="text-cream/70 text-xs"><?php echo ucfirst($currentMember['role']); ?></div>
                    </div>
                    <a href="logout" class="bg-cream text-plum px-4 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors">
                        Sign Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-4xl font-bold text-plum font-montserrat mb-4">
                Share Your Family Memories
            </h2>
            <p class="text-lg text-gray-700 max-w-2xl mx-auto">
                Upload photos that capture special moments and memories to share with the family.
            </p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-green-700 font-medium"><?php echo htmlspecialchars($message); ?></p>
                        <p class="text-green-600 text-sm mt-1">Your photos are now available in the family album!</p>
                    </div>
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
                Sharing Guidelines
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Share meaningful family moments</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Add captions to tell the story</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Include scripture references when meaningful</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span>Supported: JPG, PNG, GIF, WebP (max 5MB)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span>Your name will appear as the uploader</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span>Photos go live immediately for all family</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-plum to-plum/90 text-cream">
                <h2 class="text-xl font-bold">Upload Family Photos</h2>
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
                    <h3 class="text-lg font-semibold text-plum mb-4">Photos to Share</h3>
                    <div id="preview-container" class="space-y-6"></div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="index" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        id="submit-btn"
                        class="px-6 py-3 bg-plum text-cream rounded-lg font-medium hover:bg-opacity-90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        disabled
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Share with Family
                    </button>
                </div>
            </form>
        </div>
    </main>

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
                                        Caption or Story *
                                    </label>
                                    <textarea 
                                        name="captions[${index}]" 
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent"
                                        placeholder="Share the story behind this photo..."
                                        required
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
                                        placeholder="e.g., Psalm 127:3 NIV"
                                    />
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Alt Text -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Description (Accessibility)
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
                                            <span class="text-sm font-medium text-gray-700">Mark as Special</span>
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
    </script>
</body>
</html>