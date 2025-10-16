<?php
// Secure PHP page access with a password
session_start();

// Set your desired password here
$secret_password = "SFGS2025"; 

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Check if the password has been submitted
if (isset($_POST['password'])) {
    if ($_POST['password'] === $secret_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        // Redirect to prevent form resubmission on refresh
        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect password!";
    }
}

// Check session timeout (3 minutes = 180 seconds)
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 180)) {
        session_destroy();
        header("Location: index.php?timeout=1");
        exit;
    }
    // Update last activity time
    $_SESSION['login_time'] = time();
}

// Check if the user is authenticated, if not, show the login form
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="./img/logo.JPG">
    <title>SFGS - Database Backup Management </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 50px 45px;
            width: 100%;
            max-width: 480px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #2563eb, #1e40af);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .brand-logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            border: 3px solid #f0f4f8;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        h1 {
            color: #1a202c;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            color: #4a5568;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 14px;
            background: #fafbfc;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            font-family: inherit;
        }

        .form-input:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            transform: translateY(-1px);
        }

        .login-btn {
            width: 100%;
            padding: 16px 22px;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
            font-family: inherit;
            letter-spacing: 0.5px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(37, 99, 235, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: linear-gradient(135deg, #fed7d7, #fbb6ce);
            color: #c53030;
            padding: 14px 18px;
            border-radius: 12px;
            margin-top: 24px;
            font-size: 14px;
            border-left: 4px solid #fc8181;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(252, 129, 129, 0.15);
        }

        .timeout-message {
            background: linear-gradient(135deg, #feebc8, #fed7aa);
            color: #dd6b20;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            border-left: 4px solid #f6ad55;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(246, 173, 85, 0.15);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 10px;
                padding: 40px 30px;
                max-width: 100%;
            }

            h1 {
                font-size: 26px;
            }

            .brand-logo {
                width: 90px;
                height: 90px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .login-container {
                padding: 35px 25px;
                border-radius: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .subtitle {
                font-size: 15px;
            }

            .brand-logo {
                width: 80px;
                height: 80px;
                margin-bottom: 20px;
            }

            .form-input {
                padding: 16px 18px;
                font-size: 16px;
            }

            .login-btn {
                padding: 16px 20px;
                font-size: 15px;
            }
        }

        @media (max-width: 360px) {
            .login-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 22px;
            }
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        function disableLoginButton() {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.disabled = true;
            loginBtn.innerHTML = `
                <div style="width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite; display: inline-block; margin-right: 8px;"></div>
                LOGGING IN...
            `;
        }
    </script>
</head>
<body>
    <div class="login-container">
        <div class="brand-section">
            <div class="brand-logo">
                <img src="img/logo.JPG" alt="SFGS Logo" />
            </div>
            <h1>SFGS DMS</h1>
            <p class="subtitle">Database Backup Management</p>
        </div>

        <?php if (isset($_GET['timeout'])): ?>
            <div class="timeout-message">
                Session expired due to inactivity. Please log in again.
            </div>
        <?php endif; ?>

        <form method="post" action="index.php" onsubmit="disableLoginButton()">
            <div class="form-group">
                <label class="form-label" for="password">Access Password</label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="Enter your password" required autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                LOGIN
            </button>
        </form>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
exit; // Stop the script here if not authenticated
}
?>

<?php  
date_default_timezone_set('Africa/Lagos');

$host = "sql100.infinityfree.com";  
$username = "if0_39795047";  
$password = "s5DsRv7k4e6Cz2";  
$database = "if0_39795047_sfgs";  
$backup_dir = "backups";  

if (!file_exists($backup_dir)) {  
    mkdir($backup_dir, 0777, true);  
}  

$connection = new mysqli($host, $username, $password, $database);  
if ($connection->connect_error) {  
    die("Connection failed: " . $connection->connect_error);  
}  

// Handle AJAX backup request
if (isset($_POST['ajax_backup'])) {
    $backup_file = "$backup_dir/backup-" . date("Y-m-d-H-i-s") . ".sql";  
    $tables = [];  

    $result = $connection->query("SHOW TABLES");  
    while ($row = $result->fetch_array()) {  
        $tables[] = $row[0];  
    }  

    $backup_sql = "";  
    foreach ($tables as $table) {  
        $result = $connection->query("SELECT * FROM $table");  
        $backup_sql .= "DROP TABLE IF EXISTS `$table`;\n";  

        $row2 = $connection->query("SHOW CREATE TABLE $table")->fetch_array();  
        $backup_sql .= $row2[1] . ";\n\n";  

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $backup_sql .= "INSERT INTO `$table` VALUES(";  
                foreach ($row as $key => $value) {  
                    $value = $connection->real_escape_string($value);  
                    $backup_sql .= "'$value',";  
                }  
                $backup_sql = rtrim($backup_sql, ",") . ");\n";  
            }
        }
        $backup_sql .= "\n\n";  
    }  

    if (file_put_contents($backup_file, $backup_sql)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Backup created successfully!',
            'filename' => basename($backup_file)
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create backup file.'
        ]);
    }
    exit;
}

