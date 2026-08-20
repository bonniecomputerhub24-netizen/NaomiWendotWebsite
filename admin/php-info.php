<?php
/**
 * PHP Configuration Check
 * Shows current PHP settings relevant to video uploads
 * DELETE THIS FILE AFTER CHECKING!
 */

require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Configuration - Naomi Wendot Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>⚠️ Security Warning:</strong> Delete this file after checking configuration!
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">PHP Configuration for Video Uploads</h1>
            
            <div class="space-y-4">
                <div class="border-b pb-3">
                    <h3 class="font-semibold text-gray-700">Upload Limits</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div class="text-gray-600">upload_max_filesize:</div>
                        <div class="font-mono"><?php echo ini_get('upload_max_filesize'); ?></div>
                        
                        <div class="text-gray-600">post_max_size:</div>
                        <div class="font-mono"><?php echo ini_get('post_max_size'); ?></div>
                        
                        <div class="text-gray-600">memory_limit:</div>
                        <div class="font-mono"><?php echo ini_get('memory_limit'); ?></div>
                    </div>
                </div>

                <div class="border-b pb-3">
                    <h3 class="font-semibold text-gray-700">Execution Limits</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div class="text-gray-600">max_execution_time:</div>
                        <div class="font-mono"><?php echo ini_get('max_execution_time'); ?> seconds</div>
                        
                        <div class="text-gray-600">max_input_time:</div>
                        <div class="font-mono"><?php echo ini_get('max_input_time'); ?> seconds</div>
                    </div>
                </div>

                <div class="border-b pb-3">
                    <h3 class="font-semibold text-gray-700">Function Availability</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div class="text-gray-600">exec() function:</div>
                        <div class="font-mono">
                            <?php echo function_exists('exec') ? '<span class="text-green-600">✓ Available</span>' : '<span class="text-red-600">✗ Disabled</span>'; ?>
                        </div>
                        
                        <div class="text-gray-600">shell_exec() function:</div>
                        <div class="font-mono">
                            <?php echo function_exists('shell_exec') ? '<span class="text-green-600">✓ Available</span>' : '<span class="text-red-600">✗ Disabled</span>'; ?>
                        </div>
                    </div>
                </div>

                <div class="border-b pb-3">
                    <h3 class="font-semibold text-gray-700">FFmpeg Availability</h3>
                    <div class="mt-2 text-sm">
                        <?php
                        $ffmpegAvailable = false;
                        if (function_exists('exec')) {
                            @exec('ffmpeg -version 2>&1', $output, $returnCode);
                            $ffmpegAvailable = ($returnCode === 0);
                        }
                        ?>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="text-gray-600">FFmpeg:</div>
                            <div class="font-mono">
                                <?php echo $ffmpegAvailable ? '<span class="text-green-600">✓ Installed</span>' : '<span class="text-yellow-600">⚠ Not installed or exec() disabled</span>'; ?>
                            </div>
                        </div>
                        <?php if (!$ffmpegAvailable): ?>
                        <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs">
                            <strong>Note:</strong> Automatic thumbnail generation won't work without FFmpeg and exec(). 
                            Users can upload a Featured Image to use as video thumbnail instead.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-700">Recommendations</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-2 space-y-1">
                        <?php
                        $uploadMax = ini_get('upload_max_filesize');
                        $postMax = ini_get('post_max_size');
                        $memLimit = ini_get('memory_limit');
                        $maxExec = ini_get('max_execution_time');
                        
                        if (intval($uploadMax) < 100) {
                            echo '<li class="text-red-600">⚠ upload_max_filesize should be at least 100M for video uploads</li>';
                        } else {
                            echo '<li class="text-green-600">✓ upload_max_filesize is adequate</li>';
                        }
                        
                        if (intval($postMax) < 105) {
                            echo '<li class="text-red-600">⚠ post_max_size should be at least 105M (slightly larger than upload_max_filesize)</li>';
                        } else {
                            echo '<li class="text-green-600">✓ post_max_size is adequate</li>';
                        }
                        
                        if (intval($maxExec) < 300) {
                            echo '<li class="text-yellow-600">⚠ max_execution_time should be at least 300 seconds (5 minutes) for large uploads</li>';
                        } else {
                            echo '<li class="text-green-600">✓ max_execution_time is adequate</li>';
                        }
                        
                        if (!function_exists('exec')) {
                            echo '<li class="text-yellow-600">⚠ exec() is disabled - automatic thumbnails won\'t be generated</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
            <strong>To increase limits:</strong> Edit your <code>php.ini</code> file or add directives to <code>.htaccess</code> file (already added in admin folder).
            Contact your hosting provider if you can't modify these settings.
        </div>

        <div class="mt-4 text-center">
            <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
