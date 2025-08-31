<?php
/**
 * Secure Authentication System
 * Handles login, session management, and auto-logout functionality
 */

session_start();

// Master password
define('MASTER_PASSWORD', 'SUREFOUNDATIONGROUPOFSCHOOL2025');

// Session timeout (5 minutes)
define('SESSION_TIMEOUT', 300); // 5 minutes in seconds

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Check if session has expired
 */
function isSessionExpired() {
    if (!isset($_SESSION['last_activity'])) {
        return true;
    }
    
    return (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT;
}

/**
 * Update last activity timestamp
 */
function updateLastActivity() {
    $_SESSION['last_activity'] = time();
}

/**
 * Authenticate user with master password
 */
function authenticate($password) {
    if ($password === MASTER_PASSWORD) {
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
}

/**
 * Require authentication for protected pages
 */
function requireAuth() {
    if (!isAuthenticated() || isSessionExpired()) {
        logout();
        header('Location: login.php');
        exit;
    }
    updateLastActivity();
}

/**
 * Get session info for display
 */
function getSessionInfo() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'login_time' => $_SESSION['login_time'] ?? 0,
        'last_activity' => $_SESSION['last_activity'] ?? 0,
        'time_remaining' => SESSION_TIMEOUT - (time() - ($_SESSION['last_activity'] ?? 0))
    ];
}
?>
