<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

// Handle delete request
if ($_POST['action'] ?? '' === 'delete_result') {
    header('Content-Type: application/json');

    try {
        $result_id = $_POST['result_id'] ?? '';
        if (!$result_id) {
            throw new Exception('Result ID is required');
        }

        $cbt = createConnection('cbt');
        $stmt = $cbt->prepare("DELETE FROM test_results WHERE id = ?");
        $stmt->execute([$result_id]);

        echo json_encode(['success' => true, 'message' => 'Result deleted successfully']);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Get filter parameters
$class_filter = $_GET['class'] ?? '';
$term_filter = $_GET['term'] ?? '';
$session_filter = $_GET['session'] ?? '';
$subject_filter = $_GET['subject'] ?? '';
$assignment_type_filter = $_GET['assignment_type'] ?? '';

// Get session info
$sessionInfo = getSessionInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT Results Management - SFGS System</title>
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
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-weight: 600;
        }

        .logout-btn {
            background: var(--error);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .app-layout {
            display: flex;
            min-height: calc(100vh - 3rem);
        }

        /* Desktop Sidebar */
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

        .sidebar-nav a span {
            font-size: 1.125rem;
        }

        /* Mobile Bottom Bar */
        .bottom-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--surface);
            border-top: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
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

        .bottom-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }

        .main-content {
            flex: 1;
            padding: 1.5rem;
            overflow-x: auto;
        }

        .page-header {
            background: var(--surface);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
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

        .filters-section {
            background: var(--surface);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .filter-group select {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background: white;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .filter-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
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

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .btn-danger {
            background: var(--error);
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .results-section {
            background: var(--surface);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .results-count {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .results-table th,
        .results-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .results-table th {
            background: var(--background-color);
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .results-table td {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .results-table tr:hover {
            background: var(--background-color);
        }

        .score-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .score-excellent { background: #dcfce7; color: #166534; }
        .score-good { background: #dbeafe; color: #1e40af; }
        .score-average { background: #fef3c7; color: #92400e; }
        .score-poor { background: #fee2e2; color: #991b1b; }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: var(--surface);
            margin: 15% auto;
            padding: 2rem;
            border-radius: 0.75rem;
            width: 90%;
            max-width: 400px;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-header i {
            color: var(--error);
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .modal-body {
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .app-layout {
                flex-direction: column;
            }

            .sidebar {
                display: none;
            }

            .session-bar {
                margin-bottom: 0.5rem;
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }

            .bottom-bar {
                display: block;
            }

            .main-content {
                padding: 1rem;
                padding-bottom: 5rem;
            }

            .page-header {
                padding: 1rem;
            }

            .filters-section {
                padding: 1rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }

            .results-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .modal-content {
                margin: 25% auto;
                width: 95%;
                padding: 1.5rem;
            }

            .modal-actions {
                flex-direction: column-reverse;
            }
        }

        @media (max-width: 480px) {
            .results-table {
                font-size: 0.75rem;
            }

            .results-table th,
            .results-table td {
                padding: 0.5rem 0.25rem;
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
        <!-- Desktop Sidebar -->
        <aside class="sidebar">
            <div class="logo">CBT Sync</div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/></svg></span> <span>Dashboard</span></a></li>
                    <li><a href="results.php" class="active"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V2.5A1.5 1.5 0 0 1 1.5 1H3a.5.5 0 0 1 0 1H1.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V13a.5.5 0 0 1 1 0v.5a1.5 1.5 0 0 1-1.5 1.5h-11zM7 11.5a.5.5 0 0 1-.5-.5V8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L5.793 8H3.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zM15 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793L10.646 5.646a.5.5 0 0 0 .708.708L14 3.707V6.5a.5.5 0 0 0 1 0v-4z"/></svg></span> <span>CBT Results</span></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Mobile Bottom Bar -->
        <nav class="bottom-bar">
            <div class="bottom-nav">
                <a href="dashboard.php" class="bottom-nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="results.php" class="bottom-nav-item active">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V2.5A1.5 1.5 0 0 1 1.5 1H3a.5.5 0 0 1 0 1H1.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V13a.5.5 0 0 1 1 0v.5a1.5 1.5 0 0 1-1.5 1.5h-11zM7 11.5a.5.5 0 0 1-.5-.5V8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L5.793 8H3.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zM15 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793L10.646 5.646a.5.5 0 0 0 .708.708L14 3.707V6.5a.5.5 0 0 0 1 0v-4z"/>
                    </svg>
                    <span>Results</span>
                </a>
            </div>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <h1>CBT Results Management</h1>
                <p>View, filter, and manage student test results</p>
            </div>

            <div class="filters-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">Filter Results</h3>
                <form method="GET" action="results.php">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="class">Class</label>
                            <select name="class" id="class">
                                <option value="">All Classes</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="term">Term</label>
                            <select name="term" id="term">
                                <option value="">All Terms</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="session">Session</label>
                            <select name="session" id="session">
                                <option value="">All Sessions</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="subject">Subject</label>
                            <select name="subject" id="subject">
                                <option value="">All Subjects</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="assignment_type">Assignment Type</label>
                            <select name="assignment_type" id="assignment_type">
                                <option value="">All Types</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                            </svg>
                            Apply Filters
                        </button>
                        <a href="results.php" class="btn btn-secondary">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                            </svg>
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>

            <div class="results-section">
                <div class="results-header">
                    <h3 style="font-size: 1.125rem; font-weight: 600;">Student Results</h3>
                    <div class="results-count" id="resultsCount">Loading...</div>
                </div>

                <div class="table-container">
                    <table class="results-table" id="resultsTable">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Registration Number</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Assignment Type</th>
                                <th>Score</th>
                                <th>Total</th>
                                <th>Percentage</th>
                                <th>Term</th>
                                <th>Session</th>
                                <th>Date Taken</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTableBody">
                            <!-- Results will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <div class="empty-state" id="emptyState" style="display: none;">
                    <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16" style="opacity: 0.5; margin-bottom: 1rem;">
                        <path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V2.5A1.5 1.5 0 0 1 1.5 1H3a.5.5 0 0 1 0 1H1.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V13a.5.5 0 0 1 1 0v.5a1.5 1.5 0 0 1-1.5 1.5h-11zM7 11.5a.5.5 0 0 1-.5-.5V8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L5.793 8H3.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zM15 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793L10.646 5.646a.5.5 0 0 0 .708.708L14 3.707V6.5a.5.5 0 0 0 1 0v-4z"/>
                    </svg>
                    <h3>No Results Found</h3>
                    <p>No test results match your current filters. Try adjusting your search criteria.</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i>⚠️</i>
                <h3>Confirm Delete</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this result?</p>
                <p><strong>Student:</strong> <span id="deleteStudentName"></span></p>
                <p><strong>Subject:</strong> <span id="deleteSubject"></span></p>
                <p style="color: var(--error); font-weight: 600; margin-top: 1rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Result</button>
            </div>
        </div>
    </div>

    <script>
        // Session management (same as dashboard)
        let sessionTimeRemaining = 300;
        let sessionTimer;
        let deleteResultId = null;

        function updateSessionTimer() {
            const minutes = Math.floor(sessionTimeRemaining / 60);
            const seconds = sessionTimeRemaining % 60;
            document.getElementById('sessionTimer').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (sessionTimeRemaining <= 0) {
                alert('Session expired. You will be redirected to login.');
                logout();
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

        // Reset session timer on user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetSessionTimer, true);
        });

        // Get score badge class
        function getScoreBadgeClass(percentage) {
            if (percentage >= 80) return 'score-excellent';
            if (percentage >= 70) return 'score-good';
            if (percentage >= 50) return 'score-average';
            return 'score-poor';
        }

        // Load results based on current filters
        async function loadResults() {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const queryString = urlParams.toString();

                const response = await fetch(`fetch_results.php?${queryString}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                const resultsTableBody = document.getElementById('resultsTableBody');
                const resultsCount = document.getElementById('resultsCount');
                const emptyState = document.getElementById('emptyState');
                const resultsTable = document.getElementById('resultsTable');

                resultsCount.textContent = `${data.results.length} result(s) found`;

                if (data.results.length === 0) {
                    resultsTable.style.display = 'none';
                    emptyState.style.display = 'block';
                    return;
                }

                resultsTable.style.display = 'table';
                emptyState.style.display = 'none';

                resultsTableBody.innerHTML = data.results.map(result => {
                    const percentage = result.total_marks > 0 ? Math.round((result.score / result.total_marks) * 100) : 0;
                    const badgeClass = getScoreBadgeClass(percentage);

                    return `
                        <tr>
                            <td>${result.student_name || 'N/A'}</td>
                            <td>${result.reg_number || 'N/A'}</td>
                            <td>${result.class || 'N/A'}</td>
                            <td>${result.subject || 'N/A'}</td>
                            <td>${result.assignment_type || 'N/A'}</td>
                            <td>${result.score || 0}</td>
                            <td>${result.total_marks || 0}</td>
                            <td><span class="score-badge ${badgeClass}">${percentage}%</span></td>
                            <td>${result.term || 'N/A'}</td>
                            <td>${result.session || 'N/A'}</td>
                            <td>${result.date_taken ? new Date(result.date_taken).toLocaleDateString() : 'N/A'}</td>
                            <td>
                                <button class="btn btn-danger" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;" 
                                        onclick="showDeleteModal(${result.id}, '${result.student_name}', '${result.subject}')">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

            } catch (error) {
                console.error('Error loading results:', error);
                document.getElementById('resultsCount').textContent = 'Error loading results';
            }
        }

        // Show delete confirmation modal
        function showDeleteModal(resultId, studentName, subject) {
            deleteResultId = resultId;
            document.getElementById('deleteStudentName').textContent = studentName;
            document.getElementById('deleteSubject').textContent = subject;
            document.getElementById('deleteModal').style.display = 'block';
        }

        // Close delete modal
        function closeDeleteModal() {
            deleteResultId = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Confirm delete
        async function confirmDelete() {
            if (!deleteResultId) return;

            try {
                const response = await fetch('results.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_result&result_id=${deleteResultId}`
                });

                const data = await response.json();

                if (data.success) {
                    closeDeleteModal();
                    loadResults(); // Reload results
                    alert('Result deleted successfully');
                } else {
                    alert('Error deleting result: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting result:', error);
                alert('Error deleting result. Please try again.');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

        // Load filter options from database
        async function loadFilterOptions() {
            try {
                const response = await fetch('get_filter_options.php');
                const data = await response.json();

                if (!data.success) {
                    console.error('Failed to load filter options:', data.message);
                    return;
                }

                const filters = data.filters;
                const urlParams = new URLSearchParams(window.location.search);

                // Populate classes
                const classSelect = document.getElementById('class');
                filters.classes.forEach(cls => {
                    const option = document.createElement('option');
                    option.value = cls.name;
                    option.textContent = cls.display_name || cls.name;
                    if (urlParams.get('class') === cls.name) {
                        option.selected = true;
                    }
                    classSelect.appendChild(option);
                });

                // Populate terms
                const termSelect = document.getElementById('term');
                filters.terms.forEach(term => {
                    const option = document.createElement('option');
                    option.value = term.name;
                    option.textContent = term.name + ' Term';
                    if (urlParams.get('term') === term.name) {
                        option.selected = true;
                    }
                    termSelect.appendChild(option);
                });

                // Populate sessions
                const sessionSelect = document.getElementById('session');
                filters.sessions.forEach(session => {
                    const option = document.createElement('option');
                    option.value = session.name;
                    option.textContent = session.name;
                    if (urlParams.get('session') === session.name) {
                        option.selected = true;
                    }
                    sessionSelect.appendChild(option);
                });

                // Populate subjects
                const subjectSelect = document.getElementById('subject');
                filters.subjects.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.name;
                    option.textContent = subject.name;
                    if (urlParams.get('subject') === subject.name) {
                        option.selected = true;
                    }
                    subjectSelect.appendChild(option);
                });

                // Populate assignment types
                const assignmentTypeSelect = document.getElementById('assignment_type');
                filters.assignment_types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.test_type;
                    option.textContent = type.test_type.charAt(0).toUpperCase() + type.test_type.slice(1);
                    if (urlParams.get('assignment_type') === type.test_type) {
                        option.selected = true;
                    }
                    assignmentTypeSelect.appendChild(option);
                });

            } catch (error) {
                console.error('Error loading filter options:', error);
            }
        }

        // Initialize
        window.onload = function() {
            startSessionTimer();
            loadFilterOptions();
            loadResults();
        };
    </script>
</body>
</html>