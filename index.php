
<?php
require_once 'auth.php';

// Check if user is authenticated and session is valid
if (isAuthenticated() && !isSessionExpired()) {
    // User is authenticated, redirect to dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // User is not authenticated or session expired, redirect to login
    logout(); // Clean up any invalid session data
    header('Location: login.php');
    exit;
}
?>
