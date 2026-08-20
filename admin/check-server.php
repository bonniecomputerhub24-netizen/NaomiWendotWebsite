<?php
/**
 * Server Capability Diagnostic Script
 * Checks video processing capabilities on Truehost Cloud
 */

require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$pageTitle = "Server Diagnostics";

// Function to format bytes to human readable
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Function to convert ini value to bytes
function iniToBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

// Collect diagnostic data
$diagnostics = [];

// 1. PHP Version
$diagnostics['PHP Version'] = [
    'value' => phpversion(),
    'status' => version_compare(phpversion(), '7.4', '>=') ? 'good' : 'warning',
    'note' => version_compare(phpversion(), '7.4', '>=') ? 'Modern PHP version' : 'Older PHP version'
];

// 2. Memory Limit
$memoryLimit = ini_get('memory_limit');
$memoryBytes = iniToBytes($memoryLimit);
$diagnostics['Memory Limit'] = [
    'value' => $memoryLimit,
    'status' => $memoryBytes >= 128 * 1024 * 1024 ? 'good' : 'warning',
    'note' => $memoryBytes >= 128 * 1024 * 1024 ? 'Sufficient for video processing' : 'May be low for large videos'
];

// 3. Upload Max Filesize
$uploadMax = ini_get('upload_max_filesize');
$uploadBytes = iniToBytes($uploadMax);
$diagnostics['Upload Max Filesize'] = [
    'value' => $uploadMax,
    'status' => $uploadBytes >= 100 * 1024 * 1024 ? 'good' : 'warning',
    'note' => $uploadBytes >= 100 * 1024 * 1024 ? 'Can handle large video uploads' : 'Limited video upload size'
];

// 4. Post Max Size
$postMax = ini_get('post_max_size');
$postBytes = iniToBytes($postMax);
$diagnostics['Post Max Size'] = [
    'value' => $postMax,
    'status' => $postBytes >= 100 * 1024 * 1024 ? 'good' : 'warning',
    'note' => $postBytes >= 100 * 1024 * 1024 ? 'Can handle large video posts' : 'Limited post size'
];

// 5. Max Execution Time
$maxExecTime = ini_get('max_execution_time');
$diagnostics['Max Execution Time'] = [
    'value' => $maxExecTime == 0 ? 'Unlimited' : $maxExecTime . ' seconds',
    'status' => ($maxExecTime == 0 || $maxExecTime >= 300) ? 'good' : 'warning',
    'note' => ($maxExecTime == 0 || $maxExecTime >= 300) ? 'Sufficient for video processing' : 'May timeout on large videos'
];

// 6. Check disabled functions
$disabledFunctions = ini_get('disable_functions');
$disabledArray = array_map('trim', explode(',', $disabledFunctions));
$execDisabled = in_array('exec', $disabledArray);
$shellExecDisabled = in_array('shell_exec', $disabledArray);

$diagnostics['exec() Function'] = [
    'value' => $execDisabled ? 'DISABLED' : 'ENABLED',
    'status' => $execDisabled ? 'error' : 'good',
    'note' => $execDisabled ? 'Cannot use exec() for video processing' : 'Can execute shell commands'
];

$diagnostics['shell_exec() Function'] = [
    'value' => $shellExecDisabled ? 'DISABLED' : 'ENABLED',
    'status' => $shellExecDisabled ? 'error' : 'good',
    'note' => $shellExecDisabled ? 'Cannot use shell_exec() for video processing' : 'Can execute shell commands'
];

// 7. Check for FFmpeg (only if shell functions are enabled)
$ffmpegAvailable = false;
$ffmpegVersion = 'N/A';
$ffmpegStatus = 'error';

if (!$shellExecDisabled) {
    // Try to get FFmpeg version
    $output = @shell_exec('ffmpeg -version 2>&1');
    if ($output && stripos($output, 'ffmpeg version') !== false) {
        $ffmpegAvailable = true;
        // Extract version number
        preg_match('/ffmpeg version ([^\s]+)/', $output, $matches);
        $ffmpegVersion = $matches[1] ?? 'Installed';
        $ffmpegStatus = 'good';
    } else {
        $ffmpegVersion = 'NOT FOUND';
        $ffmpegStatus = 'error';
    }
} else {
    $ffmpegVersion = 'Cannot check (shell_exec disabled)';
    $ffmpegStatus = 'error';
}

$diagnostics['FFmpeg'] = [
    'value' => $ffmpegVersion,
    'status' => $ffmpegStatus,
    'note' => $ffmpegAvailable ? 'Server-side video compression possible' : 'Server-side video processing NOT possible'
];

