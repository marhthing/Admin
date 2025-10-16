
<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

header('Content-Type: application/json');

try {
    $cbt = createConnection('cbt');
    
    // Get all classes (ordered by display_order)
    $classStmt = $cbt->prepare("
        SELECT name, display_name 
        FROM class_levels 
        WHERE is_active = 1 
        ORDER BY display_order ASC
    ");
    $classStmt->execute();
    $classes = $classStmt->fetchAll();
    
    // Get all terms (ordered by display_order)
    $termStmt = $cbt->prepare("
        SELECT name 
        FROM terms 
        WHERE is_active = 1 
        ORDER BY display_order ASC
    ");
    $termStmt->execute();
    $terms = $termStmt->fetchAll();
    
    // Get all sessions (ordered by name DESC to show recent first)
    $sessionStmt = $cbt->prepare("
        SELECT name 
        FROM sessions 
        WHERE is_active = 1 
        ORDER BY name DESC
    ");
    $sessionStmt->execute();
    $sessions = $sessionStmt->fetchAll();
    
    // Get all subjects (ordered by name)
    $subjectStmt = $cbt->prepare("
        SELECT name 
        FROM subjects 
        WHERE is_active = 1 
        ORDER BY name ASC
    ");
    $subjectStmt->execute();
    $subjects = $subjectStmt->fetchAll();
    
    // Get distinct assignment types from test_codes
    $assignmentStmt = $cbt->prepare("
        SELECT DISTINCT test_type 
        FROM test_codes 
        WHERE test_type IS NOT NULL 
        ORDER BY test_type ASC
    ");
    $assignmentStmt->execute();
    $assignmentTypes = $assignmentStmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'filters' => [
            'classes' => $classes,
            'terms' => $terms,
            'sessions' => $sessions,
            'subjects' => $subjects,
            'assignment_types' => $assignmentTypes
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'filters' => [
            'classes' => [],
            'terms' => [],
            'sessions' => [],
            'subjects' => [],
            'assignment_types' => []
        ]
    ]);
}
?>
