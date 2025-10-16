<?php
date_default_timezone_set('Africa/Lagos');

require_once("includes/connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Logs</title>
    <link rel="stylesheet" href="css/bootstrap.css">
        <link rel="icon" type="image/png" href="./img/logo.JPG">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

    <style>
        body { background-color: #f7f7f7; font-family: Arial, sans-serif; }
        .container { margin-top: 20px; }
        .table-container { overflow-x: auto; }
        .pagination { display: flex; justify-content: center; margin-top: 10px; }
        canvas { max-width: 100%; margin: 20px 0; }
        .btn-group { display: flex; justify-content: center; gap: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Login Logs</h2>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="date" id="dateFilter" class="form-control">
            </div>
            <div class="col-md-4">
                <input type="text" id="userFilter" class="form-control" placeholder="Search User">
            </div>
            <div class="col-md-4">
                <select id="roleFilter" class="form-control">
                    <option value="">Filter by Role</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="table-container">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>IP Address</th>
                        <th>Device Info</th>
                        <th>Login Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="logTable"></tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button id="prevPage" class="btn btn-secondary" disabled>Previous</button>
            <button id="nextPage" class="btn btn-secondary">Next</button>
        </div>

        <!-- Export Buttons -->
        <div class="mt-3 btn-group">
            <button class="btn btn-primary" onclick="exportCSV()">Export CSV</button>
            <button class="btn btn-danger" onclick="exportPDF()">Export PDF</button>
        </div>

        <!-- Charts -->
        <h3 class="text-center mt-4">Login Analytics</h3>
        <canvas id="loginTrendChart"></canvas>
        <canvas id="roleChart"></canvas>
        <canvas id="statusChart"></canvas>
    </div>

    <script>
        let currentPage = 1;
const limit = 10;
let roleChartInstance = null;
let trendChartInstance = null;
let statusChartInstance = null;

function fetchLogs() {
    $.ajax({
        url: 'fetch_logs.php',
        type: 'GET',
        data: {
            date: $('#dateFilter').val(),
            user: $('#userFilter').val(),
            role: $('#roleFilter').val(),
            limit: limit,
            offset: (currentPage - 1) * limit
        },
        success: function(response) {
            try {
                const logs = JSON.parse(response);
                let tableContent = logs.map(log => `
                    <tr>
                        <td>${log.username || "N/A"}</td>
                        <td>${log.role}</td>
                        <td>${log.ip_address}</td>
                        <td>${log.device_info}</td>
                        <td>${new Date(log.login_time).toLocaleString()}</td>
                        <td>${log.login_status || "Unknown"}</td>
                    </tr>
                `).join("");
                $('#logTable').html(tableContent);

                $('#prevPage').prop('disabled', currentPage === 1);
                $('#nextPage').prop('disabled', logs.length < limit);

                updateCharts(logs);
            } catch (error) {
                console.error("Error parsing log data:", error);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching logs:", error);
        }
    });
}

function exportCSV() {
    let table = document.querySelector("table");
    let data = [];

    let headers = Array.from(table.querySelectorAll("thead th")).map(th => th.innerText);
    data.push(headers);

    table.querySelectorAll("tbody tr").forEach(row => {
        let rowData = Array.from(row.cells).map(cell => cell.innerText);
        data.push(rowData);
    });

    let csv = Papa.unparse(data);
    let blob = new Blob([csv], { type: "text/csv" });
    let link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "login_logs.csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportPDF() {
    let { jsPDF } = window.jspdf;
    let doc = new jsPDF();
    doc.text("Login Logs", 14, 15);
    doc.autoTable({
        head: [["User", "Role", "IP Address", "Device Info", "Login Time", "Status"]],
        body: Array.from(document.querySelectorAll("tbody tr")).map(row =>
            Array.from(row.cells).map(cell => cell.innerText)
        ),
        startY: 20,
        theme: "grid",
    });
    doc.save("login_logs.pdf");
}

function createChart(chartId, chartInstance, type, labels, data, label) {
    // Destroy the existing chart if it exists
    if (chartInstance !== null) {
        chartInstance.destroy();
    }

    // Create a new chart
    let ctx = document.getElementById(chartId).getContext('2d');
    return new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: ["#007bff", "#dc3545", "#28a745"]
            }]
        },
        options: { responsive: true }
    });
}

function updateCharts(logs) {
    let roles = { admin: 0, teacher: 0, student: 0 };
    let statuses = { success: 0, failed: 0 };
    let dates = {};

    logs.forEach(log => {
        roles[log.role] = (roles[log.role] || 0) + 1;
        statuses[log.login_status] = (statuses[log.login_status] || 0) + 1;

        let date = log.login_time.split(" ")[0];
        dates[date] = (dates[date] || 0) + 1;
    });

    // Update Charts
    roleChartInstance = createChart("roleChart", roleChartInstance, "pie",
        Object.keys(roles), Object.values(roles), "Role Distribution");

    statusChartInstance = createChart("statusChart", statusChartInstance, "bar",
        Object.keys(statuses), Object.values(statuses), "Login Status");

    trendChartInstance = createChart("loginTrendChart", trendChartInstance, "line",
        Object.keys(dates), Object.values(dates), "Daily Logins");
}

// Event Listeners
$('#dateFilter, #userFilter, #roleFilter').on('change keyup', fetchLogs);
$('#prevPage').click(() => { currentPage--; fetchLogs(); });
$('#nextPage').click(() => { currentPage++; fetchLogs(); });

// Auto-refresh every 5 seconds
setInterval(fetchLogs, 5000);
fetchLogs();

    </script>
</body>
</html>
