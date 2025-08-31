
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
        $whereConditions[] = "r.class = ?";
        $params[] = $class_filter;
    }

    if (!empty($term_filter)) {
        $whereConditions[] = "r.term = ?";
        $params[] = $term_filter;
    }

    if (!empty($session_filter)) {
        $whereConditions[] = "r.session = ?";
        $params[] = $session_filter;
    }

    if (!empty($subject_filter)) {
        $whereConditions[] = "r.subject = ?";
        $params[] = $subject_filter;
    }

    if (!empty($assignment_type_filter)) {
        $whereConditions[] = "r.assignment_type = ?";
        $params[] = $assignment_type_filter;
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    // Query to get results with student information
    $query = "
        SELECT 
            r.id,
            r.reg_number,
            r.student_name,
            r.class,
            r.subject,
            r.assignment_type,
            r.score,
            r.total_marks,
            r.term,
            r.session,
            r.date_taken,
            u.full_name as user_full_name
        FROM results r
        LEFT JOIN users u ON r.reg_number = u.reg_number
        $whereClause
        ORDER BY r.date_taken DESC, r.student_name ASC
    ";

    $stmt = $cbt->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // Process results to ensure we have proper student names
    foreach ($results as &$result) {
        if (empty($result['student_name']) && !empty($result['user_full_name'])) {
            $result['student_name'] = $result['user_full_name'];
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