// Handle other POST requests...
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
    } else {
        echo "File not found!";
    }

} elseif (isset($_POST['ajax_delete'])) {
    $file = $_POST['ajax_delete'];
    $file_path = "$backup_dir/$file";
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            echo json_encode(['status' => 'success', 'message' => 'File deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete file.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File not found!']);
    }
    exit;

} elseif (isset($_GET['action']) && $_GET['action'] == 'fetch_backups') {
    $backups = [];
    if (is_dir($backup_dir)) {
        $files = scandir($backup_dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $file_path = "$backup_dir/$file";
                $backups[] = [
                    'name' => $file,
                    'size' => filesize($file_path),
                    'date' => date("Y-m-d H:i:s", filemtime($file_path))
                ];
            }
        }
        // Sort by date, newest first
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
    }
    header('Content-Type: application/json');
    echo json_encode($backups);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFGS - SFGS - Database Backup Management</title>
        <link rel="icon" type="image/png" href="./img/logo.JPG">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --secondary-color: #1d4ed8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-bg: #1f2937;
            --card-bg: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Main Content */
        .main-content {
            min-height: 100vh;
        }

        /* Top Bar */
        .topbar {
            background: var(--card-bg);
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
        }

        .topbar-left h1 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .session-info {
            background: #f0f9ff;
            color: var(--info-color);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Content Area */
        .content-area {
            padding: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-title {
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stat-icon.primary { background: var(--primary-color); }
        .stat-icon.success { background: var(--success-color); }
        .stat-icon.warning { background: var(--warning-color); }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Action Section */
        .action-section {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .primary-btn {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(37, 99, 235, 0.4);
        }

        .primary-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Table Section */
        .table-section {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .table-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--border-color);
            background: #f9fafb;
        }

        .table-container {
            overflow-x: auto;
        }

        .backup-table {
            width: 100%;
            border-collapse: collapse;
        }

        .backup-table th,
        .backup-table td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .backup-table th {
            background: #f9fafb;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .backup-table tbody tr:hover {
            background: #f9fafb;
        }

        .backup-table tbody tr:last-child td {
            border-bottom: none;
        }

        .file-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .file-size {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .file-date {
            color: var(--text-secondary);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-info {
            background: var(--info-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        /* Loading and Empty States */
        .loading-state, .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-secondary);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color);
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 2000;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-bg);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow-lg);
            max-width: 450px;
            width: 90%;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .modal-body {
            margin-bottom: 25px;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-secondary {
            background: var(--border-color);
            color: var(--text-secondary);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 3000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: var(--shadow-lg);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success { background: var(--success-color); }
        .notification.error { background: var(--danger-color); }

        /* Responsive Design */
        @media (max-width: 768px) {
            .topbar {
                padding: 0 20px;
            }

            .topbar-left h1 {
                font-size: 16px;
            }

            .content-area {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }

        /* Session timeout warning */
        .timeout-warning {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-bg);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow-lg);
            z-index: 3000;
            display: none;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .timeout-warning.show {
            display: block;
        }

        .warning-icon {
            width: 80px;
            height: 80px;
            background: var(--warning-color);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1>SFGS</h1>
            </div>
            <div class="topbar-right">
                <div class="session-info">
                    <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                    </svg>
                    Session Active
                </div>
                <form method="post" style="display: inline;">
                    <button type="submit" name="logout" class="logout-btn">
                        <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                            <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Backups</span>
                        <div class="stat-icon primary">
                            <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5z"/>
                                <path d="M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value" id="total-backups">0</div>
                    <div class="stat-change">Available backup files</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Last Backup</span>
                        <div class="stat-icon success">
                            <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value" id="last-backup">Never</div>
                    <div class="stat-change" id="last-backup-time">No backups yet</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Size</span>
                        <div class="stat-icon warning">
                            <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5V5.6z"/>
                                <path d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64a.715.715 0 0 1 .012-.013l.354-.354-.354-.353a.5.5 0 0 1 .707-.707l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354a.512.512 0 0 1-.013.012A7 7 0 1 1 8 3a6 6 0 0 0 0 12z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value" id="total-size">0 KB</div>
                    <div class="stat-change">All backup files combined</div>
                </div>
            </div>

            <!-- Action Section -->
            <div class="action-section">
                <div class="section-header">
                    <h2 class="section-title">Quick Actions</h2>
                    <button class="primary-btn" onclick="createBackup()" id="backup-btn">
                        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                        </svg>
                        Create New Backup
                    </button>
                </div>
                <p>Generate a complete database backup with all tables and data. The backup file will be automatically timestamped and saved to the secure backup directory.</p>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                <div class="table-header">
                    <h2 class="section-title">Available Backups</h2>
                </div>
                <div class="table-container">
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backup-table-body">
                            <tr class="loading-state">
                                <td colspan="4">
                                    <div class="loading-spinner"></div>
                                    Loading backup files...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Deletion</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this backup file? This action cannot be undone.</p>
                <p><strong id="delete-filename"></strong></p>
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-danger" onclick="confirmDelete()">Delete File</button>
            </div>
        </div>
    </div>

    <!-- Session Timeout Warning -->
    <div class="timeout-warning" id="timeoutWarning">
        <div class="warning-icon">
            <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
        </div>
        <h3>Session Expiring Soon</h3>
        <p>Your session will expire in <span id="countdown">60</span> seconds due to inactivity.</p>
        <div style="margin-top: 20px;">
            <button class="primary-btn" onclick="extendSession()">Stay Logged In</button>
        </div>
    </div>

    <script>
        let deleteFileName = '';
        let sessionTimer;
        let warningTimer;
        let countdownInterval;
        let isIdle = false;
        const SESSION_TIMEOUT = 180000; // 3 minutes in milliseconds
        const WARNING_TIME = 60000; // Show warning 1 minute before timeout

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadBackups();
            initSessionTimeout();
        });

        // Session timeout management
        function initSessionTimeout() {
            resetSessionTimer();

            // Reset timer on user activity
            document.addEventListener('click', resetSessionTimer);
            document.addEventListener('keypress', resetSessionTimer);
            document.addEventListener('mousemove', resetSessionTimer);
            document.addEventListener('scroll', resetSessionTimer);
        }

        function resetSessionTimer() {
            clearTimeout(sessionTimer);
            clearTimeout(warningTimer);
            clearInterval(countdownInterval);
            hideTimeoutWarning();
            isIdle = false;

            // Set warning timer (2 minutes from now)
            warningTimer = setTimeout(showTimeoutWarning, SESSION_TIMEOUT - WARNING_TIME);

            // Set logout timer (3 minutes from now)
            sessionTimer = setTimeout(autoLogout, SESSION_TIMEOUT);
        }

        function showTimeoutWarning() {
            document.getElementById('timeoutWarning').classList.add('show');
            let countdown = 60;
            document.getElementById('countdown').textContent = countdown;

            countdownInterval = setInterval(() => {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    autoLogout();
                }
            }, 1000);
        }

        function hideTimeoutWarning() {
            document.getElementById('timeoutWarning').classList.remove('show');
            clearInterval(countdownInterval);
        }

        function extendSession() {
            resetSessionTimer();
            showNotification('Session extended successfully!', 'success');
        }

        function autoLogout() {
            window.location.href = 'index.php?timeout=1';
        }

        // Load backups
        function loadBackups() {
            fetch('index.php?action=fetch_backups')
                .then(response => response.json())
                .then(data => {
                    updateStats(data);
                    displayBackups(data);
                })
                .catch(error => {
                    console.error('Error loading backups:', error);
                    showNotification('Error loading backups', 'error');
                });
        }

        // Update statistics
        function updateStats(backups) {
            const totalCount = backups.length;
            let totalSize = 0;
            let lastBackupDate = 'Never';
            let lastBackupTime = 'No backups yet';

            if (totalCount > 0) {
                totalSize = backups.reduce((sum, backup) => sum + backup.size, 0);
                lastBackupDate = backups[0].date.split(' ')[0];
                lastBackupTime = backups[0].date;
            }

            document.getElementById('total-backups').textContent = totalCount;
            document.getElementById('last-backup').textContent = lastBackupDate;
            document.getElementById('last-backup-time').textContent = lastBackupTime;
            document.getElementById('total-size').textContent = formatFileSize(totalSize);
        }

        // Display backups in table
        function displayBackups(backups) {
            const tbody = document.getElementById('backup-table-body');

            if (backups.length === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state">
                        <td colspan="4">
                            <div style="text-align: center; padding: 40px;">
                                <svg width="48" height="48" style="opacity: 0.3; margin-bottom: 16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5z"/>
                                    <path d="M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                </svg>
                                <div>No backup files found</div>
                                <small>Create your first backup to get started</small>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = backups.map(backup => `
                <tr>
                    <td>
                        <div class="file-name">${backup.name}</div>
                    </td>
                    <td>
                        <div class="file-size">${formatFileSize(backup.size)}</div>
                    </td>
                    <td>
                        <div class="file-date">${backup.date}</div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-sm btn-info" onclick="downloadFile('${backup.name}')">
                                <svg width="14" height="14" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                Download
                            </button>
                            <button class="btn-sm btn-danger" onclick="showDeleteModal('${backup.name}')">
                                <svg width="14" height="14" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Create backup
        function createBackup() {
            const button = document.getElementById('backup-btn');
            button.disabled = true;
            button.innerHTML = `
                <div style="width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                Creating Backup...
            `;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ajax_backup=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    loadBackups();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error creating backup:', error);
                showNotification('Error creating backup', 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = `
                    <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                    </svg>
                    Create New Backup
                `;
            });
        }

        // Download file
        function downloadFile(filename) {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'index.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'download_file';
            input.value = filename;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

// Delete modal functions
        function showDeleteModal(filename) {
            deleteFileName = filename;
            document.getElementById('delete-filename').textContent = filename;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            deleteFileName = '';
        }

        function confirmDelete() {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ajax_delete=' + encodeURIComponent(deleteFileName)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    loadBackups();
                } else {
                    showNotification(data.message, 'error');
                }
                closeDeleteModal();
            })
            .catch(error => {
                console.error('Error deleting file:', error);
                showNotification('Error deleting file', 'error');
                closeDeleteModal();
            });
        }

        // Utility functions
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => notification.classList.add('show'), 100);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 3000);
        }



        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        };
    </script>
</body>
</html>