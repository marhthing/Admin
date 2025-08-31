<?php
/**
 * SFGS to CBT Smart Migration Script
 * Handles intelligent data synchronization between databases
 * Only updates missing or incorrect data, preserves existing records
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';
require_once 'auth.php';

// Require authentication for all migration operations
requireAuth();

// Global variable to store detailed logs
$detailedLogs = [];

/**
 * Add detailed step logging
 */
function logDetailedStep($message) {
    global $detailedLogs;
    $timestamp = date('H:i:s');
    $detailedLogs[] = "[$timestamp] $message";
}

/**
 * Get detailed logs
 */
function getDetailedLogs() {
    global $detailedLogs;
    return $detailedLogs;
}

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
 * Smart migrate admin users - only insert/update missing or incorrect data
 * SECURITY: All passwords from SFGS (plain text) are hashed before insertion
 */
function migrateAdmins() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];

    logDetailedStep("🔍 Fetching admin users from SFGS database...");

    // Get admin users from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, fullname, email, password, phone, address, state
        FROM users 
        WHERE email IS NOT NULL AND fullname IS NOT NULL
    ");
    $stmt->execute();
    $sfgsAdmins = $stmt->fetchAll();

    logDetailedStep("📊 Found " . count($sfgsAdmins) . " admin users in SFGS");

    logDetailedStep("🔍 Checking existing admin users in CBT database...");

    // Get existing admins from CBT
    $existingStmt = $cbt->prepare("
        SELECT reg_number, email, full_name 
        FROM users 
        WHERE role = 'admin'
    ");
    $existingStmt->execute();
    $existingAdmins = $existingStmt->fetchAll();

    logDetailedStep("📊 Found " . count($existingAdmins) . " existing admin users in CBT");

    // Create lookup array for existing admins
    $existingLookup = [];
    foreach ($existingAdmins as $admin) {
        $existingLookup[$admin['reg_number']] = $admin;
    }

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    logDetailedStep("🔄 Starting admin user synchronization process...");

    foreach ($sfgsAdmins as $admin) {
        $reg_number = 'ADM' . str_pad($admin['id'], 4, '0', STR_PAD_LEFT);

        logDetailedStep("👤 Processing admin: {$admin['fullname']} (ID: {$admin['id']}, Reg: {$reg_number})");

        if (isset($existingLookup[$reg_number])) {
            // Check if data matches
            $existing = $existingLookup[$reg_number];
            if ($existing['email'] === $admin['email'] && $existing['full_name'] === $admin['fullname']) {
                logDetailedStep("✅ Admin {$reg_number} data is current - skipping");
                $skipped++;
                continue; // Data is correct, skip
            } else {
                logDetailedStep("🔄 Updating admin {$reg_number} with new data");

                // Update existing record
                $updateStmt = $cbt->prepare("
                    UPDATE users SET 
                        username = :username,
                        email = :email,
                        full_name = :full_name,
                        updated_at = NOW()
                    WHERE reg_number = :reg_number AND role = 'admin'
                ");

                $updateStmt->execute([
                    'username' => $admin['email'],
                    'email' => $admin['email'],
                    'full_name' => $admin['fullname'],
                    'reg_number' => $reg_number
                ]);

                logDetailedStep("✅ Successfully updated admin {$reg_number}");
                $updated++;
            }
        } else {
            logDetailedStep("🆕 Creating new admin user {$reg_number}");
            logDetailedStep("🔐 Hashing password for security (converting from plain text)");

            // Insert new record with hashed password
            $hashedPassword = password_hash($admin['password'], PASSWORD_DEFAULT);

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
                'password' => $hashedPassword,
                'full_name' => $admin['fullname']
            ]);

            logDetailedStep("✅ Successfully created admin {$reg_number} with hashed password");
            $inserted++;
        }
    }

    logDetailedStep("🎉 Admin synchronization complete!");

    return [
        'success' => true,
        'count' => $inserted + $updated,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'message' => "Admin sync complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped",
        'details' => [
            'total_processed' => count($sfgsAdmins),
            'passwords_hashed' => $inserted,
            'security_note' => 'All passwords converted from plain text to secure hash'
        ]
    ];
}

/**
 * Smart migrate teacher users
 * SECURITY: All passwords from SFGS (plain text) are hashed before insertion
 */
