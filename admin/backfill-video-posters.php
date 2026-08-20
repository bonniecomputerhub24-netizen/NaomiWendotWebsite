<?php
/**
 * ONE-TIME SCRIPT: Backfill Video Posters
 * Generates cover frame images for existing video posts that don't have one
 * 
 * Run this once after deploying the auto-capture fix
 */

require_once __DIR__ . '/../config/db.php';

// This script requires ffmpeg or manual re-capture via Edit modal
// Since server has no ffmpeg, output instructions for manual fix

$stmt = $pdo->query("
    SELECT id, title, video_file, video_poster 
    FROM posts 
    WHERE content_type = 'video' 
    AND (video_poster IS NULL OR video_poster = '')
");

$postsNeedingPosters = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Backfill Video Posters</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .post { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #4A1942; }
        .error { color: #c00; }
        .success { color: #080; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>🎥 Video Poster Backfill Status</h1>
    
    <div class="info">
        <strong>ℹ️ Simple Fix Available</strong><br>
        Video posts without cover frames can be fixed quickly using the Edit modal:<br><br>
        <ol>
            <li>Go to <strong>Content → Manage Posts</strong> in the admin</li>
            <li>Click <strong>"Edit"</strong> on each video post listed below</li>
            <li>The video will auto-load in the Edit modal preview</li>
            <li>Pause the video at your preferred frame (or leave at 1 second)</li>
            <li>Click <strong>"Choose Cover Frame"</strong> to capture that moment as the thumbnail</li>
            <li>Click <strong>"Update Post"</strong> to save</li>
        </ol>
        <strong>Note:</strong> New videos uploaded after the auto-capture fix will automatically capture a cover frame at 1 second — no manual action needed for future uploads.
    </div>
    
    <?php if (empty($postsNeedingPosters)): ?>
        <p class="success">✅ All video posts have cover frames! No action needed.</p>
    <?php else: ?>
        <h2>Posts Needing Cover Frames (<?php echo count($postsNeedingPosters); ?>)</h2>
        <?php foreach ($postsNeedingPosters as $post): ?>
            <div class="post">
                <strong>ID <?php echo $post['id']; ?>:</strong> <?php echo htmlspecialchars($post['title']); ?><br>
                <small>Video file: <?php echo htmlspecialchars($post['video_file']); ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc;">
        <a href="content/manage.php">← Go to Manage Posts</a>
    </p>
</body>
</html>
