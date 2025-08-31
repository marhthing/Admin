
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
        $params[] = strtolower($assignment_type_filter); // Convert to lowercase to match database
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
        INNER JOIN test_codes tc ON tr.test_code_id = tc.id
        INNER JOIN users u ON tr.student_id = u.id
        LEFT JOIN subjects sub ON tc.subject_id = sub.id
        LEFT JOIN terms t ON tc.term_id = t.id
        LEFT JOIN sessions s ON tc.session_id = s.id
        $whereClause
        ORDER BY tr.submitted_at DESC, u.full_name ASC
    ";

    $stmt = $cbt->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // Process results to ensure we have proper student names and format data
    foreach ($results as &$result) {
        if (empty($result['student_name']) && !empty($result['user_full_name'])) {
            $result['student_name'] = $result['user_full_name'];
        }
        
        // Convert assignment type to proper case for display
        $result['assignment_type'] = ucfirst($result['assignment_type']);
        
        // Ensure we have default values for null fields
        $result['reg_number'] = $result['reg_number'] ?? 'N/A';
        $result['student_name'] = $result['student_name'] ?? 'Unknown Student';
        $result['class'] = $result['class'] ?? 'N/A';
        $result['subject'] = $result['subject'] ?? 'Unknown Subject';
        $result['term'] = $result['term'] ?? 'N/A';
        $result['session'] = $result['session'] ?? 'N/A';
        $result['score'] = $result['score'] ?? 0;
        $result['total_marks'] = $result['total_marks'] ?? 0;
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
    
    // Build query with filters
    $query = "SELECT 
                tr.id,
                tr.score,
                tr.total_marks,
                tr.submitted_at as date_taken,
                u.full_name as student_name,
                u.reg_number,
                tc.subject_name as subject,
                tc.assignment_type,
                tc.class_level as class,
                s.session_name as session,
                t.term_name as term
              FROM test_results tr
              LEFT JOIN users u ON tr.student_id = u.id
              LEFT JOIN test_codes tc ON tr.test_code_id = tc.id
              LEFT JOIN sessions s ON tc.session_id = s.id
              LEFT JOIN terms t ON tc.term_id = t.id
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($class_filter)) {
        $query .= " AND tc.class_level = ?";
        $params[] = $class_filter;
    }
    
    if (!empty($term_filter)) {
        $query .= " AND t.term_name = ?";
        $params[] = $term_filter;
    }
    
    if (!empty($session_filter)) {
        $query .= " AND s.session_name = ?";
        $params[] = $session_filter;
    }
    
    if (!empty($subject_filter)) {
        $query .= " AND tc.subject_name = ?";
        $params[] = $subject_filter;
    }
    
    if (!empty($assignment_type_filter)) {
        $query .= " AND tc.assignment_type = ?";
        $params[] = $assignment_type_filter;
    }
    
    $query .= " ORDER BY tr.submitted_at DESC LIMIT 100";
    
    $stmt = $cbt->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'results' => $results
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'results' => []
    ]);
}
?>
