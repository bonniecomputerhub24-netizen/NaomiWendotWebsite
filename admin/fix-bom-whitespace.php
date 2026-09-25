<?php
/**
 * BOM and Whitespace Cleanup Script
 * Removes BOM and leading/trailing whitespace from PHP files
 * Run this once to fix "headers already sent" issues
 */

$files = [
    __DIR__ . '/../includes/session.php',
    __DIR__ . '/../includes/nav.php',
    __DIR__ . '/../config/db.php',
    __DIR__ . '/../config/constants.php',
    __DIR__ . '/../includes/functions.php',
    __DIR__ . '/../includes/post-functions.php',
    __DIR__ . '/../includes/video-functions.php',
];

$fixed = 0;
$errors = [];

foreach ($files as $file) {
    if (!file_exists($file)) {
        $errors[] = "File not found: $file";
        continue;
    }

    $content = file_get_contents($file);
    $original = $content;

    // Remove BOM (Byte Order Mark)
    $content = str_replace("\xEF\xBB\xBF", '', $content);

    // Remove whitespace before opening <?php tag
    $content = ltrim($content);

    // Remove trailing whitespace after closing ?> tag (if exists)
    if (substr($content, -2) === '?>') {
        // Remove the closing PHP tag - it's not needed and can cause issues
        $content = rtrim(substr($content, 0, -2));
    } else {
        // Just trim trailing whitespace
        $content = rtrim($content);
    }

    // Only write if content changed
    if ($content !== $original) {
        if (file_put_contents($file, $content)) {
            $fixed++;
            echo "✓ Fixed: " . basename($file) . "\n";
        } else {
            $errors[] = "Could not write to: $file";
        }
    } else {
        echo "- OK: " . basename($file) . "\n";
    }
}

echo "\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "  Files checked: " . count($files) . "\n";
echo "  Files fixed: $fixed\n";
echo "  Errors: " . count($errors) . "\n";

if ($errors) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\nDone!\n";
