<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

header('Content-Type: application/json');

try {
    $connections = testConnections();
    
    $response = [
        'success' => true,
        'connections' => [
            'sfgs' => [
                'status' => $connections['sfgs'] ? 'connected' : 'error',
                'message' => $connections['sfgs'] ? 'Connected successfully' : ($connections['sfgs_error'] ?? 'Connection failed')
            ],
            'cbt' => [
                'status' => $connections['cbt'] ? 'connected' : 'error', 
                'message' => $connections['cbt'] ? 'Connected successfully' : ($connections['cbt_error'] ?? 'Connection failed')
            ]
        ]
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>