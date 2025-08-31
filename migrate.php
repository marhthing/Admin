<?php
/**
 * SFGS to CBT Migration Script
 * Handles user data migration between databases
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'test_connection':
            echo json_encode(testDatabaseConnections());
            break;
            
        case 'migrate_admins':
            echo json_encode(migrateAdmins());
            break;
            
        case 'migrate_teachers':
            echo json_encode(migrateTeachers());
            break;
            
        case 'migrate_students':
            echo json_encode(migrateStudents());
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Test database connections
 */
function testDatabaseConnections() {
    $connectionResults = testConnections();
    
    if (!$connectionResults['sfgs'] || !$connectionResults['cbt']) {
        $errors = [];
        if (!$connectionResults['sfgs']) $errors[] = "SFGS: " . ($connectionResults['sfgs_error'] ?? 'Unknown error');
        if (!$connectionResults['cbt']) $errors[] = "CBT: " . ($connectionResults['cbt_error'] ?? 'Unknown error');
        
        throw new Exception('Database connection failed: ' . implode(', ', $errors));
    }
    
    return [
        'success' => true,
        'message' => 'All database connections successful'
    ];
}

/**
 * Migrate admin users
 */
function migrateAdmins() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];
    
    // First, clear existing admin users in CBT (optional - remove if you want to keep existing data)
    $cbt->exec("DELETE FROM users WHERE role = 'admin'");
    
    // Get admin users from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, fullname, email, password, phone, address, state
        FROM users 
        WHERE email IS NOT NULL AND fullname IS NOT NULL
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    $count = 0;
    
    foreach ($admins as $admin) {
        // Generate reg_number for admin
        $reg_number = 'ADM' . str_pad($admin['id'], 4, '0', STR_PAD_LEFT);
        
        // Insert into CBT database
        $insertStmt = $cbt->prepare("
            INSERT INTO users (
                username, email, reg_number, password, role, full_name, 
                is_active, current_term, current_session, created_at
            ) VALUES (
                :username, :email, :reg_number, :password, 'admin', :full_name,
                1, 'First', '2024/2025', NOW()
            )
        ");
        
        $insertStmt->execute([
            'username' => $admin['email'],
            'email' => $admin['email'],
            'reg_number' => $reg_number,
            'password' => password_hash($admin['password'], PASSWORD_DEFAULT), // Hash the password
            'full_name' => $admin['fullname']
        ]);
        
        $count++;
    }
    
    return [
        'success' => true,
        'count' => $count,
        'message' => "Successfully migrated $count admin users"
    ];
}

/**
 * Migrate teacher users
 */
function migrateTeachers() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];
    
    // Clear existing teacher users in CBT (optional)
    $cbt->exec("DELETE FROM users WHERE role = 'teacher'");
    
    // Get teacher users from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, fullname, email, password, phone, address, state
        FROM teachers 
        WHERE email IS NOT NULL AND fullname IS NOT NULL
    ");
    $stmt->execute();
    $teachers = $stmt->fetchAll();
    
    $count = 0;
    
    foreach ($teachers as $teacher) {
        // Generate reg_number for teacher
        $reg_number = 'TCH' . str_pad($teacher['id'], 4, '0', STR_PAD_LEFT);
        
        // Insert into CBT database
        $insertStmt = $cbt->prepare("
            INSERT INTO users (
                username, email, reg_number, password, role, full_name, 
                is_active, current_term, current_session, created_at
            ) VALUES (
                :username, :email, :reg_number, :password, 'teacher', :full_name,
                1, 'First', '2024/2025', NOW()
            )
        ");
        
        $insertStmt->execute([
            'username' => $teacher['email'],
            'email' => $teacher['email'],
            'reg_number' => $reg_number,
            'password' => password_hash($teacher['password'], PASSWORD_DEFAULT),
            'full_name' => $teacher['fullname']
        ]);
        
        $count++;
    }
    
    return [
        'success' => true,
        'count' => $count,
        'message' => "Successfully migrated $count teacher users"
    ];
}

/**
 * Migrate student users
 */
function migrateStudents() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];
    
    // Clear existing student users in CBT (optional)
    $cbt->exec("DELETE FROM users WHERE role = 'student'");
    
    // Get student users from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, firstname, lastname, othername, reg_number, gen_password, 
               contact_phone, address, state, class
        FROM students 
        WHERE reg_number IS NOT NULL AND firstname IS NOT NULL AND lastname IS NOT NULL
    ");
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    $count = 0;
    
    foreach ($students as $student) {
        // Build full name
        $full_name = trim($student['firstname'] . ' ' . $student['lastname']);
        if (!empty($student['othername'])) {
            $full_name .= ' ' . $student['othername'];
        }
        
        // Generate email for student (since they don't have email in SFGS)
        $email = strtolower($student['reg_number']) . '@student.school.edu';
        
        // Use reg_number as username
        $username = $student['reg_number'];
        
        // Insert into CBT database
        $insertStmt = $cbt->prepare("
            INSERT INTO users (
                username, email, reg_number, password, role, full_name, 
                is_active, current_term, current_session, created_at
            ) VALUES (
                :username, :email, :reg_number, :password, 'student', :full_name,
                1, 'First', '2024/2025', NOW()
            )
        ");
        
        $insertStmt->execute([
            'username' => $username,
            'email' => $email,
            'reg_number' => $student['reg_number'],
            'password' => password_hash($student['gen_password'], PASSWORD_DEFAULT),
            'full_name' => $full_name
        ]);
        
        $count++;
    }
    
    return [
        'success' => true,
        'count' => $count,
        'message' => "Successfully migrated $count student users"
    ];
}

/**
 * Get migration statistics (bonus function)
 */
function getMigrationStats() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];
    
    // Count source records
    $adminCount = $sfgs->query("SELECT COUNT(*) FROM users WHERE email IS NOT NULL AND fullname IS NOT NULL")->fetchColumn();
    $teacherCount = $sfgs->query("SELECT COUNT(*) FROM teachers WHERE email IS NOT NULL AND fullname IS NOT NULL")->fetchColumn();
    $studentCount = $sfgs->query("SELECT COUNT(*) FROM students WHERE reg_number IS NOT NULL AND firstname IS NOT NULL")->fetchColumn();
    
    // Count target records
    $cbtAdminCount = $cbt->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $cbtTeacherCount = $cbt->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
    $cbtStudentCount = $cbt->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    
    return [
        'success' => true,
        'source' => [
            'admins' => $adminCount,
            'teachers' => $teacherCount,
            'students' => $studentCount,
            'total' => $adminCount + $teacherCount + $studentCount
        ],
        'target' => [
            'admins' => $cbtAdminCount,
            'teachers' => $cbtTeacherCount,
            'students' => $cbtStudentCount,
            'total' => $cbtAdminCount + $cbtTeacherCount + $cbtStudentCount
        ]
    ];
}
?>