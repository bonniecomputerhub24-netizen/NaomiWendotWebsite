<?php
/**
 * Root Index - Redirects to Main Public Homepage
 * Naomi Wendot Writer & Ministry Website
 */

// Determine if we're in a subdirectory or root
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Build full URL to public/index.php
$redirectUrl = $protocol . $host . '/public/index.php';

// Redirect to the main public homepage
header('Location: ' . $redirectUrl, true, 302);
exit;
