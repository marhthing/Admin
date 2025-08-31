<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

header('Content-Type: application/json');

try {
    $stats = [
        'total_users' => 0,
        'total_classes' => 0,
        'total_sessions' => 0,
        'last_sync' => 'Never'
    ];
    
    // Get user count from CBT database
    $cbt_conn = createConnection('cbt');
    if ($cbt_conn) {
        $result = $cbt_conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_users'] = $row['count'];
        }
        
        $result = $cbt_conn->query("SELECT COUNT(*) as count FROM class_levels");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_classes'] = $row['count'];
        }
        
        $result = $cbt_conn->query("SELECT COUNT(*) as count FROM sessions");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_sessions'] = $row['count'];
        }
        
        // Check if migration has ever run by looking for any users with hashed passwords
        $result = $cbt_conn->query("SELECT MAX(created_at) as last_update FROM users WHERE password LIKE '$2y$%' LIMIT 1");
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['last_update']) {
                $stats['last_sync'] = date('M j, H:i', strtotime($row['last_update']));
            }
        }
        
        $cbt_conn = null;
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>