<?php
/**
 * Centralized Session Management
 * Prevents multiple session_start() calls
 */

// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
