<?php
/**
 * Family Member Logout
 */

session_start();

// Clear family member session data
unset($_SESSION['family_member_id']);
unset($_SESSION['family_member_username']);
unset($_SESSION['family_member_name']);
unset($_SESSION['family_member_role']);
unset($_SESSION['family_member_permissions']);

// Destroy session if no other data exists
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect to login page with logout message
header('Location: access?logged_out=1');
exit;
?>