// 8. Check for file upload errors
$diagnostics['File Uploads'] = [
    'value' => ini_get('file_uploads') ? 'ENABLED' : 'DISABLED',
    'status' => ini_get('file_uploads') ? 'good' : 'error',
    'note' => ini_get('file_uploads') ? 'File uploads allowed' : 'File uploads disabled'
];

// 9. Temp directory
$tempDir = sys_get_temp_dir();
$tempWritable = is_writable($tempDir);
$diagnostics['Temp Directory'] = [
    'value' => $tempDir,
    'status' => $tempWritable ? 'good' : 'warning',
    'note' => $tempWritable ? 'Writable (needed for processing)' : 'Not writable - may cause issues'
];

// 10. Available disk space
$freeSpace = @disk_free_space(sys_get_temp_dir());
$diagnostics['Free Disk Space'] = [
    'value' => $freeSpace ? formatBytes($freeSpace) : 'Unknown',
    'status' => ($freeSpace && $freeSpace > 1024 * 1024 * 1024) ? 'good' : 'warning',
    'note' => ($freeSpace && $freeSpace > 1024 * 1024 * 1024) ? 'Sufficient space' : 'Limited disk space'
];

// Overall recommendation
$canProcessVideo = !$shellExecDisabled && $ffmpegAvailable;
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
        .status-good { background-color: #dcfce7; color: #166534; }
        .status-warning { background-color: #fef3c7; color: #92400e; }
        .status-error { background-color: #fee2e2; color: #991b1b; }
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Server Diagnostics</h1>
                    <p class="text-gray-600">Checking server capabilities for video processing and uploads</p>
                </div>

                <!-- Overall Status Banner -->
                <div class="mb-6 p-6 rounded-lg <?php echo $canProcessVideo ? 'bg-green-100 border-2 border-green-500' : 'bg-red-100 border-2 border-red-500'; ?>">
                    <h2 class="text-xl font-bold mb-2 <?php echo $canProcessVideo ? 'text-green-800' : 'text-red-800'; ?>">
                        <?php if ($canProcessVideo): ?>
                            ✓ Server-Side Video Processing: AVAILABLE
                        <?php else: ?>
                            ✗ Server-Side Video Processing: NOT AVAILABLE
                        <?php endif; ?>
                    </h2>
                    <p class="<?php echo $canProcessVideo ? 'text-green-700' : 'text-red-700'; ?>">
                        <?php if ($canProcessVideo): ?>
                            FFmpeg is installed and shell commands are enabled. You can build features for server-side video compression, thumbnail generation, and format conversion.
                        <?php else: ?>
                            <?php if ($shellExecDisabled): ?>
                                Shell execution functions are disabled on this server. Server-side video processing is not possible. You'll need to rely on client-side compression or pre-compressed uploads.
                            <?php elseif (!$ffmpegAvailable): ?>
                                FFmpeg is not installed on this server. You can request the hosting provider to install it, or use pre-compressed video uploads instead.
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Diagnostics Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/4">
                                    Parameter
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/4">
                                    Value
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-1/12">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-5/12">
                                    Notes
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($diagnostics as $param => $data): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($param); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-mono">
                                        <?php echo htmlspecialchars($data['value']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full status-<?php echo $data['status']; ?>">
                                            <?php echo strtoupper($data['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($data['note']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Additional Information -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Recommendations</h3>
                    <ul class="list-disc list-inside text-blue-800 space-y-2">
                        <?php if ($canProcessVideo): ?>
                            <li>You can implement automatic video compression and thumbnail generation</li>
                            <li>Consider setting up a background job queue for processing large videos</li>
                            <li>Implement progress indicators for video uploads and processing</li>
                        <?php else: ?>
                            <li><strong>Use client-side compression:</strong> Implement JavaScript libraries like ffmpeg.wasm for browser-based compression</li>
                            <li><strong>Accept pre-compressed videos:</strong> Require users to upload optimized videos (MP4, H.264 codec)</li>
                            <li><strong>Set file size limits:</strong> Restrict uploads to reasonable sizes (e.g., 50MB max)</li>
                            <li><strong>Use video poster images:</strong> Require thumbnail uploads separately</li>
                            <?php if (!$ffmpegAvailable && !$shellExecDisabled): ?>
                                <li><strong>Request FFmpeg installation:</strong> Contact Truehost Cloud support to install FFmpeg on your server</li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Disabled Functions Info (if any) -->
                <?php if (!empty($disabledFunctions)): ?>
                <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded">
                    <h3 class="text-lg font-semibold text-yellow-900 mb-2">Disabled PHP Functions</h3>
                    <p class="text-yellow-800 text-sm mb-2">The following functions are disabled on this server:</p>
                    <code class="block bg-yellow-100 p-3 rounded text-xs text-yellow-900 overflow-x-auto">
                        <?php echo htmlspecialchars($disabledFunctions); ?>
                    </code>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
