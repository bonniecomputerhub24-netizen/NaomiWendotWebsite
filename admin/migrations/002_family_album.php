<?php
/**
 * Migration: Create Family Album Tables
 * File: 002_family_album.php
 * 
 * Creates tables for the Family Album feature:
 * - family_album_photos: Store photo information and captions
 * - family_album_access: Control who can view the family album
 * 
 * Safe to re-run - checks table existence before creating
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../../config/db.php';

$pageTitle = "Family Album Migration";
$messages = [];
$errors = [];

// Function to check if table exists
function tableExists($db, $tableName) {
    try {
        $stmt = $db->prepare("SHOW TABLES LIKE :tableName");
        $stmt->execute([':tableName' => $tableName]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to create table safely
function createTable($db, $tableName, $sql) {
    try {
        $db->exec($sql);
        return ['success' => true, 'message' => "✅ Created table {$tableName}"];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "❌ Failed to create {$tableName}: " . $e->getMessage()];
    }
}

// Run migration if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $db = getDb();
        
        // Define the tables to create
        $tablesToCreate = [
            'family_album_photos' => "
                CREATE TABLE family_album_photos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) NOT NULL COMMENT 'Original filename',
                    file_path VARCHAR(500) NOT NULL COMMENT 'Path to uploaded photo file',
                    file_size INT DEFAULT 0 COMMENT 'File size in bytes',
                    mime_type VARCHAR(100) DEFAULT NULL COMMENT 'MIME type of the file',
                    caption TEXT DEFAULT NULL COMMENT 'Photo caption or description',
                    scripture_reference VARCHAR(255) DEFAULT NULL COMMENT 'Bible verse reference if applicable',
                    alt_text VARCHAR(255) DEFAULT NULL COMMENT 'Alternative text for accessibility',
                    display_order INT DEFAULT 0 COMMENT 'Order for displaying photos',
                    is_featured BOOLEAN DEFAULT FALSE COMMENT 'Whether this photo is featured',
                    uploaded_by VARCHAR(100) DEFAULT 'admin' COMMENT 'Who uploaded the photo',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_display_order (display_order),
                    INDEX idx_featured (is_featured),
                    INDEX idx_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
                COMMENT='Family album photos with captions and metadata'
            ",
            
            'family_album_access' => "
                CREATE TABLE family_album_access (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    access_code VARCHAR(100) NOT NULL UNIQUE COMMENT 'Unique access code for viewing',
                    visitor_name VARCHAR(255) DEFAULT NULL COMMENT 'Name of the person requesting access',
                    visitor_email VARCHAR(255) DEFAULT NULL COMMENT 'Email of the person requesting access',
                    visitor_message TEXT DEFAULT NULL COMMENT 'Message from visitor requesting access',
                    status ENUM('pending', 'approved', 'denied') DEFAULT 'pending' COMMENT 'Access request status',
                    approved_by VARCHAR(100) DEFAULT NULL COMMENT 'Admin who approved/denied access',
                    expires_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When access expires (NULL = never)',
                    last_accessed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When they last viewed the album',
                    access_count INT DEFAULT 0 COMMENT 'Number of times accessed',
                    notes TEXT DEFAULT NULL COMMENT 'Admin notes about this access request',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_access_code (access_code),
                    INDEX idx_status (status),
                    INDEX idx_expires (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
                COMMENT='Access control for family album viewing'
            "
        ];
        
        // Process each table
        foreach ($tablesToCreate as $tableName => $sql) {
            if (tableExists($db, $tableName)) {
                $messages[] = "ℹ️ <strong>{$tableName}</strong> already exists, skipped";
            } else {
                $result = createTable($db, $tableName, $sql);
                if ($result['success']) {
                    $messages[] = $result['message'];
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
        
        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../uploads/family_album';
        if (!file_exists($uploadDir)) {
            if (mkdir($uploadDir, 0755, true)) {
                $messages[] = "✅ Created uploads directory: /uploads/family_album";
            } else {
                $errors[] = "❌ Failed to create uploads directory";
            }
        } else {
            $messages[] = "ℹ️ Uploads directory already exists";
        }
        
        // Create .htaccess for upload security
        $htaccessPath = $uploadDir . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Family Album Upload Security\n";
            $htaccessContent .= "# Prevent direct access to uploaded files\n";
            $htaccessContent .= "Options -Indexes\n";
            $htaccessContent .= "deny from all\n";
            
            if (file_put_contents($htaccessPath, $htaccessContent)) {
                $messages[] = "✅ Created security .htaccess file";
            } else {
                $errors[] = "❌ Failed to create security .htaccess file";
            }
        }
        
        // Summary message
        if (empty($errors)) {
            $messages[] = "<strong>✅ Family Album migration completed successfully!</strong>";
        } else {
            $errors[] = "<strong>⚠️ Migration completed with errors</strong>";
        }
        
    } catch (Exception $e) {
        $errors[] = "❌ Migration failed: " . $e->getMessage();
    }
}

// Get current database tables
try {
    $db = getDb();
    $stmt = $db->query("SHOW TABLES LIKE 'family_album_%'");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $existingTables = [];
    $errors[] = "⚠️ Could not fetch existing tables: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Naomi Wendot Admin</title>
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
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="flex">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 ml-64">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-plum mb-2">📸 Family Album Migration</h1>
                    <p class="text-gray-600">Create database tables and structure for the Family Album feature</p>
                </div>

                <!-- Feature Overview -->
                <div class="mb-6 bg-gradient-to-r from-rose to-cream border border-gold/30 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold text-plum mb-2">🌟 What this creates:</h2>
                    <ul class="list-disc list-inside text-gray-700 text-sm space-y-1">
                        <li><strong>Photo Storage:</strong> Secure database table for family photos with captions and scripture references</li>
                        <li><strong>Access Control:</strong> Admin-approved viewing system with unique access codes</li>
                        <li><strong>Upload Directory:</strong> Protected file storage with security configurations</li>
                        <li><strong>Scripture Integration:</strong> Each photo can include inspirational Bible verses</li>
                    </ul>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($messages)): ?>
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-6 rounded">
                        <h3 class="text-lg font-semibold text-green-900 mb-3">Migration Results</h3>
                        <ul class="space-y-2">
                            <?php foreach ($messages as $message): ?>
                                <li class="text-green-800"><?php echo $message; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-6 rounded">
                        <h3 class="text-lg font-semibold text-red-900 mb-3">Errors</h3>
                        <ul class="space-y-2">
                            <?php foreach ($errors as $error): ?>
                                <li class="text-red-800"><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Tables to Create -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-plum mb-4">📋 Tables to Create</h2>
                    
                    <div class="space-y-6">
                        <!-- family_album_photos table -->
                        <div class="border-l-4 border-gold pl-4 py-2">
                            <h3 class="font-semibold text-plum text-lg mb-2">📸 family_album_photos</h3>
                            <p class="text-sm text-gray-600 mb-3">Store family photos with captions and metadata</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div><strong>filename:</strong> Original file name</div>
                                <div><strong>file_path:</strong> Server path to photo</div>
                                <div><strong>caption:</strong> Photo description/story</div>
                                <div><strong>scripture_reference:</strong> Bible verse reference</div>
                                <div><strong>alt_text:</strong> Accessibility description</div>
                                <div><strong>display_order:</strong> Photo ordering</div>
                                <div><strong>is_featured:</strong> Featured photo flag</div>
                                <div><strong>file_size, mime_type:</strong> File metadata</div>
                            </div>
                        </div>

                        <!-- family_album_access table -->
                        <div class="border-l-4 border-plum pl-4 py-2">
                            <h3 class="font-semibold text-plum text-lg mb-2">🔐 family_album_access</h3>
                            <p class="text-sm text-gray-600 mb-3">Control who can view the family album</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div><strong>access_code:</strong> Unique viewing code</div>
                                <div><strong>visitor_name:</strong> Visitor's name</div>
                                <div><strong>visitor_email:</strong> Contact email</div>
                                <div><strong>status:</strong> pending/approved/denied</div>
                                <div><strong>expires_at:</strong> Access expiration</div>
                                <div><strong>last_accessed_at:</strong> Last view time</div>
                                <div><strong>access_count:</strong> View counter</div>
                                <div><strong>notes:</strong> Admin notes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Example Scripture Captions -->
                <div class="bg-gradient-to-r from-cream to-rose border border-gold/30 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-plum mb-4">✨ Example Photo Captions</h2>
                    <div class="space-y-4 text-sm">
                        <div class="bg-white/70 p-4 rounded border-l-4 border-gold">
                            <p class="italic text-plum mb-2">"Does marriage work? Yes. Let your marriage be founded in God's word. Rooted in Christ, built in Christ and remaining steadfast in Christ."</p>
                            <p class="text-gold font-medium">— Family Wedding Photo</p>
                        </div>
                        <div class="bg-white/70 p-4 rounded border-l-4 border-plum">
                            <p class="italic text-plum mb-2">"However, each one of you also must love his wife as he loves himself, and the wife must respect her husband."</p>
                            <p class="text-gold font-medium">— Ephesians 5:33 NIV</p>
                        </div>
                    </div>
                </div>

                <!-- Run Migration Button -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-plum mb-4">🚀 Run Migration</h2>
                    <p class="text-gray-600 mb-4">
                        This migration will create the Family Album database structure. It's safe to run multiple times.
                    </p>
                    
                    <form method="POST" onsubmit="return confirm('Create Family Album database tables?');">
                        <button 
                            type="submit" 
                            name="run_migration" 
                            class="bg-plum hover:bg-opacity-90 text-cream font-semibold px-6 py-3 rounded-lg transition-colors flex items-center gap-2"
                        >
                            📸 Create Family Album Tables
                        </button>
                    </form>
                </div>

                <!-- Existing Tables Status -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-plum to-plum/90 text-cream">
                        <h2 class="text-xl font-bold">Current Family Album Tables</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (!empty($existingTables)): ?>
                            <div class="space-y-2">
                                <?php foreach ($existingTables as $table): ?>
                                    <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded">
                                        <span class="text-green-600">✅</span>
                                        <span class="font-medium text-green-800"><?php echo htmlspecialchars($table); ?></span>
                                        <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">exists</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p>No Family Album tables found. Run the migration to create them.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6">
                    <a href="../dashboard.php" class="text-plum hover:text-gold font-medium transition-colors">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>