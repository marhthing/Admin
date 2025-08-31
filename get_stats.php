<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $stats = [
        'total_users' => 0,
        'total_classes' => 0,
        'total_sessions' => 0,
        'total_subjects' => 0,
        'last_sync' => 'Never'
    ];
    
    // Get statistics from CBT database using PDO
    $cbt_conn = createConnection('cbt');
    if ($cbt_conn) {
        // Get total users count
        $stmt = $cbt_conn->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) {
            $stats['total_users'] = $result['count'];
        }
        
        // Get total classes count
        $stmt = $cbt_conn->prepare("SELECT COUNT(*) as count FROM class_levels");
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) {
            $stats['total_classes'] = $result['count'];
        }
        
        // Get total sessions count
        $stmt = $cbt_conn->prepare("SELECT COUNT(*) as count FROM sessions");
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) {
            $stats['total_sessions'] = $result['count'];
        }
        
        // Get total subjects count
        $stmt = $cbt_conn->prepare("SELECT COUNT(*) as count FROM subjects");
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) {
            $stats['total_subjects'] = $result['count'];
        }
        
        // Check if migration has ever run by looking for any users with hashed passwords
        $stmt = $cbt_conn->prepare("SELECT MAX(created_at) as last_update FROM users WHERE password LIKE ? LIMIT 1");
        $stmt->execute(['$2y$%']);
        $result = $stmt->fetch();
        if ($result && $result['last_update']) {
            $stats['last_sync'] = date('M j, H:i', strtotime($result['last_update']));
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