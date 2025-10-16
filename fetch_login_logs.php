
<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

header('Content-Type: application/json');

try {
    $cbt = createConnection('cbt');
    
    // Get filter parameters
    $date = $_GET['date'] ?? '';
    $user = $_GET['user'] ?? '';
    $role = $_GET['role'] ?? '';
    $limit = intval($_GET['limit'] ?? 10);
    $offset = intval($_GET['offset'] ?? 0);
    
    // Build query
    $sql = "SELECT * FROM login_logs WHERE 1=1";
    $params = [];
    
    if ($date) {
        $sql .= " AND DATE(login_time) = ?";
        $params[] = $date;
    }
    
    if ($user) {
        $sql .= " AND username LIKE ?";
        $params[] = "%$user%";
    }
    
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    
    $sql .= " ORDER BY login_time DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $cbt->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
    echo json_encode($logs);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>
