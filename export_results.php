
<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

try {
    // Get format and filter parameters
    $format = $_GET['format'] ?? 'csv';
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

    // Query to get results
    $query = "
        SELECT 
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
    ";

    $stmt = $cbt->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    exportCSV($results);

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

function exportCSV($results) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="cbt_results_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV headers
    fputcsv($output, [
        'Registration Number',
        'Student Name',
        'Class',
        'Subject',
        'Assignment Type',
        'Score',
        'Total Marks',
        'Percentage',
        'Term',
        'Session',
        'Date Taken'
    ]);
    
    // Add data rows
    foreach ($results as $result) {
        $percentage = $result['total_marks'] > 0 
            ? round(($result['score'] / $result['total_marks']) * 100, 2) 
            : 0;
        
        fputcsv($output, [
            $result['reg_number'] ?? 'N/A',
            $result['student_name'] ?? 'Unknown',
            $result['class'] ?? 'N/A',
            $result['subject'] ?? 'Unknown',
            ucfirst($result['assignment_type'] ?? 'test'),
            $result['score'] ?? 0,
            $result['total_marks'] ?? 0,
            $percentage . '%',
            $result['term'] ?? 'N/A',
            $result['session'] ?? 'N/A',
            $result['date_taken'] ? date('Y-m-d', strtotime($result['date_taken'])) : 'N/A'
        ]);
    }
    
    fclose($output);
    exit;
}
?>
