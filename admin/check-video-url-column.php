<?php
/**
 * Quick Diagnostic: Check video_url Column Status
 * Confirms whether video_url column exists and checks for orphaned video posts
 */

require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../config/db.php';

$pageTitle = "Video URL Column Check";

try {
    $db = getDb();
    
    // 1. Check if video_url column exists
    $stmt = $db->prepare("SHOW COLUMNS FROM posts LIKE 'video_url'");
    $stmt->execute();
    $videoUrlColumn = $stmt->fetch();
    
    // 2. Get all columns in posts table
    $stmt = $db->query("SHOW COLUMNS FROM posts");
    $allColumns = $stmt->fetchAll();
    
    // 3. Check for video posts that might be broken
    $stmt = $db->query("
        SELECT 
            id, 
            title, 
            content_type, 
            video_file, 
            video_thumbnail,
            status,
            published_at
        FROM posts 
        WHERE content_type = 'video'
        ORDER BY created_at DESC
    ");
    $videoPosts = $stmt->fetchAll();
    
    // 4. Check for potentially orphaned video posts (video type but no video_file)
    $orphanedVideos = array_filter($videoPosts, function($post) {
        return empty($post['video_file']);
    });
    
} catch (Exception $e) {
    $error = $e->getMessage();
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
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="flex">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 ml-64">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Video URL Column Diagnostic</h1>
                    <p class="text-gray-600">Checking for video_url column and potential data issues</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-6 rounded">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">Error</h3>
                        <p class="text-red-800"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php else: ?>

                    <!-- Result 1: Column Existence Check -->
                    <div class="mb-6 p-6 rounded-lg border-2 <?php echo $videoUrlColumn ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'; ?>">
                        <h2 class="text-xl font-bold mb-3 <?php echo $videoUrlColumn ? 'text-green-900' : 'text-red-900'; ?>">
                            1. SHOW COLUMNS FROM posts LIKE 'video_url'
                        </h2>
                        
                        <?php if ($videoUrlColumn): ?>
                            <div class="bg-green-100 p-4 rounded font-mono text-sm mb-3">
                                <strong class="text-green-900">✓ Column EXISTS</strong>
                            </div>
                            <table class="min-w-full bg-white rounded shadow-sm">
                                <thead class="bg-green-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-green-900">Field</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-green-900">Type</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-green-900">Null</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-green-900">Key</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-green-900">Default</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-green-900">Extra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-4 py-2 border-t"><?php echo htmlspecialchars($videoUrlColumn['Field']); ?></td>
                                        <td class="px-4 py-2 border-t"><?php echo htmlspecialchars($videoUrlColumn['Type']); ?></td>
                                        <td class="px-4 py-2 border-t"><?php echo htmlspecialchars($videoUrlColumn['Null']); ?></td>
                                        <td class="px-4 py-2 border-t"><?php echo htmlspecialchars($videoUrlColumn['Key'] ?? ''); ?></td>
                                        <td class="px-4 py-2 border-t"><?php echo htmlspecialchars($videoUrlColumn['Default'] ?? 'NULL'); ?></td>
                                        <td class="px-4 py-2 border-t"><?php echo htmlspecialchars($videoUrlColumn['Extra'] ?? ''); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="text-green-800 mt-3 font-semibold">
                                ✓ Answer: YES, video_url column exists in posts table
                            </p>
                        <?php else: ?>
                            <div class="bg-red-100 p-4 rounded font-mono text-sm mb-3">
                                <strong class="text-red-900">✗ Column DOES NOT EXIST</strong><br>
                                <span class="text-red-700">Empty set (0 rows returned)</span>
                            </div>
                            <p class="text-red-800 mt-3 font-semibold">
                                ✗ Answer: NO, video_url column is MISSING from posts table
                            </p>
                            <div class="mt-4 bg-yellow-50 border border-yellow-300 p-4 rounded">
                                <p class="text-yellow-800 text-sm">
                                    <strong>⚠️ Issue Detected:</strong> The manage.php script references $_POST['video_url'] and attempts to save it to a video_url column that doesn't exist in the database. This will cause a SQL error when trying to create/update posts with video_source = 'url'.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Result 2: Check for Orphaned Video Posts -->
                    <div class="mb-6 bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">
                            2. Check for Potentially Broken Video Posts
                        </h2>
                        
                        <div class="mb-4">
                            <p class="text-gray-700 mb-2">
                                <strong>Total video posts:</strong> <?php echo count($videoPosts); ?>
                            </p>
                            <p class="text-gray-700 mb-2">
                                <strong>Video posts with video_file:</strong> <?php echo count($videoPosts) - count($orphanedVideos); ?>
                            </p>
                            <p class="text-gray-700">
                                <strong>Video posts WITHOUT video_file:</strong> 
                                <span class="<?php echo count($orphanedVideos) > 0 ? 'text-red-600 font-bold' : 'text-green-600'; ?>">
                                    <?php echo count($orphanedVideos); ?>
                                </span>
                            </p>
                        </div>

                        <?php if (count($orphanedVideos) > 0): ?>
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                                <h3 class="text-red-900 font-semibold mb-2">⚠️ Orphaned Video Posts Found</h3>
                                <p class="text-red-800 text-sm mb-3">
                                    These posts have content_type = 'video' but no video_file. They may have been intended to use video URLs.
                                </p>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white rounded shadow-sm text-sm">
                                        <thead class="bg-red-100">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-semibold text-red-900">ID</th>
                                                <th class="px-3 py-2 text-left font-semibold text-red-900">Title</th>
                                                <th class="px-3 py-2 text-left font-semibold text-red-900">Status</th>
                                                <th class="px-3 py-2 text-left font-semibold text-red-900">Published</th>
                                                <th class="px-3 py-2 text-left font-semibold text-red-900">Impact</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orphanedVideos as $orphan): ?>
                                                <tr class="border-t">
                                                    <td class="px-3 py-2"><?php echo $orphan['id']; ?></td>
                                                    <td class="px-3 py-2"><?php echo htmlspecialchars($orphan['title']); ?></td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 py-1 text-xs rounded <?php echo $orphan['status'] === 'published' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                            <?php echo strtoupper($orphan['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2"><?php echo $orphan['published_at'] ?? 'Not published'; ?></td>
                                                    <td class="px-3 py-2">
                                                        <?php if ($orphan['status'] === 'published'): ?>
                                                            <span class="text-red-700 font-semibold">🔴 LIVE-BREAKING</span>
                                                        <?php else: ?>
                                                            <span class="text-yellow-700">⚠️ Draft/Scheduled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-green-50 border-l-4 border-green-500 p-4">
                                <p class="text-green-800">
                                    ✓ All video posts have a video_file. No orphaned videos detected.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Result 3: All Video Posts Summary -->
                    <?php if (count($videoPosts) > 0): ?>
                        <div class="mb-6 bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">
                                3. All Video Posts
                            </h2>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-700">ID</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Title</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Status</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-700">video_file</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Published</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($videoPosts as $video): ?>
                                            <tr class="border-t hover:bg-gray-50">
                                                <td class="px-3 py-2"><?php echo $video['id']; ?></td>
                                                <td class="px-3 py-2"><?php echo htmlspecialchars($video['title']); ?></td>
                                                <td class="px-3 py-2">
                                                    <span class="px-2 py-1 text-xs rounded <?php 
                                                        echo $video['status'] === 'published' ? 'bg-green-100 text-green-800' : 
                                                            ($video['status'] === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'); 
                                                    ?>">
                                                        <?php echo strtoupper($video['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <?php if ($video['video_file']): ?>
                                                        <span class="text-green-600">✓ <?php echo htmlspecialchars($video['video_file']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-red-600 font-semibold">✗ NULL</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-600"><?php echo $video['published_at'] ?? 'Not published'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-6">
                            <p class="text-blue-800">
                                ℹ️ No video posts found in the database.
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Summary and Recommendations -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Summary</h3>
                        <ul class="list-disc list-inside text-blue-800 space-y-2 text-sm">
                            <li>
                                <strong>video_url column:</strong> 
                                <?php echo $videoUrlColumn ? 'EXISTS ✓' : 'MISSING ✗'; ?>
                            </li>
                            <li>
                                <strong>Orphaned video posts:</strong> 
                                <?php echo count($orphanedVideos); ?>
                                <?php if (count($orphanedVideos) > 0): ?>
                                    <?php 
                                    $publishedOrphans = array_filter($orphanedVideos, function($v) { return $v['status'] === 'published'; });
                                    ?>
                                    (<?php echo count($publishedOrphans); ?> published - <strong class="text-red-700">LIVE-BREAKING</strong>)
                                <?php endif; ?>
                            </li>
                            <li>
                                <strong>Impact:</strong> 
                                <?php if (!$videoUrlColumn): ?>
                                    The video URL feature in manage.php is <strong>NON-FUNCTIONAL</strong> due to missing column.
                                    <?php if (count($orphanedVideos) === 0): ?>
                                        However, no published posts are affected. This is a <strong>dormant unused path</strong>.
                                    <?php else: ?>
                                        <?php if (count($publishedOrphans) > 0): ?>
                                            <strong class="text-red-700">CRITICAL:</strong> Published posts are broken.
                                        <?php else: ?>
                                            Only draft/scheduled posts affected. Not live-breaking yet.
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    System appears functional.
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>

                    <!-- All Columns Reference -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Complete posts Table Columns</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($allColumns as $col): ?>
                                <span class="px-2 py-1 bg-white border border-gray-300 rounded text-xs font-mono <?php echo $col['Field'] === 'video_url' ? 'bg-green-100 border-green-500' : ''; ?>">
                                    <?php echo htmlspecialchars($col['Field']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php endif; ?>

                <!-- Back Button -->
                <div class="mt-6">
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
