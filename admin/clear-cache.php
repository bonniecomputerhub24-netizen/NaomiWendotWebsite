<?php
/**
 * Clear Performance Caches
 * Run this after database schema changes or if experiencing issues
 */

require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$cleared = [];
$errors = [];

// Clear posts table columns cache
$cacheFile1 = sys_get_temp_dir() . '/posts_table_columns_v2.json';
if (file_exists($cacheFile1)) {
    if (unlink($cacheFile1)) {
        $cleared[] = 'Posts table columns cache';
    } else {
        $errors[] = 'Could not delete posts columns cache';
    }
}

// Clear navigation categories cache
$cacheFile2 = sys_get_temp_dir() . '/nav_categories.json';
if (file_exists($cacheFile2)) {
    if (unlink($cacheFile2)) {
        $cleared[] = 'Navigation categories cache';
    } else {
        $errors[] = 'Could not delete nav categories cache';
    }
}

// Clear any other temp cache files
$tempDir = sys_get_temp_dir();
$pattern = $tempDir . '/*_cache_*.json';
foreach (glob($pattern) as $file) {
    if (unlink($file)) {
        $cleared[] = basename($file);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Cache - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        plum: '#4A1942',
                        gold: '#D4A017',
                        cream: '#FFFDF5'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="inline-block p-4 bg-plum rounded-full mb-4">
                    <svg class="w-12 h-12 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-plum mb-2">Cache Cleared!</h1>
                <p class="text-gray-600">Performance caches have been refreshed</p>
            </div>

            <?php if (!empty($cleared)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-green-800 mb-2">✅ Cleared Successfully:</h3>
                        <ul class="text-sm text-green-700 space-y-1">
                            <?php foreach ($cleared as $item): ?>
                                <li>• <?php echo htmlspecialchars($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <svg class="h-6 w-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800 mb-1">ℹ️ No Caches Found</h3>
                        <p class="text-sm text-blue-700">Either caches were already cleared or they haven't been created yet.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <svg class="h-6 w-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800 mb-2">⚠️ Errors:</h3>
                        <ul class="text-sm text-red-700 space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li>• <?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-plum mb-3">ℹ️ What was cleared?</h3>
                <ul class="text-sm text-gray-700 space-y-2">
                    <li>
                        <strong>Posts Table Columns Cache</strong><br>
                        <span class="text-xs text-gray-600">Stores which columns exist in the posts table (refreshes automatically after 1 hour)</span>
                    </li>
                    <li>
                        <strong>Navigation Categories Cache</strong><br>
                        <span class="text-xs text-gray-600">Stores category list for navigation menu (refreshes automatically after 5 minutes)</span>
                    </li>
                </ul>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">When to clear cache:</p>
                        <ul class="space-y-1 ml-4 list-disc">
                            <li>After adding new columns to the database</li>
                            <li>After updating category structure</li>
                            <li>If pages are showing outdated data</li>
                            <li>If experiencing 508 errors (as troubleshooting step)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <a 
                    href="dashboard.php" 
                    class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all text-center"
                >
                    ← Back to Dashboard
                </a>
                <a 
                    href="content/manage.php" 
                    class="flex-1 px-6 py-3 bg-plum text-white rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center"
                >
                    Manage Content →
                </a>
            </div>

            <div class="mt-6 text-center text-xs text-gray-500">
                <p>Cache will rebuild automatically on next page load</p>
            </div>
        </div>
    </div>
</body>
</html>
