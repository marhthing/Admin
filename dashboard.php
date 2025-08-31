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

        .main-content {
            padding: 1.5rem;
            padding-bottom: 5rem;
            max-width: 100%;
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
            grid-template-columns: 1fr 1fr;
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
                grid-template-columns: 1fr 1fr;
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
            </div>

            <!-- Migration Controls Section -->
            <div class="migration-section">
                    <div class="migration-header">
                        <h3>Data Migration</h3>
                        <p>Intelligent synchronization between SFGS and CBT systems</p>
                    </div>
                    
                    <div class="security-notices">
                        <div class="security-alert">
                            <span class="alert-icon"><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: text-bottom; margin-right: 0.25rem;"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg></span>
                            <strong>Security Enhanced:</strong> All passwords from SFGS (plain text) will be securely hashed before insertion into CBT database.
                        </div>
                        <div class="alert">
                            <span class="alert-icon"><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: text-bottom; margin-right: 0.25rem;"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg></span>
                            Smart sync mode: Existing data will be preserved and only missing or incorrect data will be updated.
                        </div>
                    </div>

                    <div class="migration-controls">
                        <button class="migrate-btn" onclick="startMigration()" id="migrateBtn">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.5rem;">
                                <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                            </svg>
                            Start Smart Sync
                        </button>
                        <p class="migration-note">Click to begin intelligent data synchronization with detailed logging</p>
                    </div>
                </div>

            <div class="data-mapping-section">
                        <h3>Data Mapping Overview</h3>
                        <div class="mapping-grid">
                            <div class="mapping-item">
                                <div class="mapping-source">sfgs.users</div>
                                <div class="mapping-arrow">→</div>
                                <div class="mapping-target">cbt.users</div>
                                <div class="mapping-note">Admin accounts</div>
                            </div>
                            <div class="mapping-item">
                                <div class="mapping-source">sfgs.teachers</div>
                                <div class="mapping-arrow">→</div>
                                <div class="mapping-target">cbt.users</div>
                                <div class="mapping-note">Teacher accounts</div>
                            </div>
                            <div class="mapping-item">
                                <div class="mapping-source">sfgs.students</div>
                                <div class="mapping-arrow">→</div>
                                <div class="mapping-target">cbt.users</div>
                                <div class="mapping-note">Student accounts</div>
                            </div>
                            <div class="mapping-item">
                                <div class="mapping-source">sfgs.classes</div>
                                <div class="mapping-arrow">→</div>
                                <div class="mapping-target">cbt.class_levels</div>
                                <div class="mapping-note">Class structure</div>
                            </div>
                            <div class="mapping-item">
                                <div class="mapping-source">sfgs.sessions</div>
                                <div class="mapping-arrow">→</div>
                                <div class="mapping-target">cbt.sessions</div>
                                <div class="mapping-note">Academic sessions</div>
                            </div>
                            <div class="mapping-item">
                                <div class="mapping-source">Standard terms</div>
                                <div class="mapping-arrow">→</div>
                                <div class="mapping-target">cbt.terms</div>
                                <div class="mapping-note">Term structure</div>
                            </div>
                        </div>
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
                                <div class="stat-number" id="lastSyncTime">Never</div>
                                <div class="stat-label">Last Sync</div>
                            </div>
                        </div>
                        <div class="system-actions">
                            <button class="action-btn" onclick="refreshSystemStats()" id="refreshStatsBtn">
                                Refresh Stats
                            </button>
                            <button class="action-btn secondary" onclick="clearLogs()">
                                Clear Logs
                            </button>
                        </div>
                    </div>

            <div class="status-section">
                <h3>Migration Status & Detailed Logs</h3>

                <div class="status-grid">
                    <div class="status-item" id="status-connection">
                        <div class="status-icon status-pending">⏳</div>
                        <span class="status-text">Database Connection Check</span>
                    </div>
                    <div class="status-item" id="status-admin">
                        <div class="status-icon status-pending">⏳</div>
                        <span class="status-text">Admin Migration (Password Hashing)</span>
                    </div>
                    <div class="status-item" id="status-teachers">
                        <div class="status-icon status-pending">⏳</div>
                        <span class="status-text">Teachers Migration (Password Hashing)</span>
                    </div>
                    <div class="status-item" id="status-students">
                        <div class="status-icon status-pending">⏳</div>
                        <span class="status-text">Students Migration (Password Hashing)</span>
                    </div>
                    <div class="status-item" id="status-classes">
                        <div class="status-icon status-pending">⏳</div>
                        <span class="status-text">Classes Migration</span>
                    </div>
                    <div class="status-item" id="status-sessions">
                        <div class="status-icon status-pending">⏳</div>
                        <span class="status-text">Sessions Migration</span>
                    </div>
                    <div class="status-item" id="status-terms">
                        <div class="status-icon status-pending">⏳</div>
                        <span class="status-text">Terms Migration</span>
                    </div>
                </div>

                <div class="progress-container">
                    <div class="progress-text">Progress: <span id="progressText">0%</span></div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>

                <div class="summary-box" id="summaryBox">
                    <h4><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.5rem; vertical-align: text-bottom;"><path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V2.5A1.5 1.5 0 0 1 1.5 1H3a.5.5 0 0 1 0 1H1.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V13a.5.5 0 0 1 1 0v.5a1.5 1.5 0 0 1-1.5 1.5h-11zM7 11.5a.5.5 0 0 1-.5-.5V8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L5.793 8H3.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zM15 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793L10.646 5.646a.5.5 0 0 0 .708.708L14 3.707V6.5a.5.5 0 0 0 1 0v-4z"/></svg> Migration Summary</h4>
                    <div class="summary-grid" id="summaryContent"></div>
                </div>

                <div class="log-container">
                    <div class="log-area" id="logArea">
                        <div style="color: #94a3b8;">Detailed migration logs will appear here...</div>
                        <div style="color: #94a3b8;">Click "Start Smart Sync" to begin the secure process.</div>
                        <div style="color: #fbbf24;">Note: All passwords will be converted from plain text to secure hash.</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Session management
        let sessionTimeRemaining = 300; // 5 minutes
        let sessionTimer;

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

        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logMessage = `[${timestamp}] ${message}`;

            const logArea = document.getElementById('logArea');
            const logDiv = document.createElement('div');
            logDiv.textContent = logMessage;

            const colors = {
                error: '#f87171',
                success: '#4ade80',
                warning: '#fbbf24',
                info: '#e2e8f0',
                security: '#fb7185'
    

            logDiv.style.color = colors[type] || colors.info;
            logArea.appendChild(logDiv);
            logArea.scrollTop = logArea.scrollHeight;
        }

        function updateStatus(statusId, state) {
            const statusElement = document.getElementById(`status-${statusId}`);
            const icon = statusElement.querySelector('.status-icon');

            icon.className = `status-icon status-${state}`;

            const icons = {
                pending: '⏳',
                running: '⏳',
                success: '✓',
                error: '✗'
    

            icon.textContent = icons[state] || '⏳';
        }

        function updateProgress(percentage) {
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            progressFill.style.width = percentage + '%';
            progressText.textContent = Math.round(percentage) + '%';
        }

        function showMigrationSummary(data) {
            const summaryBox = document.getElementById('summaryBox');
            const summaryContent = document.getElementById('summaryContent');

            summaryContent.innerHTML = `
                <div class="summary-item">
                    <strong>Total Users</strong>
                    ${data.total || 0}
                </div>
                <div class="summary-item">
                    <strong>Admins</strong>
                    ${data.admins || 0}
                </div>
                <div class="summary-item">
                    <strong>Teachers</strong>
                    ${data.teachers || 0}
                </div>
                <div class="summary-item">
                    <strong>Students</strong>
                    ${data.students || 0}
                </div>
                <div class="summary-item">
                    <strong>Classes</strong>
                    ${data.classes || 0}
                </div>
                <div class="summary-item">
                    <strong>Sessions</strong>
                    ${data.sessions || 0}
                </div>
                <div class="summary-item">
                    <strong>Terms</strong>
                    ${data.terms || 0}
                </div>
                <div class="summary-item">
                    <strong>Passwords Hashed</strong>
                    ${data.passwords_hashed || 0}
                </div>
                <div class="summary-item">
                    <strong>Duration</strong>
                    ${data.duration || '0'}s
                </div>
            `;

            summaryBox.classList.add('show');
        }

        async function callMigrationAPI(action, data = {}) {
            try {
                const response = await fetch('migrate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action, ...data })
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Unknown error occurred');
                }

                return result;
            } catch (error) {
                throw new Error(`API request failed: ${error.message}`);
            }
        }

        async function startMigration() {
            const migrateBtn = document.getElementById('migrateBtn');
            const startTime = Date.now();

            // Reset UI
            migrateBtn.disabled = true;
            migrateBtn.innerHTML = '<span>⏳</span> Syncing...';
            document.getElementById('logArea').innerHTML = '';
            document.getElementById('summaryBox').classList.remove('show');
            updateProgress(0);

            // Reset all status indicators
            ['connection', 'admin', 'teachers', 'students', 'classes', 'sessions', 'terms'].forEach(id => {
                updateStatus(id, 'pending');
            });

            log('Starting secure smart synchronization process...', 'info');
            log('Security Mode: All passwords will be hashed before insertion', 'security');

            try {
                // Step 1: Test database connections
                log('Testing database connections...', 'info');
                updateStatus('connection', 'running');
                const connectionResult = await callMigrationAPI('test_connection');
                log('✓ Database connections successful', 'success');
                updateStatus('connection', 'success');
                updateProgress(10);

                // Step 2: Migrate admins
                log('Synchronizing admin users with password hashing...', 'info');
                updateStatus('admin', 'running');
                const adminResult = await callMigrationAPI('migrate_admins');
                log(`✓ ${adminResult.message}`, 'success');
                log(`Hashed ${adminResult.details?.passwords_hashed || 0} admin passwords`, 'security');
                updateStatus('admin', 'success');
                updateProgress(20);

                // Step 3: Migrate teachers
                log('Synchronizing teacher users with password hashing...', 'info');
                updateStatus('teachers', 'running');
                const teacherResult = await callMigrationAPI('migrate_teachers');
                log(`✓ ${teacherResult.message}`, 'success');
                log(`Hashed ${teacherResult.details?.passwords_hashed || 0} teacher passwords`, 'security');
                updateStatus('teachers', 'success');
                updateProgress(35);

                // Step 4: Migrate students
                log('Synchronizing student users with password hashing...', 'info');
                updateStatus('students', 'running');
                const studentResult = await callMigrationAPI('migrate_students');
                log(`✓ ${studentResult.message}`, 'success');
                log(`Hashed ${studentResult.details?.passwords_hashed || 0} student passwords`, 'security');
                updateStatus('students', 'success');
                updateProgress(50);

                // Step 5: Migrate classes
                log('Synchronizing classes...', 'info');
                updateStatus('classes', 'running');
                const classResult = await callMigrationAPI('migrate_classes');
                log(`✓ ${classResult.message}`, 'success');
                updateStatus('classes', 'success');
                updateProgress(65);

                // Step 6: Migrate sessions
                log('Synchronizing sessions...', 'info');
                updateStatus('sessions', 'running');
                const sessionResult = await callMigrationAPI('migrate_sessions');
                log(`✓ ${sessionResult.message}`, 'success');
                updateStatus('sessions', 'success');
                updateProgress(80);

                // Step 7: Migrate terms
                log('Synchronizing terms...', 'info');
                updateStatus('terms', 'running');
                const termResult = await callMigrationAPI('migrate_terms');
                log(`✓ ${termResult.message}`, 'success');
                updateStatus('terms', 'success');
                updateProgress(95);

                // Show completion summary
                const duration = Math.round((Date.now() - startTime) / 1000);
                const totalUsers = (adminResult.count || 0) + (teacherResult.count || 0) + (studentResult.count || 0);
                const totalPasswordsHashed = (adminResult.details?.passwords_hashed || 0) + 
                                           (teacherResult.details?.passwords_hashed || 0) + 
                                           (studentResult.details?.passwords_hashed || 0);

                log('Secure smart synchronization completed successfully!', 'success');
                log(`Total passwords securely hashed: ${totalPasswordsHashed}`, 'security');
                updateProgress(100);

                showMigrationSummary({
                    total: totalUsers,
                    admins: adminResult.count,
                    teachers: teacherResult.count,
                    students: studentResult.count,
                    classes: classResult.count,
                    sessions: sessionResult.count,
                    terms: termResult.count,
                    passwords_hashed: totalPasswordsHashed,
                    duration: duration
                });

            } catch (error) {
                log(`❌ Synchronization failed: ${error.message}`, 'error');

                // Mark any running status as error
                ['connection', 'admin', 'teachers', 'students', 'classes', 'sessions', 'terms'].forEach(id => {
                    const statusElement = document.getElementById(`status-${id}`);
                    const icon = statusElement.querySelector('.status-icon');
                    if (icon.classList.contains('status-running')) {
                        updateStatus(id, 'error');
                    }
                });
            } finally {
                migrateBtn.disabled = false;
                migrateBtn.innerHTML = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.5rem;"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/></svg> Start Smart Sync';
            }
        }

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
            log('System initialized and ready for secure synchronization.', 'info');
            log('Security: All SFGS passwords (plain text) will be hashed before CBT insertion.', 'security');
            log('Existing data will be preserved - only missing/incorrect data will be updated.', 'info');
            
            // Automatically test database connections on load
            setTimeout(() => {
                testDatabaseConnections();
                refreshSystemStats();
            }, 500);
            
            // Auto-refresh database status every 30 seconds
            setInterval(() => {
                testDatabaseConnections();
            }, 30000);

        
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
                    document.getElementById('lastSyncTime').textContent = result.stats.last_sync || 'Never';
                }
            } catch (error) {
                console.error('Failed to fetch stats:', error);
            } finally {
                refreshBtn.disabled = false;
                refreshBtn.textContent = 'Refresh Stats';
            }
        }
        
        function clearLogs() {
            document.getElementById('logArea').innerHTML = '';
            log('System initialized and ready for secure synchronization.', 'info');
            log('Security: All SFGS passwords (plain text) will be hashed before CBT insertion.', 'security');
            log('Existing data will be preserved - only missing/incorrect data will be updated.', 'info');
        }

    </script>
        </main>
    </div>
</body>
</html>