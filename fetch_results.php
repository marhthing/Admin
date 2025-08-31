
<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

header('Content-Type: application/json');

try {
    // Get filter parameters
    $class_filter = $_GET['class'] ?? '';
    $term_filter = $_GET['term'] ?? '';
    $session_filter = $_GET['session'] ?? '';
    $subject_filter = $_GET['subject'] ?? '';
    $assignment_type_filter = $_GET['assignment_type'] ?? '';

    $cbt = createConnection('cbt');

    // Build dynamic query with filters
    $whereConditions = [];
    $params = [];

    if (!empty($class_filter)) {
        $whereConditions[] = "tc.class_level = ?";
        $params[] = $class_filter;
    }

    if (!empty($term_filter)) {
        $whereConditions[] = "t.name = ?";
        $params[] = $term_filter;
    }

    if (!empty($session_filter)) {
        $whereConditions[] = "s.name = ?";
        $params[] = $session_filter;
    }

    if (!empty($subject_filter)) {
        $whereConditions[] = "sub.name = ?";
        $params[] = $subject_filter;
    }

    if (!empty($assignment_type_filter)) {
        $whereConditions[] = "tc.test_type = ?";
        $params[] = $assignment_type_filter;
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    // Query to get results with student information from actual CBT tables
    $query = "
        SELECT 
            tr.id,
            u.reg_number,
            u.full_name as student_name,
            tc.class_level as class,
            sub.name as subject,
            tc.test_type as assignment_type,
            tr.score,
            tr.total_questions as total_marks,
            t.name as term,
            s.name as session,
            tr.submitted_at as date_taken,
            u.full_name as user_full_name
        FROM test_results tr
        LEFT JOIN test_codes tc ON tr.test_code_id = tc.id
        LEFT JOIN users u ON tr.student_id = u.id
        LEFT JOIN subjects sub ON tc.subject_id = sub.id
        LEFT JOIN terms t ON tc.term_id = t.id
        LEFT JOIN sessions s ON tc.session_id = s.id
        $whereClause
        ORDER BY tr.submitted_at DESC, u.full_name ASC
    ";

    $stmt = $cbt->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // Process results to ensure we have proper student names
    foreach ($results as &$result) {
        if (empty($result['student_name']) && !empty($result['user_full_name'])) {
            $result['student_name'] = $result['user_full_name'];
        }
        
        // Convert assignment type to more readable format
        if ($result['assignment_type'] === 'test') {
            $result['assignment_type'] = 'Test';
        }
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'count' => count($results)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'results' => []
    ]);
}
?>
