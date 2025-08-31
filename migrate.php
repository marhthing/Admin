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
            
        case 'migrate_classes':
            echo json_encode(migrateClasses());
            break;
            
        case 'migrate_sessions':
            echo json_encode(migrateSessions());
            break;
            
        case 'migrate_terms':
            echo json_encode(migrateTerms());
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
 * Migrate classes from SFGS to CBT
 */
function migrateClasses() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];
    
    // Clear existing class levels in CBT
    $cbt->exec("DELETE FROM class_levels");
    
    // Get classes from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, classes
        FROM classes 
        WHERE classes IS NOT NULL
        ORDER BY id
    ");
    $stmt->execute();
    $classes = $stmt->fetchAll();
    
    $count = 0;
    
    foreach ($classes as $class) {
        // Determine level type based on class name
        $className = strtoupper(trim($class['classes']));
        $levelType = 'junior'; // default
        
        if (strpos($className, 'SS') !== false || strpos($className, 'SSS') !== false) {
            $levelType = 'senior';
        }
        
        // Insert into CBT database
        $insertStmt = $cbt->prepare("
            INSERT INTO class_levels (
                name, display_name, display_order, level_type, is_active, created_at
            ) VALUES (
                :name, :display_name, :display_order, :level_type, 1, NOW()
            )
        ");
        
        $insertStmt->execute([
            'name' => $className,
            'display_name' => $className,
            'display_order' => $class['id'],
            'level_type' => $levelType
        ]);
        
        $count++;
    }
    
    return [
        'success' => true,
        'count' => $count,
        'message' => "Successfully migrated $count classes"
    ];
}

/**
 * Migrate sessions from SFGS to CBT
 */
function migrateSessions() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];
    
    // Clear existing sessions in CBT
    $cbt->exec("DELETE FROM sessions");
    
    // Get sessions from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, sessions
        FROM sessions 
        WHERE sessions IS NOT NULL
        ORDER BY id
    ");
    $stmt->execute();
    $sessions = $stmt->fetchAll();
    
    $count = 0;
    
    foreach ($sessions as $session) {
        // Parse session name to create start and end dates
        $sessionName = trim($session['sessions']);
        
        // Extract academic year (e.g., "2024/2025")
        if (preg_match('/(\d{4})\/(\d{4})/', $sessionName, $matches)) {
            $startYear = $matches[1];
            $endYear = $matches[2];
            $startDate = $startYear . '-09-01'; // Assuming September start
            $endDate = $endYear . '-07-31';    // Assuming July end
        } else {
            // Default dates if pattern doesn't match
            $startDate = date('Y') . '-09-01';
            $endDate = (date('Y') + 1) . '-07-31';
        }
        
        // Check if this is the current session (you can modify this logic)
        $isCurrent = ($sessionName === '2024/2025') ? 1 : 0;
        
        // Insert into CBT database
        $insertStmt = $cbt->prepare("
            INSERT INTO sessions (
                name, start_date, end_date, is_current, is_active, created_at
            ) VALUES (
                :name, :start_date, :end_date, :is_current, 1, NOW()
            )
        ");
        
        $insertStmt->execute([
            'name' => $sessionName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_current' => $isCurrent
        ]);
        
        $count++;
    }
    
    return [
        'success' => true,
        'count' => $count,
        'message' => "Successfully migrated $count sessions"
    ];
}

/**
 * Migrate terms to CBT (creates standard terms)
 */
function migrateTerms() {
    $connections = getDatabaseConnections();
    $cbt = $connections['cbt'];
    
    // Clear existing terms in CBT
    $cbt->exec("DELETE FROM terms");
    
    // Create standard terms
    $terms = [
        ['name' => 'First', 'display_order' => 1],
        ['name' => 'Second', 'display_order' => 2],
        ['name' => 'Third', 'display_order' => 3]
    ];
    
    $count = 0;
    
    foreach ($terms as $term) {
        $insertStmt = $cbt->prepare("
            INSERT INTO terms (
                name, display_order, is_active, created_at
            ) VALUES (
                :name, :display_order, 1, NOW()
            )
        ");
        
        $insertStmt->execute([
            'name' => $term['name'],
            'display_order' => $term['display_order']
        ]);
        
        $count++;
    }
    
    return [
        'success' => true,
        'count' => $count,
        'message' => "Successfully migrated $count terms"
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