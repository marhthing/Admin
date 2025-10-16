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
    <title>SFGS to CBT Migration System</title>
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
            margin: 0;
        }



        /* Top Sections Grid Layout */
        .top-sections-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .top-sections-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        /* Mobile Bottom Bar */
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
            margin-bottom: 1.5rem;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .header {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            text-align: center;
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                margin-bottom: 1rem;
            }
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
            letter-spacing: -0.025em;
        }

        .header p {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.25rem;
            }
        }


        /* Database Status Styles */
        .status-overview {
            margin-bottom: 1.5rem;
        }

        .db-status-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .db-status-card h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
        }

        .db-connections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .db-connection {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            background: var(--background);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .connection-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .connection-indicator.pending {
            background: var(--secondary);
            animation: pulse 2s infinite;
        }

        .connection-indicator.connected {
            background: var(--success);
        }

        .connection-indicator.error {
            background: var(--error);
        }

        .connection-info {
            flex: 1;
        }

        .connection-info strong {
            display: block;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.125rem;
        }

        .connection-info span {
            display: block;
            color: var(--text-secondary);
            font-size: 0.75rem;
        }

        .connection-status {
            font-weight: 500 !important;
        }

        .test-connections-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.8125rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .test-connections-btn:hover {
            background: #475569;
            transform: translateY(-1px);
        }

        /* Migration Section Styles */
        .migration-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .migration-header {
            margin-bottom: 1rem;
        }

        .migration-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            letter-spacing: -0.025em;
        }

        .migration-header p {
            color: var(--text-secondary);
            font-size: 0.8125rem;
        }

        .security-notices {
            margin-bottom: 1.5rem;
        }

        .migration-note {
            margin-top: 0.5rem;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-align: center;
        }

        /* Data Mapping Styles */
        .data-mapping-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .data-mapping-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
        }

        .mapping-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .mapping-item {
            display: grid;
            grid-template-columns: 2fr auto 2fr 1.5fr;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem;
            background: var(--background);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .mapping-source, .mapping-target {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .mapping-arrow {
            color: var(--primary);
            font-weight: 600;
            text-align: center;
        }

        .mapping-note {
            color: var(--text-muted);
            font-size: 0.75rem;
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
        }

        /* System Stats Section */
        .system-stats-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .system-stats-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: var(--background);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .system-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .action-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .action-btn.secondary {
            background: var(--secondary);
        }

        .action-btn.secondary:hover {
            background: #475569;
        }

        @media (max-width: 768px) {

            .db-connections {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }



            .mapping-item {
                grid-template-columns: 1fr;
                gap: 0.5rem;
                text-align: center;
            }

            .mapping-arrow {
                transform: rotate(90deg);
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 0.5rem;
            }

            .system-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        .info-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        @media (max-width: 768px) {
            .info-section {
                padding: 1rem;
            }
        }

        .info-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.025em;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .info-item {
            background: var(--background);
            padding: 0.625rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .info-item strong {
            color: var(--text-primary);
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.8125rem;
            font-weight: 500;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .info-item span {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .migration-controls {
            background: var(--surface-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }

        .migrate-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
        }

        .migrate-btn:hover:not(:disabled) {
            background: var(--primary-hover);
            box-shadow: var(--shadow-md);
        }

        .migrate-btn:disabled {
            background: var(--secondary);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .status-section {
            background: var(--surface-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            grid-column: 1 / -1;
        }

        .status-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 0.75rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            padding: 0.875rem;
            background: var(--background-color);
            border-radius: 0.5rem;
            border: 1px solid var(--border);
            transition: all 0.2s ease;
        }

        .status-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            margin-right: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .status-pending { background: var(--secondary); }
        .status-running { 
            background: var(--warning); 
            animation: pulse 2s infinite;
        }
        .status-success { background: var(--success); }
        .status-error { background: var(--error); }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .status-text {
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .progress-container {
            margin: 1rem 0;
        }

        .progress-bar {
            width: 100%;
            height: 0.375rem;
            background: var(--border);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 4px;
        }

        .progress-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .log-container {
            margin-top: 1rem;
        }

        .log-area {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: var(--radius-sm);
            padding: 0.875rem;
            font-family: 'JetBrains Mono', 'SF Mono', Monaco, 'Cascadia Code', monospace;
            font-size: 0.8125rem;
            height: 280px;
            overflow-y: auto;
            border: 1px solid var(--border);
            line-height: 1.4;
        }

        .log-area::-webkit-scrollbar {
            width: 6px;
        }

        .log-area::-webkit-scrollbar-track {
            background: #334155;
            border-radius: 3px;
        }

        .log-area::-webkit-scrollbar-thumb {
            background: #64748b;
            border-radius: 3px;
        }

        .summary-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: var(--radius-sm);
            padding: 0.875rem;
            margin-top: 1rem;
            display: none;
        }

        .summary-box.show {
            display: block;
        }

        .summary-box h4 {
            color: var(--success);
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .summary-item {
            background: white;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #bbf7d0;
        }

        .summary-item strong {
            display: block;
            color: var(--success);
            font-weight: 500;
            margin-bottom: 0.25rem;
            font-size: 0.8125rem;
        }

        .alert {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: var(--radius-sm);
            padding: 0.875rem;
            margin-bottom: 1rem;
            color: #92400e;
            font-size: 0.8125rem;
        }

        .alert-icon {
            display: inline-block;
            margin-right: 0.5rem;
        }

        .security-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: var(--radius-sm);
            padding: 0.875rem;
            margin-bottom: 1rem;
            color: var(--error);
            font-size: 0.8125rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {

            .status-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Mobile navigation improvements */
        @media (max-width: 768px) {

            .session-bar {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
                padding: 0.625rem 0.875rem;
            }

            .session-info {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }
            }


            .info-section,
            .migration-controls,
            .status-section {
                padding: 1rem;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .log-area {
                height: 250px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.25rem;
            }

            .migrate-btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.875rem;
            }

            .info-item,
            .status-item {
                padding: 0.75rem;
            }

            .status-icon {
                width: 1.5rem;
                height: 1.5rem;
                margin-right: 0.75rem;
                font-size: 0.75rem;
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
                    <li><a href="dashboard.php" class="active"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/></svg></span> <span>Dashboard</span></a></li>
                    <li><a href="results.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V2.5A1.5 1.5 0 0 1 1.5 1H3a.5.5 0 0 1 0 1H1.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V13a.5.5 0 0 1 1 0v.5a1.5 1.5 0 0 1-1.5 1.5h-11zM7 11.5a.5.5 0 0 1-.5-.5V8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L5.793 8H3.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zM15 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793L10.646 5.646a.5.5 0 0 0 .708.708L14 3.707V6.5a.5.5 0 0 0 1 0v-4z"/></svg></span> <span>CBT Results</span></a></li>
                    <li><a href="login_logs.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm.256 7a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/><path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/></svg></span> <span>Login Logs</span></a></li>
                    <li><a href="backup.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 5.996V14H3s-1 0-1-1 1-4 6-4c.564 0 1.077.038 1.544.107a4.524 4.524 0 0 0-.803.918A10.46 10.46 0 0 0 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h5ZM9 13a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm4-2.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-.5.5h-1.5a.5.5 0 0 1 0-1H13V11a.5.5 0 0 1 .5-.5ZM13 7.5a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-1 0V8h-1a.5.5 0 0 1-.5-.5ZM5.5 5A.5.5 0 0 1 5 4.5V3a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H6V4a.5.5 0 0 1-.5.5Z"/></svg></span> <span>Backup</span></a></li>
                    <li><a href="settings.php"><span class="icon"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg></span> <span>Settings</span></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Mobile Bottom Bar -->
        <nav class="bottom-bar">
            <div class="bottom-nav">
                <a href="dashboard.php" class="bottom-nav-item active">
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
                <a href="login_logs.php" class="bottom-nav-item">
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
                <h1>Database Migration System</h1>
                <p>SFGS → CBT Data Synchronization</p>
            </div>


            <!-- Database Status Section -->
            <div class="db-status-card">
                <h3>Database Connection Status</h3>
                <div class="db-connections">
                    <div class="db-connection" id="sfgs-status">
                        <div class="connection-indicator pending" id="sfgs-indicator"></div>
                        <div class="connection-info">
                            <strong>SFGS Database</strong>
                            <span>if0_39795047_sfgs (Source)</span>
                            <span class="connection-status" id="sfgs-status-text">Checking...</span>
                        </div>
                    </div>
                    <div class="db-connection" id="cbt-status">
                        <div class="connection-indicator pending" id="cbt-indicator"></div>
                        <div class="connection-info">
                            <strong>CBT Database</strong>
                            <span>if0_39795047_cbt (Target)</span>
                            <span class="connection-status" id="cbt-status-text">Checking...</span>
                        </div>
                    </div>
                </div>
                <button class="test-connections-btn" onclick="testDatabaseConnections()" id="testConnectionsBtn">
                    Refresh Status
                </button>
            </div>

            <div class="system-stats-section">
                        <h3>System Statistics</h3>
                        <div class="stats-grid" id="systemStats">
                            <div class="stat-item">
                                <div class="stat-number" id="totalUsersCount">-</div>
                                <div class="stat-label">Total Users</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="totalClassesCount">-</div>
                                <div class="stat-label">Classes</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="totalSessionsCount">-</div>
                                <div class="stat-label">Sessions</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="totalSubjectsCount">-</div>
                                <div class="stat-label">Subjects</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="lastSyncTime">Never</div>
                                <div class="stat-label">Last Sync</div>
                            </div>
                        </div>
                        <div class="system-actions">
                            <button class="action-btn" onclick="refreshSystemStats()" id="refreshStatsBtn">
                                Refresh Stats
                            </button>
                        </div>
                    </div>

            
        </main>
    </div>

    <script>
        // Session management
        let sessionTimeRemaining = 300; // 5 minutes
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
            fetch('session_ping.php', { method: 'POST' }); // Ping server to reset session
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

        // Database connection testing
        async function testDatabaseConnections() {
            const testBtn = document.getElementById('testConnectionsBtn');
            const sfgsIndicator = document.getElementById('sfgs-indicator');
            const cbtIndicator = document.getElementById('cbt-indicator');
            const sfgsStatus = document.getElementById('sfgs-status-text');
            const cbtStatus = document.getElementById('cbt-status-text');

            // Set to checking state
            testBtn.disabled = true;
            testBtn.textContent = 'Testing...';
            sfgsIndicator.className = 'connection-indicator pending';
            cbtIndicator.className = 'connection-indicator pending';
            sfgsStatus.textContent = 'Testing connection...';
            cbtStatus.textContent = 'Testing connection...';

            try {
                const response = await fetch('check_connections.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Update SFGS status
                    if (result.connections.sfgs.status === 'connected') {
                        sfgsIndicator.className = 'connection-indicator connected';
                        sfgsStatus.textContent = 'Connected';
                        sfgsStatus.style.color = 'var(--success)';
                    } else {
                        sfgsIndicator.className = 'connection-indicator error';
                        sfgsStatus.textContent = 'Connection failed';
                        sfgsStatus.style.color = 'var(--error)';
                    }

                    // Update CBT status
                    if (result.connections.cbt.status === 'connected') {
                        cbtIndicator.className = 'connection-indicator connected';
                        cbtStatus.textContent = 'Connected';
                        cbtStatus.style.color = 'var(--success)';
                    } else {
                        cbtIndicator.className = 'connection-indicator error';
                        cbtStatus.textContent = 'Connection failed';
                        cbtStatus.style.color = 'var(--error)';
                    }
                } else {
                    // Both failed
                    sfgsIndicator.className = 'connection-indicator error';
                    cbtIndicator.className = 'connection-indicator error';
                    sfgsStatus.textContent = 'Test failed';
                    cbtStatus.textContent = 'Test failed';
                    sfgsStatus.style.color = 'var(--error)';
                    cbtStatus.style.color = 'var(--error)';
                }
            } catch (error) {
                console.error('Connection test failed:', error);
                sfgsIndicator.className = 'connection-indicator error';
                cbtIndicator.className = 'connection-indicator error';
                sfgsStatus.textContent = 'Test failed';
                cbtStatus.textContent = 'Test failed';
                sfgsStatus.style.color = 'var(--error)';
                cbtStatus.style.color = 'var(--error)';
            } finally {
                testBtn.disabled = false;
                testBtn.textContent = 'Refresh Status';
            }
        }

        // Auto-test connections on page load
        window.addEventListener('load', function() {
            testDatabaseConnections();
        });

        // Initialize
        window.onload = function() {
            startSessionTimer();

            // Automatically test database connections on load
            setTimeout(() => {
                testDatabaseConnections();
                refreshSystemStats();
            }, 500);

            // Auto-refresh database status every 30 seconds
            setInterval(() => {
                testDatabaseConnections();
            }, 30000);
        }

        // System statistics functions
        async function refreshSystemStats() {
            const refreshBtn = document.getElementById('refreshStatsBtn');
            refreshBtn.disabled = true;
            refreshBtn.textContent = 'Loading...';

            try {
                const response = await fetch('get_stats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('totalUsersCount').textContent = result.stats.total_users || '0';
                    document.getElementById('totalClassesCount').textContent = result.stats.total_classes || '0';
                    document.getElementById('totalSessionsCount').textContent = result.stats.total_sessions || '0';
                    document.getElementById('totalSubjectsCount').textContent = result.stats.total_subjects || '0';
                    document.getElementById('lastSyncTime').textContent = result.stats.last_sync || 'Never';
                }
            } catch (error) {
                console.error('Failed to fetch stats:', error);
            } finally {
                refreshBtn.disabled = false;
                refreshBtn.textContent = 'Refresh Stats';
            }
        }

        

    </script>
</body>
</html>