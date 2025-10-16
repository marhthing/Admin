<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    // Connect to SFGS database (where login_logs table is)
    $sfgs = createConnection('sfgs');
    
    // Get filters from request
    $date = $_GET['date'] ?? null;
    $user = $_GET['user'] ?? null;
    $role = $_GET['role'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Base query with JOINs to get usernames from different tables
    $query = "SELECT login_logs.*, 
                     CASE 
                         WHEN login_logs.role = 'admin' THEN users.fullname
                         WHEN login_logs.role = 'teacher' THEN teachers.fullname
                         WHEN login_logs.role = 'student' THEN CONCAT(students.firstname, ' ', students.lastname)  
                     END AS username,
                     IF(login_logs.status = 'success', 'Success', 'Failed') AS login_status
              FROM login_logs 
              LEFT JOIN users ON login_logs.role = 'admin' AND login_logs.user_id = users.id
              LEFT JOIN teachers ON login_logs.role = 'teacher' AND login_logs.user_id = teachers.id
              LEFT JOIN students ON login_logs.role = 'student' AND login_logs.user_id = students.id
              WHERE 1=1";
    
    $params = [];
    
    // Apply filters
    if (!empty($date)) {
        $query .= " AND DATE(login_logs.login_time) = ?";
        $params[] = $date;
    }
    
    if (!empty($user)) {
        $query .= " AND (users.fullname LIKE ? OR teachers.fullname LIKE ? OR CONCAT(students.firstname, ' ', students.lastname) LIKE ?)";
        $user_param = "%$user%";
        $params[] = $user_param;
        $params[] = $user_param;
        $params[] = $user_param;
    }
    
    if (!empty($role)) {
        $query .= " AND login_logs.role = ?";
        $params[] = $role;
    }
    
    // Sorting & Pagination
    $query .= " ORDER BY login_logs.login_time DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Execute query with PDO
    $stmt = $sfgs->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
    echo json_encode($logs);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>
