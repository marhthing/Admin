
<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

    if ($format === 'csv') {
        exportCSV($results);
    } else {
        exportPDF($results, $class_filter, $term_filter, $session_filter, $subject_filter, $assignment_type_filter);
    }

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

function exportPDF($results, $class_filter, $term_filter, $session_filter, $subject_filter, $assignment_type_filter) {
    // Build filter display text
    $filters_text = [];
    if ($class_filter) $filters_text[] = "Class: $class_filter";
    if ($term_filter) $filters_text[] = "Term: $term_filter";
    if ($session_filter) $filters_text[] = "Session: $session_filter";
    if ($subject_filter) $filters_text[] = "Subject: $subject_filter";
    if ($assignment_type_filter) $filters_text[] = "Type: " . ucfirst($assignment_type_filter);
    
    $subtitle = !empty($filters_text) ? implode(' | ', $filters_text) : 'All Results';
    $date = date('F d, Y - h:i A');
    $count = count($results);
    
    // Build HTML for PDF
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px;
                font-size: 11px;
            }
            h1 { 
                text-align: center; 
                color: #4f46e5; 
                margin-bottom: 10px;
                font-size: 24px;
            }
            .subtitle {
                text-align: center;
                color: #666;
                margin-bottom: 5px;
                font-size: 12px;
            }
            .meta {
                text-align: center;
                color: #999;
                margin-bottom: 20px;
                font-size: 10px;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 10px;
            }
            th {
                background: #4f46e5;
                color: white;
                padding: 8px 4px;
                text-align: left;
                font-size: 10px;
                border: 1px solid #3730a3;
            }
            td {
                border: 1px solid #ddd;
                padding: 6px 4px;
                font-size: 9px;
            }
            tr:nth-child(even) {
                background: #f9fafb;
            }
            .footer {
                margin-top: 20px;
                text-align: center;
                font-size: 9px;
                color: #999;
            }
        </style>
    </head>
    <body>
        <h1>CBT Results Report</h1>
        <div class='subtitle'>$subtitle</div>
        <div class='meta'>Generated: $date | Total Records: $count</div>
        
        <table>
            <thead>
                <tr>
                    <th>Reg No.</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Score</th>
                    <th>Total</th>
                    <th>%</th>
                    <th>Term</th>
                    <th>Session</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($results as $result) {
        $percentage = $result['total_marks'] > 0 
            ? round(($result['score'] / $result['total_marks']) * 100, 1) 
            : 0;
        
        $html .= "<tr>
            <td>" . htmlspecialchars($result['reg_number'] ?? 'N/A') . "</td>
            <td>" . htmlspecialchars($result['student_name'] ?? 'Unknown') . "</td>
            <td>" . htmlspecialchars($result['class'] ?? 'N/A') . "</td>
            <td>" . htmlspecialchars($result['subject'] ?? 'Unknown') . "</td>
            <td>" . htmlspecialchars(ucfirst($result['assignment_type'] ?? 'test')) . "</td>
            <td>" . ($result['score'] ?? 0) . "</td>
            <td>" . ($result['total_marks'] ?? 0) . "</td>
            <td>" . $percentage . "%</td>
            <td>" . htmlspecialchars($result['term'] ?? 'N/A') . "</td>
            <td>" . htmlspecialchars($result['session'] ?? 'N/A') . "</td>
            <td>" . ($result['date_taken'] ? date('Y-m-d', strtotime($result['date_taken'])) : 'N/A') . "</td>
        </tr>";
    }
    
    $html .= "</tbody>
        </table>
        
        <div class='footer'>
            Sure Foundation Group of Schools - CBT Results Management System
        </div>
    </body>
    </html>";
    
    // Generate PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    // Output PDF
    $dompdf->stream('cbt_results_' . date('Y-m-d_H-i-s') . '.pdf', ['Attachment' => true]);
    exit;
}
?>
