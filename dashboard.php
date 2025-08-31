
<?php
require_once 'auth.php';

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
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --error-color: #dc2626;
            --warning-color: #d97706;
            --background-color: #f8fafc;
            --surface-color: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .session-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            box-shadow: var(--shadow-sm);
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
            background: rgba(255, 255, 255, 0.2);
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .header {
            background: var(--surface-color);
            border-radius: 0.75rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--text-secondary);
            font-size: 1.125rem;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-section {
            background: var(--surface-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .info-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .info-item {
            background: var(--background-color);
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .info-item strong {
            color: var(--text-primary);
            display: block;
            margin-bottom: 0.25rem;
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
            border: 1px solid var(--border-color);
        }

        .migrate-btn {
            background: var(--primary-color);
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
            background: var(--secondary-color);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .status-section {
            background: var(--surface-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
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
            margin-bottom: 1rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            padding: 0.875rem;
            background: var(--background-color);
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
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

        .status-pending { background: var(--secondary-color); }
        .status-running { 
            background: var(--warning-color); 
            animation: pulse 2s infinite;
        }
        .status-success { background: var(--success-color); }
        .status-error { background: var(--error-color); }

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
            height: 0.5rem;
            background: var(--border-color);
            border-radius: 0.25rem;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 0.25rem;
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
            background: #1e293b;
            color: #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            font-size: 0.875rem;
            height: 300px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            line-height: 1.5;
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
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
            display: none;
        }

        .summary-box.show {
            display: block;
        }

        .summary-box h4 {
            color: var(--success-color);
            font-weight: 600;
            margin-bottom: 0.75rem;
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
            color: var(--success-color);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .alert {
            background: #fef3cd;
            border: 1px solid #fde68a;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #92400e;
        }

        .alert-icon {
            display: inline-block;
            margin-right: 0.5rem;
        }

        .security-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            color: var(--error-color);
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

        @media (max-width: 768px) {
            .container {
                padding: 0.5rem;
            }

            .session-bar {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .header {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }

            .header h1 {
                font-size: 1.5rem;
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
    <div class="session-bar">
        <div class="session-info">
            <span>🔐 Authenticated Session</span>
            <span class="session-timer" id="sessionTimer">5:00</span>
        </div>
        <button class="logout-btn" onclick="logout()">
            🚪 Logout
        </button>
    </div>

    <div class="container">
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
                    <div style="color: #fbbf24;">🔐 Security Note: All passwords will be converted from plain text to secure hash.</div>
                </div>
            </div>
        </div>
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
</body>
</html>
