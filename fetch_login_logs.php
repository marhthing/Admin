<?php

require_once("includes/connection.php");

// Get filters from request, with validation
$date = isset($_GET['date']) ? $_GET['date'] : null;
$user = isset($_GET['user']) ? $_GET['user'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Base query
$query = "SELECT login_logs.*, 
                 CASE 
                     WHEN login_logs.role = 'admin' THEN users.fullname
                     WHEN login_logs.role = 'teacher' THEN teachers.fullname
                     WHEN login_logs.role = 'student' THEN CONCAT(students.firstname, ' ', students.lastname)  
                 END AS username,
                 IF(login_logs.status = 1, 'Success', 'Failed') AS login_status
          FROM login_logs 
          LEFT JOIN users ON login_logs.role = 'admin' AND login_logs.user_id = users.id
          LEFT JOIN teachers ON login_logs.role = 'teacher' AND login_logs.user_id = teachers.id
          LEFT JOIN students ON login_logs.role = 'student' AND login_logs.user_id = students.id
          WHERE 1=1";


$params = [];
$types = "";

// Apply filters
if (!empty($date)) {
    $query .= " AND DATE(login_logs.login_time) = ?";
    $params[] = $date;
    $types .= "s";
}
if (!empty($user)) {
    $query .= " AND (users.fullname LIKE ? OR teachers.fullname LIKE ? OR students.surname LIKE ?)";
    $user_param = "%$user%";
    array_push($params, $user_param, $user_param, $user_param);
    $types .= "sss";
}
if (!empty($role)) {
    $query .= " AND login_logs.role = ?";
    $params[] = $role;
    $types .= "s";
}

// Sorting & Pagination
$query .= " ORDER BY login_logs.login_time DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Prepare & execute
$stmt = $connection->prepare($query);

// Bind parameters **only if** there are parameters
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch logs
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

echo json_encode($logs);
?>
