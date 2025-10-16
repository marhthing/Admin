
<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

// Get filter parameters
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT Results Report - Print</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
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
        
        .print-btn {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #4338ca;
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
        
        .score-excellent { 
            background: #dcfce7; 
            color: #166534;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .score-good { 
            background: #dbeafe; 
            color: #1e40af;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .score-average { 
            background: #fef3c7; 
            color: #92400e;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .score-poor { 
            background: #fee2e2; 
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
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
    <div class="print-btn no-print">
        <button class="btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button class="btn" onclick="window.close()" style="background: #64748b;">Close</button>
    </div>
    
    <h1>CBT Results Report</h1>
    <div class="subtitle"><?php echo htmlspecialchars($subtitle); ?></div>
    <div class="meta">Generated: <?php echo $date; ?> | Total Records: <?php echo $count; ?></div>
    
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
        <tbody>
            <?php foreach ($results as $result): 
                $percentage = $result['total_marks'] > 0 
                    ? round(($result['score'] / $result['total_marks']) * 100, 1) 
                    : 0;
                
                $badgeClass = 'score-poor';
                if ($percentage >= 80) $badgeClass = 'score-excellent';
                elseif ($percentage >= 70) $badgeClass = 'score-good';
                elseif ($percentage >= 50) $badgeClass = 'score-average';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($result['reg_number'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($result['student_name'] ?? 'Unknown'); ?></td>
                <td><?php echo htmlspecialchars($result['class'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($result['subject'] ?? 'Unknown'); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($result['assignment_type'] ?? 'test')); ?></td>
                <td><?php echo $result['score'] ?? 0; ?></td>
                <td><?php echo $result['total_marks'] ?? 0; ?></td>
                <td><span class="<?php echo $badgeClass; ?>"><?php echo $percentage; ?>%</span></td>
                <td><?php echo htmlspecialchars($result['term'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($result['session'] ?? 'N/A'); ?></td>
                <td><?php echo $result['date_taken'] ? date('Y-m-d', strtotime($result['date_taken'])) : 'N/A'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        Sure Foundation Group of Schools - CBT Results Management System
    </div>
    
    <script>
        // Auto-trigger print dialog after page loads (optional)
        // window.onload = function() {
        //     setTimeout(() => window.print(), 500);
        // };
    </script>
</body>
</html>
