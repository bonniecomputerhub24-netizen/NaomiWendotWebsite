<?php
/**
 * Authentication System
 * Naomi Wendot Admin Panel
 */

session_start();

// Admin credentials (in production, store hashed password in database)
define('ADMIN_USERNAME', 'naomi');
define('ADMIN_PASSWORD_HASH', password_hash('naomi2024', PASSWORD_DEFAULT)); // Change this password!

/**
 * Check if user is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login - redirect to login page if not authenticated
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/../login.php');
        exit;
    }
}

/**
 * Login user - accepts username or email
 */
function loginAdmin($usernameOrEmail, $password) {
    require_once __DIR__ . '/../../config/db.php';
    
    try {
        $db = getDb();
        
        // Check if input is username or email
        $stmt = $db->prepare("
            SELECT id, username, password, email, full_name 
            FROM admin_users 
            WHERE username = :username OR email = :email 
            LIMIT 1
        ");
        $stmt->execute([
            ':username' => $usernameOrEmail,
            ':email' => $usernameOrEmail
        ]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_full_name'] = $admin['full_name'];
            $_SESSION['admin_login_time'] = time();
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Logout user
 */
function logoutAdmin() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Get greeting based on time of day
 */
function getGreeting() {
    $hour = date('G');
    
    if ($hour >= 5 && $hour < 12) {
        return 'Good Morning';
    } elseif ($hour >= 12 && $hour < 17) {
        return 'Good Afternoon';
    } elseif ($hour >= 17 && $hour < 21) {
        return 'Good Evening';
    } else {
        return 'Good Night';
    }
}

/**
 * Get admin name
 */
function getAdminName() {
    if (isset($_SESSION['admin_full_name']) && !empty($_SESSION['admin_full_name'])) {
        return $_SESSION['admin_full_name'];
    }
    return isset($_SESSION['admin_username']) ? ucfirst($_SESSION['admin_username']) : 'Admin';
}

/**
 * Update admin password
 */
function updateAdminPassword($adminId, $currentPassword, $newPassword) {
    require_once __DIR__ . '/../../config/db.php';
    
    try {
        $db = getDb();
        
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $adminId]);
        $admin = $stmt->fetch();
        
        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Update to new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE admin_users SET password = :password WHERE id = :id");
        $updateStmt->execute([
            ':password' => $newPasswordHash,
            ':id' => $adminId
        ]);
        
        return ['success' => true, 'message' => 'Password updated successfully'];
    } catch (Exception $e) {
        error_log("Password update error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update password'];
    }
}

/**
 * Update admin profile
 */
function updateAdminProfile($adminId, $fullName, $email) {
    require_once __DIR__ . '/../../config/db.php';
    
    try {
        $db = getDb();
        
        $stmt = $db->prepare("
            UPDATE admin_users 
            SET full_name = :full_name, email = :email 
            WHERE id = :id
        ");
        $stmt->execute([
            ':full_name' => $fullName,
            ':email' => $email,
            ':id' => $adminId
        ]);
        
        // Update session
        $_SESSION['admin_full_name'] = $fullName;
        $_SESSION['admin_email'] = $email;
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update profile'];
    }
}