function migrateTeachers() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];

    logDetailedStep("🔍 Fetching teacher users from SFGS database...");

    // Get teacher users from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, fullname, email, password, phone, address, state
        FROM teachers 
        WHERE email IS NOT NULL AND fullname IS NOT NULL
    ");
    $stmt->execute();
    $sfgsTeachers = $stmt->fetchAll();

    logDetailedStep("📊 Found " . count($sfgsTeachers) . " teacher users in SFGS");

    logDetailedStep("🔍 Checking existing teacher users in CBT database...");

    // Get existing teachers from CBT
    $existingStmt = $cbt->prepare("
        SELECT reg_number, email, full_name 
        FROM users 
        WHERE role = 'teacher'
    ");
    $existingStmt->execute();
    $existingTeachers = $existingStmt->fetchAll();

    logDetailedStep("📊 Found " . count($existingTeachers) . " existing teacher users in CBT");

    // Create lookup array for existing teachers
    $existingLookup = [];
    foreach ($existingTeachers as $teacher) {
        $existingLookup[$teacher['reg_number']] = $teacher;
    }

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    logDetailedStep("🔄 Starting teacher user synchronization process...");

    foreach ($sfgsTeachers as $teacher) {
        $reg_number = 'TCH' . str_pad($teacher['id'], 4, '0', STR_PAD_LEFT);

        logDetailedStep("👨‍🏫 Processing teacher: {$teacher['fullname']} (ID: {$teacher['id']}, Reg: {$reg_number})");

        if (isset($existingLookup[$reg_number])) {
            // Check if data matches
            $existing = $existingLookup[$reg_number];
            if ($existing['email'] === $teacher['email'] && $existing['full_name'] === $teacher['fullname']) {
                logDetailedStep("✅ Teacher {$reg_number} data is current - skipping");
                $skipped++;
                continue; // Data is correct, skip
            } else {
                logDetailedStep("🔄 Updating teacher {$reg_number} with new data");

                // Update existing record
                $updateStmt = $cbt->prepare("
                    UPDATE users SET 
                        username = :username,
                        email = :email,
                        full_name = :full_name,
                        updated_at = NOW()
                    WHERE reg_number = :reg_number AND role = 'teacher'
                ");

                $updateStmt->execute([
                    'username' => $teacher['email'],
                    'email' => $teacher['email'],
                    'full_name' => $teacher['fullname'],
                    'reg_number' => $reg_number
                ]);

                logDetailedStep("✅ Successfully updated teacher {$reg_number}");
                $updated++;
            }
        } else {
            logDetailedStep("🆕 Creating new teacher user {$reg_number}");
            logDetailedStep("🔐 Hashing password for security (converting from plain text)");

            // Insert new record with hashed password
            $hashedPassword = password_hash($teacher['password'], PASSWORD_DEFAULT);

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
                'password' => $hashedPassword,
                'full_name' => $teacher['fullname']
            ]);

            logDetailedStep("✅ Successfully created teacher {$reg_number} with hashed password");
            $inserted++;
        }
    }

    logDetailedStep("🎉 Teacher synchronization complete!");

    return [
        'success' => true,
        'count' => $inserted + $updated,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'message' => "Teacher sync complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped",
        'details' => [
            'total_processed' => count($sfgsTeachers),
            'passwords_hashed' => $inserted,
            'security_note' => 'All passwords converted from plain text to secure hash'
        ]
    ];
}

/**
 * Smart migrate student users
 * SECURITY: All passwords from SFGS (plain text) are hashed before insertion
 */
