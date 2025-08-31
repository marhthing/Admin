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

        .app-layout {
            display: flex;
            min-height: calc(100vh - 3rem);
        }

        /* Desktop Sidebar */
        .sidebar {
            width: 250px;
            background: var(--surface-color);
            border-right: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .nav-menu {
            padding: 1rem 0;
        }

        .nav-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .nav-item:hover {
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .nav-item.active {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        .nav-item i {
            margin-right: 0.75rem;
            width: 1.25rem;
        }

        /* Mobile Bottom Bar */
        .bottom-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--surface-color);
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
            color: var(--primary-color);
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
            background: var(--surface-color);
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
            background: var(--surface-color);
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
            border-color: var(--primary-color);
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
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .btn-danger {
            background: var(--error-color);
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .results-section {
            background: var(--surface-color);
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
            background-color: var(--surface-color);
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
            color: var(--error-color);
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
            <span>🔐 Authenticated Session</span>
            <span class="session-timer" id="sessionTimer">5:00</span>
        </div>
        <button class="logout-btn" onclick="logout()">
            🚪 Logout
        </button>
    </div>

    <div class="app-layout">
        <!-- Desktop Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>SFGS System</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-item">
                    <i>🏠</i>
                    Migration Dashboard
                </a>
                <a href="results.php" class="nav-item active">
                    <i>📊</i>
                    CBT Results
                </a>
            </div>
        </nav>

        <!-- Mobile Bottom Bar -->
        <nav class="bottom-bar">
            <div class="bottom-nav">
                <a href="dashboard.php" class="bottom-nav-item">
                    <i>🏠</i>
                    <span>Dashboard</span>
                </a>
                <a href="results.php" class="bottom-nav-item active">
                    <i>📊</i>
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
                                <option value="JSS1" <?= $class_filter === 'JSS1' ? 'selected' : '' ?>>JSS1</option>
                                <option value="JSS2" <?= $class_filter === 'JSS2' ? 'selected' : '' ?>>JSS2</option>
                                <option value="JSS3" <?= $class_filter === 'JSS3' ? 'selected' : '' ?>>JSS3</option>
                                <option value="SS1" <?= $class_filter === 'SS1' ? 'selected' : '' ?>>SS1</option>
                                <option value="SS2" <?= $class_filter === 'SS2' ? 'selected' : '' ?>>SS2</option>
                                <option value="SS3" <?= $class_filter === 'SS3' ? 'selected' : '' ?>>SS3</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="term">Term</label>
                            <select name="term" id="term">
                                <option value="">All Terms</option>
                                <option value="First" <?= $term_filter === 'First' ? 'selected' : '' ?>>First Term</option>
                                <option value="Second" <?= $term_filter === 'Second' ? 'selected' : '' ?>>Second Term</option>
                                <option value="Third" <?= $term_filter === 'Third' ? 'selected' : '' ?>>Third Term</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="session">Session</label>
                            <select name="session" id="session">
                                <option value="">All Sessions</option>
                                <option value="2024/2025" <?= $session_filter === '2024/2025' ? 'selected' : '' ?>>2024/2025</option>
                                <option value="2023/2024" <?= $session_filter === '2023/2024' ? 'selected' : '' ?>>2023/2024</option>
                                <option value="2022/2023" <?= $session_filter === '2022/2023' ? 'selected' : '' ?>>2022/2023</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="subject">Subject</label>
                            <select name="subject" id="subject">
                                <option value="">All Subjects</option>
                                <option value="Mathematics" <?= $subject_filter === 'Mathematics' ? 'selected' : '' ?>>Mathematics</option>
                                <option value="English" <?= $subject_filter === 'English' ? 'selected' : '' ?>>English</option>
                                <option value="Physics" <?= $subject_filter === 'Physics' ? 'selected' : '' ?>>Physics</option>
                                <option value="Chemistry" <?= $subject_filter === 'Chemistry' ? 'selected' : '' ?>>Chemistry</option>
                                <option value="Biology" <?= $subject_filter === 'Biology' ? 'selected' : '' ?>>Biology</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="assignment_type">Assignment Type</label>
                            <select name="assignment_type" id="assignment_type">
                                <option value="">All Types</option>
                                <option value="Test" <?= $assignment_type_filter === 'Test' ? 'selected' : '' ?>>Test</option>
                                <option value="Assignment" <?= $assignment_type_filter === 'Assignment' ? 'selected' : '' ?>>Assignment</option>
                                <option value="Exam" <?= $assignment_type_filter === 'Exam' ? 'selected' : '' ?>>Exam</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i>🔍</i>
                            Apply Filters
                        </button>
                        <a href="results.php" class="btn btn-secondary">
                            <i>🔄</i>
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
                    <i>📊</i>
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
                <p style="color: var(--error-color); font-weight: 600; margin-top: 1rem;">This action cannot be undone.</p>
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

        // Initialize
        window.onload = function() {
            startSessionTimer();
            loadResults();
        };
    </script>
</body>
</html>