
<?php
require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isAuthenticated() && !isSessionExpired()) {
        updateLastActivity();
        echo json_encode(['success' => true, 'time_remaining' => SESSION_TIMEOUT - (time() - $_SESSION['last_activity'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Session expired']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