function migrateStudents() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];

    logDetailedStep("🔍 Fetching student users from SFGS database...");

    // Get student users from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, firstname, lastname, othername, reg_number, gen_password, 
               contact_phone, address, state, class
        FROM students 
        WHERE reg_number IS NOT NULL AND firstname IS NOT NULL AND lastname IS NOT NULL
    ");
    $stmt->execute();
    $sfgsStudents = $stmt->fetchAll();

    logDetailedStep("📊 Found " . count($sfgsStudents) . " student users in SFGS");

    logDetailedStep("🔍 Checking existing student users in CBT database...");

    // Get existing students from CBT
    $existingStmt = $cbt->prepare("
        SELECT reg_number, email, full_name 
        FROM users 
        WHERE role = 'student'
    ");
    $existingStmt->execute();
    $existingStudents = $existingStmt->fetchAll();

    logDetailedStep("📊 Found " . count($existingStudents) . " existing student users in CBT");

    // Create lookup array for existing students
    $existingLookup = [];
    foreach ($existingStudents as $student) {
        $existingLookup[$student['reg_number']] = $student;
    }

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    logDetailedStep("🔄 Starting student user synchronization process...");

    foreach ($sfgsStudents as $student) {
        // Build full name
        $full_name = trim($student['firstname'] . ' ' . $student['lastname']);
        if (!empty($student['othername'])) {
            $full_name .= ' ' . $student['othername'];
        }

        // Generate email for student
        $email = strtolower($student['reg_number']) . '@student.school.edu';
        $username = $student['reg_number'];

        logDetailedStep("👨‍🎓 Processing student: {$full_name} (Reg: {$student['reg_number']})");

        if (isset($existingLookup[$student['reg_number']])) {
            // Check if data matches
            $existing = $existingLookup[$student['reg_number']];
            if ($existing['email'] === $email && $existing['full_name'] === $full_name) {
                logDetailedStep("✅ Student {$student['reg_number']} data is current - skipping");
                $skipped++;
                continue; // Data is correct, skip
            } else {
                logDetailedStep("🔄 Updating student {$student['reg_number']} with new data");

                // Update existing record
                $updateStmt = $cbt->prepare("
                    UPDATE users SET 
                        username = :username,
                        email = :email,
                        full_name = :full_name,
                        updated_at = NOW()
                    WHERE reg_number = :reg_number AND role = 'student'
                ");

                $updateStmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $full_name,
                    'reg_number' => $student['reg_number']
                ]);

                logDetailedStep("✅ Successfully updated student {$student['reg_number']}");
                $updated++;
            }
        } else {
            logDetailedStep("🆕 Creating new student user {$student['reg_number']}");
            logDetailedStep("🔐 Hashing password for security (converting from plain text)");

            // Insert new record with hashed password
            $hashedPassword = password_hash($student['gen_password'], PASSWORD_DEFAULT);

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
                'password' => $hashedPassword,
                'full_name' => $full_name
            ]);

            logDetailedStep("✅ Successfully created student {$student['reg_number']} with hashed password");
            $inserted++;
        }
    }

    logDetailedStep("🎉 Student synchronization complete!");

    return [
        'success' => true,
        'count' => $inserted + $updated,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'message' => "Student sync complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped",
        'details' => [
            'total_processed' => count($sfgsStudents),
            'passwords_hashed' => $inserted,
            'security_note' => 'All passwords converted from plain text to secure hash'
        ]
    ];
}

/**
 * Smart migrate classes from SFGS to CBT
 */
