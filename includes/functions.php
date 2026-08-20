<?php
/**
 * Helper Functions
 * Naomi Wendot Writer & Ministry Website
 */

/**
 * Escape and sanitize output for HTML
 * 
 * @param string $string Raw string to escape
 * @return string HTML-safe string
 */
function e($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date to human-readable format
 * 
 * @param string|null $date Date string
 * @param string $format Output format (default: 'F j, Y')
 * @return string Formatted date or dash if null
 */
function formatDate($date, $format = 'F j, Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '—';
    }
    
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        return '—';
    }
}

/**
 * Truncate string with ellipsis
 * 
 * @param string $string String to truncate
 * @param int $length Maximum length
 * @param string $append Suffix to add (default: '...')
 * @return string Truncated string
 */
function truncate($string, $length = 100, $append = '...') {
    if (mb_strlen($string) <= $length) {
        return $string;
    }
    
    $truncated = mb_substr($string, 0, $length);
    
    // Try to break at last space to avoid cutting words
    $lastSpace = mb_strrpos($truncated, ' ');
    if ($lastSpace !== false) {
        $truncated = mb_substr($truncated, 0, $lastSpace);
    }
    
    return $truncated . $append;
}

// WhatsApp function removed - contact via email only

/**
 * Determine if current page matches path and return active CSS classes
 * 
 * @param string $path Path to compare against current URI
 * @return string Tailwind classes for active state or empty string
 */
function activeNavClass($path) {
    $currentUri = $_SERVER['REQUEST_URI'];
    
    // Normalize paths for comparison
    $currentPath = parse_url($currentUri, PHP_URL_PATH);
    $comparePath = parse_url($path, PHP_URL_PATH);
    
    // Check for exact match or if current path starts with the nav path (for sections)
    if ($currentPath === $comparePath || strpos($currentPath, $comparePath) === 0) {
        return 'border-b-2 border-gold text-gold';
    }
    
    return '';
}

/**
 * Get correct base path relative to current file location
 * Returns the appropriate ../ prefix for assets and includes
 * 
 * @return string Relative path prefix
 */
function basePath() {
    $currentFile = $_SERVER['SCRIPT_FILENAME'];
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    
    // Get relative path from document root
    $relativePath = str_replace($docRoot, '', dirname($currentFile));
    
    // Normalize directory separators
    $relativePath = str_replace('\\', '/', $relativePath);
    
    // For files in public/ directory (but not body-of-work inside public)
    if (strpos($relativePath, '/public') !== false && strpos($relativePath, '/body-of-work') === false) {
        return '../';
    }
    
    // For files in shop/ directory
    if (strpos($relativePath, '/shop') !== false) {
        return '../';
    }
    
    // For files in body-of-work/, nature-bible-verses/, verses/ or similar subdirectories
    if (strpos($relativePath, '/body-of-work') !== false || 
        strpos($relativePath, '/nature-bible-verses') !== false ||
        strpos($relativePath, '/verses') !== false) {
        return '../';
    }
    
    // For admin and its subdirectories
    if (strpos($relativePath, '/admin') !== false) {
        // Count depth from admin folder
        $pathAfterAdmin = substr($relativePath, strpos($relativePath, '/admin') + 6);
        $depth = substr_count($pathAfterAdmin, '/');
        return str_repeat('../', $depth + 1);
    }
    
    // Default to same directory (for root level files)
    return './';
}

/**
 * Get full asset URL
 * 
 * @param string $assetPath Path relative to assets folder (e.g., 'css/custom.css')
 * @return string Full asset URL
 */
function asset($assetPath) {
    return BASE_URL . 'assets/' . ltrim($assetPath, '/');
}

/**
 * Get full URL for a site path
 * 
 * @param string $path Site path (e.g., '/public/about.php')
 * @return string Full URL
 */
function url($path) {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Redirect to another page
 * 
 * @param string $path Path to redirect to
 * @param int $statusCode HTTP status code (default: 302)
 */
function redirect($path, $statusCode = 302) {
    header('Location: ' . url($path), true, $statusCode);
    exit;
}

/**
 * Check if user is logged in (for admin area)
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require login (redirect to login page if not authenticated)
 * 
 * @param string $redirectTo Where to redirect if not logged in
 */
function requireLogin($redirectTo = '/admin/login.php') {
    if (!isLoggedIn()) {
        redirect($redirectTo);
    }
}
