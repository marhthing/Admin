<?php
date_default_timezone_set('Africa/Lagos');
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

// Get session info
$sessionInfo = getSessionInfo();

$backup_dir = "backups";

if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Handle AJAX backup request for both databases
if (isset($_POST['ajax_backup'])) {
    $backup_file = "$backup_dir/backup-" . date("Y-m-d-H-i-s") . ".sql";
    $backup_sql = "";
    
    try {
        // Header with restoration instructions
        $backup_sql .= "-- ========================================================================\n";
        $backup_sql .= "-- SFGS + CBT Database Backup\n";
        $backup_sql .= "-- Generated: " . date("Y-m-d H:i:s") . "\n";
        $backup_sql .= "-- ========================================================================\n";
        $backup_sql .= "-- \n";
        $backup_sql .= "-- RESTORATION INSTRUCTIONS:\n";
        $backup_sql .= "-- 1. Create two databases on your MySQL server:\n";
        $backup_sql .= "--    - if0_39795047_sfgs (or rename to your preferred name)\n";
        $backup_sql .= "--    - if0_39795047_cbt (or rename to your preferred name)\n";
        $backup_sql .= "-- \n";
        $backup_sql .= "-- 2. If you want to use different database names, replace:\n";
        $backup_sql .= "--    'if0_39795047_sfgs' with your SFGS database name\n";
        $backup_sql .= "--    'if0_39795047_cbt' with your CBT database name\n";
        $backup_sql .= "-- \n";
        $backup_sql .= "-- 3. Import this file:\n";
        $backup_sql .= "--    - Via phpMyAdmin: Import > Choose this file > Go\n";
        $backup_sql .= "--    - Via command line: mysql -u username -p < backup-file.sql\n";
        $backup_sql .= "-- \n";
        $backup_sql .= "-- ========================================================================\n\n";
        
        // Backup SFGS Database
        $sfgs = createConnection('sfgs');
        $backup_sql .= "-- ========================================================================\n";
        $backup_sql .= "-- SECTION 1: SFGS DATABASE\n";
        $backup_sql .= "-- ========================================================================\n\n";
        $backup_sql .= "-- Create database if it doesn't exist\n";
        $backup_sql .= "CREATE DATABASE IF NOT EXISTS `if0_39795047_sfgs` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n\n";
        $backup_sql .= "-- Switch to SFGS database\n";
        $backup_sql .= "USE `if0_39795047_sfgs`;\n\n";
        
        $tables = $sfgs->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $backup_sql .= "-- Table: $table\n";
            $backup_sql .= "DROP TABLE IF EXISTS `$table`;\n";
            
            $createTable = $sfgs->query("SHOW CREATE TABLE `$table`")->fetch();
            $backup_sql .= $createTable[1] . ";\n\n";
            
            $rows = $sfgs->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                $backup_sql .= "-- Data for table: $table\n";
                foreach ($rows as $row) {
                    $backup_sql .= "INSERT INTO `$table` VALUES(";
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $backup_sql .= implode(",", $values) . ");\n";
                }
            }
            $backup_sql .= "\n\n";
        }
        
        // Backup CBT Database
        $cbt = createConnection('cbt');
        $backup_sql .= "-- ========================================================================\n";
        $backup_sql .= "-- SECTION 2: CBT DATABASE\n";
        $backup_sql .= "-- ========================================================================\n\n";
        $backup_sql .= "-- Create database if it doesn't exist\n";
        $backup_sql .= "CREATE DATABASE IF NOT EXISTS `if0_39795047_cbt` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n\n";
        $backup_sql .= "-- Switch to CBT database\n";
        $backup_sql .= "USE `if0_39795047_cbt`;\n\n";
        
        $tables = $cbt->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $backup_sql .= "-- Table: $table\n";
            $backup_sql .= "DROP TABLE IF EXISTS `$table`;\n";
            
            $createTable = $cbt->query("SHOW CREATE TABLE `$table`")->fetch();
            $backup_sql .= $createTable[1] . ";\n\n";
            
            $rows = $cbt->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                $backup_sql .= "-- Data for table: $table\n";
                foreach ($rows as $row) {
                    $backup_sql .= "INSERT INTO `$table` VALUES(";
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $backup_sql .= implode(",", $values) . ");\n";
                }
            }
            $backup_sql .= "\n\n";
        }
        
        $backup_sql .= "-- ========================================================================\n";
        $backup_sql .= "-- BACKUP COMPLETED SUCCESSFULLY\n";
        $backup_sql .= "-- ========================================================================\n";
        
        if (file_put_contents($backup_file, $backup_sql)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Backup created successfully for both SFGS and CBT databases!',
                'filename' => basename($backup_file)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create backup file.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Backup failed: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Handle download request
if (isset($_POST['download_file'])) {
    $file = $_POST['download_file'];
    $file_path = "$backup_dir/$file";
    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}

// Handle delete request
if (isset($_POST['ajax_delete'])) {
    $file = $_POST['ajax_delete'];
    $file_path = "$backup_dir/$file";
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            echo json_encode(['status' => 'success', 'message' => 'Backup deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete backup.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Backup file not found!']);
    }
    exit;
}

