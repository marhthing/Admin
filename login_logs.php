
<?php
require_once 'auth.php';
require_once 'db.php';

// Require authentication
requireAuth();

// Get session info
$sessionInfo = getSessionInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Logs - CBT Sync</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .filters-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
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

        .filter-group input, .filter-group select {
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: white;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .filter-group input:focus, .filter-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
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

        .logs-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .logs-table th,
        .logs-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .logs-table th {
            background: var(--background);
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .logs-table td {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .logs-table tr:hover {
            background: var(--background);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .charts-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .chart-container {
            background: var(--background);
            padding: 1rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            position: relative;
            height: 300px;
            display: flex;
            flex-direction: column;
        }

        .chart-container canvas {
            max-height: 250px;
        }

        .export-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
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

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 250px;
            }

            .chart-container canvas {
                max-height: 200px;
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
        <aside class="sidebar">
            <div class="logo">CBT Sync</div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/></svg></span> <span>Dashboard</span></a></li>
                    <li><a href="results.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V2.5A1.5 1.5 0 0 1 1.5 1H3a.5.5 0 0 1 0 1H1.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V13a.5.5 0 0 1 1 0v.5a1.5 1.5 0 0 1-1.5 1.5h-11zM7 11.5a.5.5 0 0 1-.5-.5V8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L5.793 8H3.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zM15 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793L10.646 5.646a.5.5 0 0 0 .708.708L14 3.707V6.5a.5.5 0 0 0 1 0v-4z"/></svg></span> <span>CBT Results</span></a></li>
                    <li><a href="login_logs.php" class="active"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm.256 7a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/><path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/></svg></span> <span>Login Logs</span></a></li>
                    <li><a href="login_logs.php" class="active"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm.256 7a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/><path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/></svg></span> <span>Login Logs</span></a></li>
                    <li><a href="backup.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 5.996V14H3s-1 0-1-1 1-4 6-4c.564 0 1.077.038 1.544.107a4.524 4.524 0 0 0-.803.918A10.46 10.46 0 0 0 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h5ZM9 13a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm4-2.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-.5.5h-1.5a.5.5 0 0 1 0-1H13V11a.5.5 0 0 1 .5-.5ZM13 7.5a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-1 0V8h-1a.5.5 0 0 1-.5-.5ZM5.5 5A.5.5 0 0 1 5 4.5V3a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H6V4a.5.5 0 0 1-.5.5Z"/></svg></span> <span>Backup</span></a></li>
                    <li><a href="settings.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg></span> <span>Settings</span></a></li>
                </ul>
                <a href="login_logs.php" class="bottom-nav-item active">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm.256 7a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/><path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/>
                    </svg>
                    <span>Logs</span>
                </a>
                <a href="backup.php" class="bottom-nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                    </svg>
                    <span>Backup</span>
                </a>
                <a href="settings.php" class="bottom-nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <nav class="bottom-bar">
            <div class="bottom-nav">
                <a href="dashboard.php" class="bottom-nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="results.php" class="bottom-nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V2.5A1.5 1.5 0 0 1 1.5 1H3a.5.5 0 0 1 0 1H1.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V13a.5.5 0 0 1 1 0v.5a1.5 1.5 0 0 1-1.5 1.5h-11zM7 11.5a.5.5 0 0 1-.5-.5V8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L5.793 8H3.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zM15 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793L10.646 5.646a.5.5 0 0 0 .708.708L14 3.707V6.5a.5.5 0 0 0 1 0v-4z"/>
                    </svg>
                    <span>Results</span>
                </a>
                <a href="login_logs.php" class="bottom-nav-item active">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm.256 7a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/><path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/>
                    </svg>
                    <span>Logs</span>
                </a>
                <a href="backup.php" class="bottom-nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                    </svg>
                    <span>Backup</span>
                </a>
                <a href="settings.php" class="bottom-nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                    </svg>
                    <span>Settings</span>
                </a>
            </div>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <h1>Login Logs</h1>
                <p>Monitor user login activity and system access</p>
            </div>

            <div class="filters-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">Filter Logs</h3>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="dateFilter">Date</label>
                        <input type="date" id="dateFilter">
                    </div>
                    <div class="filter-group">
                        <label for="userFilter">Search User</label>
                        <input type="text" id="userFilter" placeholder="Enter username...">
                    </div>
                    <div class="filter-group">
                        <label for="roleFilter">Role</label>
                        <select id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="logs-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="font-size: 1.125rem; font-weight: 600;">Login Activity</h3>
                    <div class="export-actions">
                        <button class="btn btn-primary" onclick="exportCSV()">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                                <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                            </svg>
                            Export CSV
                        </button>
                        <button class="btn btn-secondary" onclick="window.print()">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                            </svg>
                            Print
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>IP Address</th>
                                <th>Device Info</th>
                                <th>Login Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="logTable">
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">Loading logs...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination" id="pagination">
                    <button class="btn btn-secondary" id="prevPage" disabled>Previous</button>
                    <button class="btn btn-secondary" id="nextPage">Next</button>
                </div>
            </div>

            <div class="charts-section">
                <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">Login Analytics</h3>
                <div class="charts-grid">
                    <div class="chart-container">
                        <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-primary);">Daily Login Trend</h4>
                        <canvas id="loginTrendChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-primary);">Role Distribution</h4>
                        <canvas id="roleChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-primary);">Login Status</h4>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let sessionTimeRemaining = 300;
        let sessionTimer;

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px; padding: 1rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); animation: slideIn 0.3s ease;';
            
            if (type === 'success') {
                notification.style.background = '#dcfce7';
                notification.style.color = '#166534';
                notification.style.border = '1px solid #bbf7d0';
            } else if (type === 'error') {
                notification.style.background = '#fee2e2';
                notification.style.color = '#991b1b';
                notification.style.border = '1px solid #fecaca';
            } else {
                notification.style.background = '#dbeafe';
                notification.style.color = '#1e40af';
                notification.style.border = '1px solid #bfdbfe';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        let currentPage = 1;
        const limit = 10;
        let roleChartInstance = null;
        let trendChartInstance = null;
        let statusChartInstance = null;

        function updateSessionTimer() {
            const minutes = Math.floor(sessionTimeRemaining / 60);
            const seconds = sessionTimeRemaining % 60;
            document.getElementById('sessionTimer').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (sessionTimeRemaining <= 0) {
                showNotification('Session expired. You will be redirected to login.', 'info'); setTimeout(() => {
                logout(); }, 2000);
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

        async function fetchLogs() {
            try {
                const params = new URLSearchParams({
                    date: document.getElementById('dateFilter').value,
                    user: document.getElementById('userFilter').value,
                    role: document.getElementById('roleFilter').value,
                    limit: limit,
                    offset: (currentPage - 1) * limit
                });

                const response = await fetch(`fetch_login_logs.php?${params}`);
                const logs = await response.json();

                const tableBody = document.getElementById('logTable');
                
                if (logs.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No logs found</td></tr>';
                } else {
                    tableBody.innerHTML = logs.map(log => `
                        <tr>
                            <td>${log.username || 'N/A'}</td>
                            <td>${log.role || 'N/A'}</td>
                            <td>${log.ip_address || 'N/A'}</td>
                            <td>${log.device_info || 'N/A'}</td>
                            <td>${new Date(log.login_time).toLocaleString()}</td>
                            <td><span class="status-badge ${log.login_status === 'success' ? 'status-success' : 'status-failed'}">${log.login_status || 'Unknown'}</span></td>
                        </tr>
                    `).join('');
                }

                document.getElementById('prevPage').disabled = currentPage === 1;
                document.getElementById('nextPage').disabled = logs.length < limit;

                updateCharts(logs);
            } catch (error) {
                console.error('Error fetching logs:', error);
                document.getElementById('logTable').innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--error);">Error loading logs</td></tr>';
            }
        }

        function createChart(chartId, chartInstance, type, labels, data, label) {
            if (chartInstance !== null) {
                chartInstance.destroy();
            }

            const ctx = document.getElementById(chartId).getContext('2d');
            return new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: type === 'pie' ? ['#6366f1', '#ef4444', '#10b981'] : '#6366f1',
                        borderColor: type === 'line' ? '#6366f1' : undefined,
                        borderWidth: type === 'line' ? 2 : 1,
                        fill: type === 'line' ? false : undefined
                    }]
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: type === 'pie',
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: type !== 'pie' ? {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    } : {}
                }
            });
        }

        function updateCharts(logs) {
            const roles = { admin: 0, teacher: 0, student: 0 };
            const statuses = { success: 0, failed: 0 };
            const dates = {};

            logs.forEach(log => {
                if (log.role) roles[log.role] = (roles[log.role] || 0) + 1;
                if (log.login_status) statuses[log.login_status] = (statuses[log.login_status] || 0) + 1;

                const date = log.login_time.split(' ')[0];
                dates[date] = (dates[date] || 0) + 1;
            });

            roleChartInstance = createChart('roleChart', roleChartInstance, 'pie',
                Object.keys(roles), Object.values(roles), 'Role Distribution');

            statusChartInstance = createChart('statusChart', statusChartInstance, 'bar',
                Object.keys(statuses), Object.values(statuses), 'Login Status');

            trendChartInstance = createChart('loginTrendChart', trendChartInstance, 'line',
                Object.keys(dates), Object.values(dates), 'Daily Logins');
        }

        function exportCSV() {
            const table = document.querySelector('.logs-table');
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText);
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(row =>
                Array.from(row.cells).map(cell => cell.innerText)
            );

            let csv = [headers, ...rows].map(row => row.join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'login_logs.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        document.getElementById('dateFilter').addEventListener('change', () => { currentPage = 1; fetchLogs(); });
        document.getElementById('userFilter').addEventListener('input', () => { currentPage = 1; fetchLogs(); });
        document.getElementById('roleFilter').addEventListener('change', () => { currentPage = 1; fetchLogs(); });
        document.getElementById('prevPage').addEventListener('click', () => { currentPage--; fetchLogs(); });
        document.getElementById('nextPage').addEventListener('click', () => { currentPage++; fetchLogs(); });

        window.onload = function() {
            startSessionTimer();
            fetchLogs();
            setInterval(fetchLogs, 30000);
        };
    </script>
</body>
</html>
