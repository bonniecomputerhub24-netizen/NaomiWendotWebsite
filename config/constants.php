<?php
/**
 * Site-wide Constants & Configuration
 * Naomi Wendot Writer & Ministry Website
 */

// Site Identity
define('SITE_NAME', 'Naomi Wendot');
define('SITE_TAGLINE', 'Life Blessed is a Life Shared.');
define('SITE_URL', 'https://naomiwendot.com');
define('SITE_EMAIL', 'info@naomiwendot.com');
define('AUTHOR_NAME', 'Naomi Wendot');

// Contact Information
// Phone number removed - use email only: info@naomiwendot.com

// Brand Colors (Hex)
define('COLOR_PLUM', '#4A1942');
define('COLOR_GOLD', '#D4A017');
define('COLOR_CREAM', '#FFFDF5');
define('COLOR_ROSE', '#FDEAEA');
define('COLOR_CHARCOAL', '#1C1C1C');

// Base URL Configuration - Simplified for Production
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Simply use the host as BASE_URL for production
// The site is in the root of the domain (public_html)
define('BASE_URL', $protocol . $host . '/');

// Navigation Menu
define('NAV_MENU', [
    ['label' => 'Home', 'path' => '/index.php'],
    ['label' => 'About', 'path' => '/public/about.php'],
    ['label' => 'Body of Work', 'path' => '/public/body-of-work.php'],
    ['label' => 'Nature & Bible Verses', 'path' => '/public/nature-bible-verses/index.php'],
    ['label' => 'Contact', 'path' => '/public/contact.php']
]);

// Body of Work Categories
define('BODY_OF_WORK_CATEGORIES', [
    [
        'slug' => 'poems',
        'label' => 'Poems',
        'icon' => 'quill',
        'description' => 'Freestyle, heartfelt expressions of faith, hope, and everyday beauty'
    ],
    [
        'slug' => 'articles',
        'label' => 'Articles',
        'icon' => 'book-open',
        'description' => 'Thoughtful reflections on life, faith, and personal growth'
    ],
    [
        'slug' => 'daily-inspirations',
        'label' => 'Daily Inspirations',
        'icon' => 'sunrise',
        'description' => 'Short, Holy Spirit-inspired messages to start the reader\'s day with hope'
    ],
    [
        'slug' => 'stories',
        'label' => 'Stories',
        'icon' => 'leaf',
        'description' => 'Narratives of faith, courage, and transformation'
    ],
    [
        'slug' => 'testimonies',
        'label' => 'Testimonies',
        'icon' => 'praying-hands',
        'description' => 'Real stories of God\'s grace, protection, and answered prayer'
    ]
]);