// Fetch backups list
if (isset($_GET['action']) && $_GET['action'] == 'fetch_backups') {
    $backups = [];
    if (is_dir($backup_dir)) {
        $files = scandir($backup_dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $file_path = "$backup_dir/$file";
                $backups[] = [
                    'name' => $file,
                    'size' => filesize($file_path),
                    'date' => filemtime($file_path)
                ];
            }
        }
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }
    echo json_encode($backups);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" type="image/png" href="./img/logo.JPG">
    <title>Database Backup - SFGS</title>
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #64748b;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --background: #fafafa;
            --surface: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border: #f3f4f6;
            --border-hover: #e5e7eb;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --radius: 12px;
            --radius-sm: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.5;
            min-height: 100vh;
            font-size: 14px;
        }

        .session-bar {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8125rem;
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
            border-radius: var(--radius);
        }

        .session-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .session-timer {
            background: var(--background);
            color: var(--text-secondary);
            padding: 0.25rem 0.625rem;
            border-radius: 16px;
            font-weight: 500;
            font-size: 0.75rem;
            border: 1px solid var(--border);
        }

        .logout-btn {
            background: var(--error);
            color: white;
            border: none;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .app-layout {
            display: flex;
            min-height: calc(100vh - 3rem);
        }

        .sidebar {
            width: 240px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 1.5rem;
            flex-shrink: 0;
            box-shadow: var(--shadow);
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            letter-spacing: -0.025em;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.15s ease;
        }

        .sidebar-nav a:hover {
            background: var(--background);
            color: var(--primary);
        }

        .sidebar-nav a.active {
            background: var(--primary);
            color: white;
        }

        .main-content {
            flex: 1;
            padding: 1.5rem;
            overflow-x: auto;
        }

        .page-header {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--text-secondary);
        }

        .backup-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: #059669;
        }

        .btn-danger {
            background: var(--error);
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            background: #dc2626;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .backups-list {
            margin-top: 1rem;
        }

        .backup-item {
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .backup-item:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow);
        }

        .backup-info {
            flex: 1;
        }

        .backup-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .backup-meta {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .backup-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            animation: modalSlideIn 0.2s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .modal-icon.warning {
            background: #fef3c7;
            color: #d97706;
        }

        .modal-icon.info {
            background: #dbeafe;
            color: #2563eb;
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .modal-body {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .modal-footer .btn {
            min-width: 80px;
        }

        .bottom-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--surface);
            border-top: 1px solid var(--border);
            box-shadow: var(--shadow);
            z-index: 1000;
        }

        .bottom-nav {
            display: flex;
            justify-content: space-around;
            padding: 0.75rem 0;
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.75rem;
            min-width: 0;
            flex: 1;
        }

        .bottom-nav-item.active {
            color: var(--primary);
            font-weight: 600;
        }

        .bottom-nav-item svg {
            margin-bottom: 0.25rem;
        }

        @media (max-width: 768px) {
            .app-layout {
                flex-direction: column;
            }

            .sidebar {
                display: none;
            }

            .bottom-bar {
                display: block;
            }

            .main-content {
                padding: 1rem;
                padding-bottom: 5rem;
            }

            .backup-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .backup-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="session-bar">
        <div class="session-info">
            <span><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: text-bottom; margin-right: 0.25rem;"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg> Authenticated Session</span>
            <span class="session-timer" id="sessionTimer">5:00</span>
        </div>
        <button class="logout-btn" onclick="logout()">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.25rem;">
                <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
            </svg>
            Logout
        </button>
    </div>

    <div class="app-layout">
        <?php include 'navigation.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Database Backup</h1>
                <p>Create and manage backups for SFGS and CBT databases</p>
            </div>

            <div id="alertContainer"></div>

            <div class="backup-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">Create New Backup</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1rem; font-size: 0.875rem;">
                    Click the button below to create a complete backup of both SFGS and CBT databases.
                </p>
                <button class="btn btn-success" id="createBackupBtn" onclick="createBackup()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                    Create Backup (SFGS + CBT)
                </button>
            </div>

            <div class="backup-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">Available Backups</h3>
                <div class="backups-list" id="backupsList">
                    <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">Loading backups...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-icon" id="modalIcon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16" id="modalIconSvg"></svg>
                </div>
                <h3 class="modal-title" id="modalTitle"></h3>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer" id="modalFooter"></div>
        </div>
    </div>

    <script>
        let sessionTimeRemaining = 300;
        let sessionTimer;

        // Modal functions
        function showModal(title, message, type = 'info', buttons = []) {
            const modal = document.getElementById('modalOverlay');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            const modalIcon = document.getElementById('modalIcon');
            const modalIconSvg = document.getElementById('modalIconSvg');
            const modalFooter = document.getElementById('modalFooter');

            modalTitle.textContent = title;
            modalBody.textContent = message;

            // Set icon and color
            if (type === 'warning') {
                modalIcon.className = 'modal-icon warning';
                modalIconSvg.innerHTML = '<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>';
            } else {
                modalIcon.className = 'modal-icon info';
                modalIconSvg.innerHTML = '<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>';
            }

            // Add buttons
            modalFooter.innerHTML = '';
            buttons.forEach(btn => {
                const button = document.createElement('button');
                button.className = `btn ${btn.class}`;
                button.textContent = btn.text;
                button.onclick = () => {
                    closeModal();
                    if (btn.action) btn.action();
                };
                modalFooter.appendChild(button);
            });

            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('active');
        }

        // Close modal when clicking overlay
        document.getElementById('modalOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        function updateSessionTimer() {
            const minutes = Math.floor(sessionTimeRemaining / 60);
            const seconds = sessionTimeRemaining % 60;
            document.getElementById('sessionTimer').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (sessionTimeRemaining <= 0) {
                showModal(
                    'Session Expired',
                    'Your session has expired. You will be redirected to login.',
                    'info',
                    [{ text: 'OK', class: 'btn-primary', action: logout }]
                );
                return;
            }

            sessionTimeRemaining--;
        }

        function resetSessionTimer() {
            sessionTimeRemaining = 300;
            fetch('session_ping.php', { method: 'POST' });
        }

        function startSessionTimer() {
            sessionTimer = setInterval(updateSessionTimer, 1000);
        }

        function logout() {
            if (sessionTimer) {
                clearInterval(sessionTimer);
            }
            fetch('logout.php', { method: 'POST' })
                .then(() => {
                    window.location.href = 'login.php';
                });
        }

        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetSessionTimer, true);
        });

        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        async function createBackup() {
            const btn = document.getElementById('createBackupBtn');
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Creating backup...';

            try {
                const formData = new FormData();
                formData.append('ajax_backup', '1');

                const response = await fetch('backup.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert(result.message, 'success');
                    loadBackups();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Error creating backup: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        async function downloadBackup(filename) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'backup.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'download_file';
            input.value = filename;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        async function deleteBackup(filename) {
            showModal(
                'Delete Backup',
                `Are you sure you want to delete ${filename}? This action cannot be undone.`,
                'warning',
                [
                    { text: 'Cancel', class: 'btn-secondary' },
                    { text: 'Delete', class: 'btn-danger', action: () => confirmDeleteBackup(filename) }
                ]
            );
        }

        async function confirmDeleteBackup(filename) {
            try {
                const formData = new FormData();
                formData.append('ajax_delete', filename);

                const response = await fetch('backup.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert(result.message, 'success');
                    loadBackups();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Error deleting backup: ' + error.message, 'error');
            }
        }

        async function loadBackups() {
            try {
                const response = await fetch('backup.php?action=fetch_backups');
                const backups = await response.json();

                const backupsList = document.getElementById('backupsList');

                if (backups.length === 0) {
                    backupsList.innerHTML = '<p style="color: var(--text-secondary); text-align: center; padding: 2rem;">No backups available</p>';
                    return;
                }

                backupsList.innerHTML = backups.map(backup => `
                    <div class="backup-item">
                        <div class="backup-info">
                            <div class="backup-name">${backup.name}</div>
                            <div class="backup-meta">
                                ${formatFileSize(backup.size)} • ${formatDate(backup.date)}
                            </div>
                        </div>
                        <div class="backup-actions">
                            <button class="btn btn-primary btn-sm" onclick="downloadBackup('${backup.name}')">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                Download
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteBackup('${backup.name}')">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                document.getElementById('backupsList').innerHTML = '<p style="color: var(--error); text-align: center; padding: 2rem;">Error loading backups</p>';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function formatDate(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleString();
        }

        window.onload = function() {
            startSessionTimer();
            loadBackups();
        };
    </script>
</body>
</html>
