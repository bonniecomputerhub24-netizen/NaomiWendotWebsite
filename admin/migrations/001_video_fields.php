<?php
/**
 * Migration: Add Video Metadata Fields to Posts Table
 * File: 001_video_fields.php
 * 
 * Adds support for richer video metadata:
 * - video_poster: Path to manually-chosen cover image
 * - video_orientation: Video aspect ratio (landscape/portrait/square)
 * - video_views: View counter for videos
 * 
 * Safe to re-run - checks column existence before adding
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../../config/db.php';

$pageTitle = "Video Fields Migration";
$messages = [];
$errors = [];

// Function to check if column exists
function columnExists($db, $tableName, $columnName) {
    try {
        $stmt = $db->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE :columnName");
        $stmt->execute([':columnName' => $columnName]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to add column safely
function addColumn($db, $tableName, $columnName, $columnDefinition) {
    try {
        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnDefinition}";
        $db->exec($sql);
        return ['success' => true, 'message' => "✅ Added {$columnName}"];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "❌ Failed to add {$columnName}: " . $e->getMessage()];
    }
}

// Run migration if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $db = getDb();
        $tableName = 'posts';
        
        // Define the columns to add
        $columnsToAdd = [
            'video_poster' => [
                'definition' => "VARCHAR(255) NULL COMMENT 'Path to manually-chosen cover image for video'",
                'description' => 'Path to a manually-chosen cover image'
            ],
            'video_orientation' => [
                'definition' => "ENUM('landscape','portrait','square') DEFAULT 'landscape' COMMENT 'Video aspect ratio orientation'",
                'description' => 'Video orientation (landscape/portrait/square)'
            ],
            'video_views' => [
                'definition' => "INT DEFAULT 0 COMMENT 'Number of times video has been viewed'",
                'description' => 'View counter for videos'
            ]
        ];
        
        // Process each column
        foreach ($columnsToAdd as $columnName => $columnInfo) {
            if (columnExists($db, $tableName, $columnName)) {
                $messages[] = "ℹ️ <strong>{$columnName}</strong> already exists, skipped";
            } else {
                $result = addColumn($db, $tableName, $columnName, $columnInfo['definition']);
                if ($result['success']) {
                    $messages[] = $result['message'] . " — <em>{$columnInfo['description']}</em>";
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
        
        // Summary message
        if (empty($errors)) {
            $messages[] = "<strong>✅ Migration completed successfully!</strong>";
        } else {
            $errors[] = "<strong>⚠️ Migration completed with errors</strong>";
        }
        
    } catch (Exception $e) {
        $errors[] = "❌ Migration failed: " . $e->getMessage();
    }
}

// Get current table structure
try {
    $db = getDb();
    $stmt = $db->query("SHOW COLUMNS FROM posts");
    $currentColumns = $stmt->fetchAll();
} catch (PDOException $e) {
    $currentColumns = [];
    $errors[] = "⚠️ Could not fetch current table structure: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Naomi Wendot Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Video Fields Migration</h1>
                    <p class="text-gray-600">Add video metadata fields to the posts table</p>
                </div>

                <!-- Important Note About Server Capabilities -->
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded">
                    <h2 class="text-lg font-semibold text-yellow-900 mb-2">📌 Server Capability Note</h2>
                    <p class="text-yellow-800 mb-2">
                        <strong>FFmpeg is unavailable on this host.</strong> This migration only adds database schema for video metadata.
                    </p>
                    <ul class="list-disc list-inside text-yellow-800 text-sm space-y-1">
                        <li>No server-side video compression will run</li>
                        <li>No automatic thumbnail generation will occur</li>
                        <li>Video processing will rely on client-side tools or pre-compressed uploads</li>
                        <li>Poster images must be uploaded manually or generated client-side</li>
                    </ul>
                    <p class="text-yellow-700 text-sm mt-2">✓ Understood - This is schema-only, no processing logic included.</p>
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

                <!-- Migration Details -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Fields to Add</h2>
                    
                    <div class="space-y-4">
                        <!-- video_poster -->
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <h3 class="font-semibold text-gray-900">video_poster</h3>
                            <p class="text-sm text-gray-600">Type: <code class="bg-gray-100 px-2 py-1 rounded">VARCHAR(255) NULL</code></p>
                            <p class="text-sm text-gray-600 mt-1">Purpose: Path to a manually-chosen cover image for videos (e.g., "/uploads/posters/video-thumb.jpg")</p>
                        </div>

                        <!-- video_orientation -->
                        <div class="border-l-4 border-purple-500 pl-4 py-2">
                            <h3 class="font-semibold text-gray-900">video_orientation</h3>
                            <p class="text-sm text-gray-600">Type: <code class="bg-gray-100 px-2 py-1 rounded">ENUM('landscape','portrait','square') DEFAULT 'landscape'</code></p>
                            <p class="text-sm text-gray-600 mt-1">Purpose: Track video aspect ratio for proper display formatting</p>
                        </div>

                        <!-- video_views -->
                        <div class="border-l-4 border-green-500 pl-4 py-2">
                            <h3 class="font-semibold text-gray-900">video_views</h3>
                            <p class="text-sm text-gray-600">Type: <code class="bg-gray-100 px-2 py-1 rounded">INT DEFAULT 0</code></p>
                            <p class="text-sm text-gray-600 mt-1">Purpose: Counter for tracking video view statistics</p>
                        </div>
                    </div>
                </div>

                <!-- Run Migration Button -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Run Migration</h2>
                    <p class="text-gray-600 mb-4">
                        This migration is safe to run multiple times. Existing columns will be skipped automatically.
                    </p>
                    
                    <form method="POST" onsubmit="return confirm('Run migration to add video metadata fields?');">
                        <button 
                            type="submit" 
                            name="run_migration" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors"
                        >
                            🚀 Run Migration
                        </button>
                    </form>
                </div>

                <!-- Current Table Structure -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-100 border-b">
                        <h2 class="text-xl font-bold text-gray-900">Current Posts Table Structure</h2>
                    </div>
                    
                    <?php if (!empty($currentColumns)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Null</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($currentColumns as $column): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                <?php 
                                                $isVideoField = in_array($column['Field'], ['video_poster', 'video_orientation', 'video_views']);
                                                echo $isVideoField ? '<span class="text-green-600 font-bold">🎥 ' : '';
                                                echo htmlspecialchars($column['Field']); 
                                                echo $isVideoField ? '</span>' : '';
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600 font-mono"><?php echo htmlspecialchars($column['Type']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($column['Null']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($column['Key']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($column['Default'] ?? 'NULL'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="px-6 py-4 text-gray-500">
                            Unable to load table structure
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Back Button -->
                <div class="mt-6">
                    <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
