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

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }
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

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid var(--border);
                padding: 1rem;
            }
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            letter-spacing: -0.025em;
        }

        @media (max-width: 768px) {
            .sidebar .logo {
                margin-bottom: 1rem;
                font-size: 1.125rem;
            }
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

        .main-content-wrapper {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
            height: 100vh;
            background: var(--background);
        }

        @media (max-width: 768px) {
            .main-content-wrapper {
                padding: 1rem;
                height: auto;
                min-height: calc(100vh - 80px);
            }
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

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-bottom: 1rem;
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
            .main-content {
                grid-template-columns: 1fr;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Mobile navigation improvements */
        @media (max-width: 768px) {
            .sidebar-nav ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 0.5rem;
            }

            .sidebar-nav li {
                flex: 1;
                min-width: 0;
            }

            .sidebar-nav a {
                padding: 0.5rem;
                text-align: center;
                font-size: 0.8125rem;
            }

            .sidebar-nav a span {
                font-size: 1rem;
            }

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

            .main-content {
                gap: 1rem;
                margin-bottom: 1rem;
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
<div class="app-container">
    <aside class="sidebar">
        <div class="logo">CBT Sync</div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php" class="active"><span class="icon">📊</span> <span>Dashboard</span></a></li>
                <li><a href="results.php"><span class="icon">📈</span> <span>CBT Results</span></a></li>
                <!-- Add other navigation links here -->
            </ul>
        </nav>
    </aside>

    <div class="main-content-wrapper">
        <div class="session-bar">
            <div class="session-info">
                <span>🔐 Authenticated Session</span>
                <span class="session-timer" id="sessionTimer">5:00</span>
            </div>
            <button class="logout-btn" onclick="logout()">
                🚪 Logout
            </button>
        </div>

        <main class="container">
            <div class="header">
                <h1>Database Migration System</h1>
                <p>SFGS → CBT Data Synchronization</p>
            </div>

            <div class="main-content">
                <div class="info-section">
                    <h3>📋 Migration Overview</h3>
                    <div class="security-alert">
                        <span class="alert-icon">🔐</span>
                        <strong>Security Enhanced:</strong> All passwords from SFGS (plain text) will be securely hashed before insertion into CBT database.
                    </div>

                    <div class="alert">
                        <span class="alert-icon">⚠️</span>
                        Smart sync mode: Existing data will be preserved and only missing or incorrect data will be updated.
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Source Database</strong>
                            <span>if0_39795047_sfgs (Read-only)</span>
                        </div>
                        <div class="info-item">
                            <strong>Target Database</strong>
                            <span>if0_39795047_cbt (Update-only)</span>
                        </div>
                        <div class="info-item">
                            <strong>Admin Users</strong>
                            <span>sfgs.users → cbt.users (passwords hashed)</span>
                        </div>
                        <div class="info-item">
                            <strong>Teachers</strong>
                            <span>sfgs.teachers → cbt.users (passwords hashed)</span>
                        </div>
                        <div class="info-item">
                            <strong>Students</strong>
                            <span>sfgs.students → cbt.users (passwords hashed)</span>
                        </div>
                        <div class="info-item">
                            <strong>Classes</strong>
                            <span>sfgs.classes → cbt.class_levels</span>
                        </div>
                        <div class="info-item">
                            <strong>Sessions</strong>
                            <span>sfgs.sessions → cbt.sessions</span>
                        </div>
                        <div class="info-item">
                            <strong>Terms</strong>
                            <span>Standard terms → cbt.terms</span>
                        </div>
                    </div>
                </div>

                <div class="migration-controls">
                    <h3>🚀 Start Migration</h3>
                    <p style="margin-bottom: 1rem; color: var(--text-secondary);">
                        Click to begin intelligent data synchronization with detailed step logging
                    </p>
                    <button class="migrate-btn" onclick="startMigration()" id="migrateBtn">
                        <span>🔄</span>
                        Start Smart Sync
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
                    <h4>📊 Migration Summary</h4>
                    <div class="summary-grid" id="summaryContent"></div>
                </div>

                <div class="log-container">
                    <div class="log-area" id="logArea">
                        <div style="color: #94a3b8;">📋 Detailed migration logs will appear here...</div>
                        <div style="color: #94a3b8;">Click "Start Smart Sync" to begin the secure process.</div>
                        <div style="color: #fbbf24;">🔐 Note: All passwords will be converted from plain text to secure hash.</div>
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
            };

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
            };

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

            log('🚀 Starting secure smart synchronization process...', 'info');
            log('🔐 Security Mode: All passwords will be hashed before insertion', 'security');

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
                log(`🔐 Hashed ${adminResult.details?.passwords_hashed || 0} admin passwords`, 'security');
                updateStatus('admin', 'success');
                updateProgress(20);

                // Step 3: Migrate teachers
                log('Synchronizing teacher users with password hashing...', 'info');
                updateStatus('teachers', 'running');
                const teacherResult = await callMigrationAPI('migrate_teachers');
                log(`✓ ${teacherResult.message}`, 'success');
                log(`🔐 Hashed ${teacherResult.details?.passwords_hashed || 0} teacher passwords`, 'security');
                updateStatus('teachers', 'success');
                updateProgress(35);

                // Step 4: Migrate students
                log('Synchronizing student users with password hashing...', 'info');
                updateStatus('students', 'running');
                const studentResult = await callMigrationAPI('migrate_students');
                log(`✓ ${studentResult.message}`, 'success');
                log(`🔐 Hashed ${studentResult.details?.passwords_hashed || 0} student passwords`, 'security');
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

                log('🎉 Secure smart synchronization completed successfully!', 'success');
                log(`🔐 Total passwords securely hashed: ${totalPasswordsHashed}`, 'security');
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
                migrateBtn.innerHTML = '<span>🔄</span> Start Smart Sync';
            }
        }

        // Initialize
        window.onload = function() {
            startSessionTimer();
            log('System initialized and ready for secure synchronization.', 'info');
            log('🔐 Security: All SFGS passwords (plain text) will be hashed before CBT insertion.', 'security');
            log('Existing data will be preserved - only missing/incorrect data will be updated.', 'info');
        };
    </script>
</div>
</body>
</html>