<?php
require_once 'auth.php';

// Check if user is authenticated and session is valid
if (isAuthenticated() && !isSessionExpired()) {
    // User is authenticated, redirect to dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Only logout if there was an expired session, not if no session exists
    if (isAuthenticated() && isSessionExpired()) {
        logout(); // Clean up expired session data
    }
    header('Location: login.php');
    exit;
}
?>