function migrateClasses() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];

    // Get classes from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, classes
        FROM classes 
        WHERE classes IS NOT NULL
        ORDER BY id
    ");
    $stmt->execute();
    $sfgsClasses = $stmt->fetchAll();

    // Get existing class levels from CBT
    $existingStmt = $cbt->prepare("
        SELECT name, display_name, level_type, display_order
        FROM class_levels
    ");
    $existingStmt->execute();
    $existingClasses = $existingStmt->fetchAll();

    // Create lookup array for existing classes
    $existingLookup = [];
    foreach ($existingClasses as $class) {
        $existingLookup[$class['name']] = $class;
    }

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    logDetailedStep("🔄 Starting class level synchronization process...");

    foreach ($sfgsClasses as $class) {
        try {
            // Determine level type based on class name
            $className = strtoupper(trim($class['classes']));
            $levelType = 'junior'; // default

            if (strpos($className, 'SS') !== false || strpos($className, 'SSS') !== false) {
                $levelType = 'senior';
            }

            // Determine display order
            $displayOrder = 1;
            if (preg_match('/(\d+)/', $className, $matches)) {
                $displayOrder = intval($matches[1]);
            }

            logDetailedStep("🔍 Processing class: $className (Level: $levelType)");

            if (isset($existingLookup[$className])) {
                // Check if data matches
                $existing = $existingLookup[$className];
                if ($existing['display_name'] === $className && 
                    $existing['level_type'] === $levelType && 
                    $existing['display_order'] == $class['id']) {
                    logDetailedStep("✅ Class {$className} data is current - skipping");
                    $skipped++;
                    continue; // Data is correct, skip
                } else {
                    logDetailedStep("🔄 Updating class {$className} with new data");
                    // Update existing record
                    $updateStmt = $cbt->prepare("
                        UPDATE class_levels SET 
                            display_name = :display_name,
                            display_order = :display_order,
                            level_type = :level_type,
                            updated_at = NOW()
                        WHERE name = :name
                    ");

                    $updateStmt->execute([
                        'display_name' => $className,
                        'display_order' => $class['id'],
                        'level_type' => $levelType,
                        'name' => $className
                    ]);
                    logDetailedStep("✅ Successfully updated class {$className}");
                    $updated++;
                }
            } else {
                logDetailedStep("🆕 Creating new class {$className}");
                // Insert new record
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
                logDetailedStep("✅ Successfully created class {$className}");
                $inserted++;
            }
        } catch (Exception $e) {
            logDetailedStep("❌ Error processing class {$class['classes']}: " . $e->getMessage());
            // Optionally, you can choose to stop processing or just log and continue
            // For now, we'll just log and continue to the next class
        }
    }

    logDetailedStep("🎉 Class synchronization complete!");

    return [
        'success' => true,
        'count' => $inserted + $updated,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'message' => "Class sync complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped"
    ];
}

/**
 * Smart migrate sessions from SFGS to CBT
 */
function migrateSessions() {
    $connections = getDatabaseConnections();
    $sfgs = $connections['sfgs'];
    $cbt = $connections['cbt'];

    // Get sessions from SFGS
    $stmt = $sfgs->prepare("
        SELECT id, sessions
        FROM sessions 
        WHERE sessions IS NOT NULL
        ORDER BY id
    ");
    $stmt->execute();
    $sfgsSessions = $stmt->fetchAll();

    // Get existing sessions from CBT
    $existingStmt = $cbt->prepare("
        SELECT name, start_date, end_date, is_current
        FROM sessions
    ");
    $existingStmt->execute();
    $existingSessions = $existingStmt->fetchAll();

    // Create lookup array for existing sessions
    $existingLookup = [];
    foreach ($existingSessions as $session) {
        $existingLookup[$session['name']] = $session;
    }

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($sfgsSessions as $session) {
        $sessionName = trim($session['sessions']);

        // Parse session name to create start and end dates
        if (preg_match('/(\d{4})\/(\d{4})/', $sessionName, $matches)) {
            $startYear = $matches[1];
            $endYear = $matches[2];
            $startDate = $startYear . '-09-01';
            $endDate = $endYear . '-07-31';
        } else {
            $startDate = date('Y') . '-09-01';
            $endDate = (date('Y') + 1) . '-07-31';
        }

        $isCurrent = ($sessionName === '2024/2025') ? 1 : 0;

        if (isset($existingLookup[$sessionName])) {
            // Check if data matches
            $existing = $existingLookup[$sessionName];
            if ($existing['start_date'] === $startDate && 
                $existing['end_date'] === $endDate && 
                $existing['is_current'] == $isCurrent) {
                $skipped++;
                continue; // Data is correct, skip
            } else {
                // Update existing record
                $updateStmt = $cbt->prepare("
                    UPDATE sessions SET 
                        start_date = :start_date,
                        end_date = :end_date,
                        is_current = :is_current,
                        updated_at = NOW()
                    WHERE name = :name
                ");

                $updateStmt->execute([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_current' => $isCurrent,
                    'name' => $sessionName
                ]);
                $updated++;
            }
        } else {
            // Insert new record
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
            $inserted++;
        }
    }

    return [
        'success' => true,
        'count' => $inserted + $updated,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'message' => "Session sync complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped"
    ];
}

/**
 * Smart migrate terms to CBT
 */
function migrateTerms() {
    $connections = getDatabaseConnections();
    $cbt = $connections['cbt'];

    // Get existing terms from CBT
    $existingStmt = $cbt->prepare("
        SELECT name, display_order
        FROM terms
    ");
    $existingStmt->execute();
    $existingTerms = $existingStmt->fetchAll();

    // Create lookup array for existing terms
    $existingLookup = [];
    foreach ($existingTerms as $term) {
        $existingLookup[$term['name']] = $term;
    }

    // Standard terms to ensure exist
    $terms = [
        ['name' => 'First', 'display_order' => 1],
        ['name' => 'Second', 'display_order' => 2],
        ['name' => 'Third', 'display_order' => 3]
    ];

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($terms as $term) {
        if (isset($existingLookup[$term['name']])) {
            // Check if data matches
            $existing = $existingLookup[$term['name']];
            if ($existing['display_order'] == $term['display_order']) {
                $skipped++;
                continue; // Data is correct, skip
            } else {
                // Update existing record
                $updateStmt = $cbt->prepare("
                    UPDATE terms SET 
                        display_order = :display_order,
                        updated_at = NOW()
                    WHERE name = :name
                ");

                $updateStmt->execute([
                    'display_order' => $term['display_order'],
                    'name' => $term['name']
                ]);
                $updated++;
            }
        } else {
            // Insert new record
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
            $inserted++;
        }
    }

    return [
        'success' => true,
        'count' => $inserted + $updated,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'message' => "Term sync complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped"
    ];
}
?>