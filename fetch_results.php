<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

header('Content-Type: application/json');

try {
    // Get filter parameters and trim whitespace
    $class_filter = trim($_GET['class'] ?? '');
    $term_filter = trim($_GET['term'] ?? '');
    $session_filter = trim($_GET['session'] ?? '');
    $subject_filter = trim($_GET['subject'] ?? '');
    $assignment_type_filter = trim($_GET['assignment_type'] ?? '');

    $cbt = createConnection('cbt');

    // Build dynamic query with filters
    $whereConditions = [];
    $params = [];

    if (!empty($class_filter)) {
        $whereConditions[] = "tc.class_level = ?";
        $params[] = trim($class_filter);
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
        $whereConditions[] = "LOWER(tc.test_type) = LOWER(?)";
        $params[] = $assignment_type_filter;
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    // Query to get results with student information
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
            tr.submitted_at as date_taken
        FROM test_results tr
        INNER JOIN test_codes tc ON tr.test_code_id = tc.id
        INNER JOIN users u ON tr.student_id = u.id
        LEFT JOIN subjects sub ON tc.subject_id = sub.id
        LEFT JOIN terms t ON tc.term_id = t.id
        LEFT JOIN sessions s ON tc.session_id = s.id
        $whereClause
        ORDER BY tr.submitted_at DESC, u.full_name ASC
        LIMIT 100
    ";

    $stmt = $cbt->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process results to ensure we have proper data
    foreach ($results as &$result) {
        // Convert assignment type to proper case for display
        $result['assignment_type'] = ucfirst($result['assignment_type'] ?? 'test');

        // Ensure we have default values for null fields
        $result['reg_number'] = $result['reg_number'] ?? 'N/A';
        $result['student_name'] = $result['student_name'] ?? 'Unknown Student';
        $result['class'] = $result['class'] ?? 'N/A';
        $result['subject'] = $result['subject'] ?? 'Unknown Subject';
        $result['term'] = $result['term'] ?? 'N/A';
        $result['session'] = $result['session'] ?? 'N/A';
        $result['score'] = intval($result['score'] ?? 0);
        $result['total_marks'] = intval($result['total_marks'] ?? 0);
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