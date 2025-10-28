<?php
session_start();

// Simple authentication (Add proper authentication in production)
$admin_password = "rajathkiran"; // Change this!
if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['admin_password'])) {
        if ($_POST['admin_password'] === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $login_error = "Invalid password!";
        }
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                }
                .login-box {
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                    text-align: center;
                }
                .login-box h2 { margin-bottom: 20px; color: #333; }
                .login-box input {
                    width: 100%;
                    padding: 12px;
                    margin: 10px 0;
                    border: 2px solid #e0e0e0;
                    border-radius: 8px;
                    font-size: 14px;
                }
                .login-box button {
                    width: 100%;
                    padding: 12px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 16px;
                    cursor: pointer;
                    margin-top: 10px;
                }
                .error { color: red; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>🔐 Admin Login</h2>
                <form method="POST">
                    <input type="password" name="admin_password" placeholder="Enter Admin Password" required>
                    <button type="submit">Login</button>
                </form>
                <?php if (isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

$servername = "sql102.infinityfree.com";
$username = "if0_40272780";
$password = "RpO6nuMBS83c";
$dbname = "if0_40272780_students";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'delete':
            $id = intval($_GET['id']);
            $sql = "DELETE FROM registrations WHERE id = $id";
            echo json_encode(['success' => $conn->query($sql)]);
            exit;
            
        case 'update_status':
            $id = intval($_POST['id']);
            $status = $conn->real_escape_string($_POST['status']);
            $sql = "UPDATE registrations SET status = '$status' WHERE id = $id";
            echo json_encode(['success' => $conn->query($sql)]);
            exit;
            
        case 'export':
            $sql = "SELECT * FROM registrations ORDER BY registration_date DESC";
            $result = $conn->query($sql);
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="registrations_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Full Name', 'Email', 'Phone', 'DOB', 'Gender', 'Course', 'Address', 'Status', 'Registration Date']);
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
            
        case 'search':
            $search = $conn->real_escape_string($_GET['q']);
            $sql = "SELECT * FROM registrations WHERE 
                    full_name LIKE '%$search%' OR 
                    email LIKE '%$search%' OR 
                    phone LIKE '%$search%' OR 
                    course LIKE '%$search%'
                    ORDER BY registration_date DESC";
            $result = $conn->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit;
    }
}

// Get filter parameters
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build SQL query with filters
$sql = "SELECT * FROM registrations WHERE 1=1";
if ($course_filter) $sql .= " AND course = '" . $conn->real_escape_string($course_filter) . "'";
if ($status_filter) $sql .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
if ($date_filter) $sql .= " AND DATE(registration_date) = '" . $conn->real_escape_string($date_filter) . "'";
$sql .= " ORDER BY registration_date DESC";

$result = $conn->query($sql);

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN DATE(registration_date) = CURDATE() THEN 1 ELSE 0 END) as today,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved
    FROM registrations";
$stats = $conn->query($stats_sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Student Registrations</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .stat-card.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .stat-card h3 {
            font-size: 36px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-card p {
            opacity: 0.95;
            font-size: 14px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .filters select, .filters input[type="date"] {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-approved {
            background: #d1fae5;
            color: #059669;
        }

        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .action-btn {
            padding: 6px 12px;
            margin: 0 3px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .action-btn:hover {
            transform: scale(1.05);
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .no-data i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideDown 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-label {
            font-weight: 600;
            color: #667eea;
            min-width: 150px;
        }

        .detail-value {
            color: #333;
            flex: 1;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters {
                flex-direction: column;
            }

            .search-box {
                width: 100%;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-graduate"></i> Student Registration Dashboard</h1>
            <div class="header-actions">
                <button class="btn btn-success" onclick="exportData()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <button class="btn btn-secondary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <a href="index.html" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
                <a href="?logout=1" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $stats['total']; ?></h3>
                <p><i class="fas fa-users"></i> Total Registrations</p>
            </div>
            <div class="stat-card blue">
                <h3><?php echo $stats['today']; ?></h3>
                <p><i class="fas fa-calendar-day"></i> Today's Registrations</p>
            </div>
            <div class="stat-card orange">
                <h3><?php echo $stats['pending']; ?></h3>
                <p><i class="fas fa-clock"></i> Pending Reviews</p>
            </div>
            <div class="stat-card green">
                <h3><?php echo $stats['approved']; ?></h3>
                <p><i class="fas fa-check-circle"></i> Approved</p>
            </div>
        </div>

        <div class="filters">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by name, email, phone, or course...">
                <i class="fas fa-search"></i>
            </div>
            <select id="courseFilter" onchange="applyFilters()">
                <option value="">All Courses</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Information Technology">Information Technology</option>
                <option value="Electronics">Electronics</option>
                <option value="Mechanical">Mechanical</option>
                <option value="Civil">Civil</option>
            </select>
            <select id="statusFilter" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <input type="date" id="dateFilter" onchange="applyFilters()">
            <button class="btn btn-secondary" onclick="clearFilters()">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?php echo $row['id']; ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['course']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status'] ?? 'pending'); ?>">
                                    <?php echo $row['status'] ?? 'Pending'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['registration_date'])); ?></td>
                            <td>
                                <button class="action-btn btn-primary" onclick="viewDetails(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="action-btn btn-danger" onclick="deleteRecord(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-inbox"></i>
                <h2>No registrations found</h2>
                <p>Registrations will appear here once students submit the form.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for viewing details -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><i class="fas fa-user-circle"></i> Student Details</h2>
            <div id="detailContent"></div>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button class="btn btn-success" onclick="updateStatus('Approved')">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button class="btn btn-danger" onclick="updateStatus('Rejected')">
                    <i class="fas fa-times"></i> Reject
                </button>
                <button class="btn btn-secondary" onclick="updateStatus('Pending')">
                    <i class="fas fa-clock"></i> Mark Pending
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentRecordId = null;
        let searchTimeout = null;

        // Search functionality
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const searchTerm = $('#searchInput').val();
                if (searchTerm.length >= 2) {
                    searchRecords(searchTerm);
                } else if (searchTerm.length === 0) {
                    location.reload();
                }
            }, 500);
        });

        function searchRecords(term) {
            $.get('?action=search&q=' + encodeURIComponent(term), function(data) {
                updateTable(data);
            });
        }

        function updateTable(data) {
            const tbody = $('#dataTable tbody');
            tbody.empty();
            
            if (data.length === 0) {
                tbody.append('<tr><td colspan="8" class="no-data">No results found</td></tr>');
                return;
            }
            
            data.forEach(row => {
                const status = row.status || 'Pending';
                const tr = `
                    <tr data-id="${row.id}">
                        <td>${row.id}</td>
                        <td>${row.full_name}</td>
                        <td>${row.email}</td>
                        <td>${row.phone}</td>
                        <td>${row.course}</td>
                        <td><span class="status-badge status-${status.toLowerCase()}">${status}</span></td>
                        <td>${new Date(row.registration_date).toLocaleDateString()}</td>
                        <td>
                            <button class="action-btn btn-primary" onclick="viewDetails(${row.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn btn-danger" onclick="deleteRecord(${row.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(tr);
            });
        }

        function applyFilters() {
            const course = $('#courseFilter').val();
            const status = $('#statusFilter').val();
            const date = $('#dateFilter').val();
            
            const params = new URLSearchParams();
            if (course) params.append('course', course);
            if (status) params.append('status', status);
            if (date) params.append('date', date);
            
            window.location.href = '?' + params.toString();
        }

        function clearFilters() {
            window.location.href = window.location.pathname;
        }

        function viewDetails(id) {
            currentRecordId = id;
            const row = $(`tr[data-id="${id}"]`);
            
            $.get('get_details.php?id=' + id, function(data) {
                let html = '';
                for (const [key, value] of Object.entries(data)) {
                    if (key !== 'id') {
                        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        html += `
                            <div class="detail-row">
                                <div class="detail-label">${label}:</div>
                                <div class="detail-value">${value || 'N/A'}</div>
                            </div>
                        `;
                    }
                }
                $('#detailContent').html(html);
                $('#detailModal').fadeIn();
            }).fail(function() {
                alert('Error loading details');
            });
        }

        function closeModal() {
            $('#detailModal').fadeOut();
        }

        function updateStatus(status) {
            if (!currentRecordId) return;
            
            $.post('?action=update_status', {
                id: currentRecordId,
                status: status
            }, function(response) {
                if (response.success) {
                    alert('Status updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating status');
                }
            });
        }

        function deleteRecord(id) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                return;
            }
            
            $.get('?action=delete&id=' + id, function(response) {
                if (response.success) {
                    $(`tr[data-id="${id}"]`).fadeOut(300, function() {
                        $(this).remove();
                    });
                    alert('Record deleted successfully!');
                } else {
                    alert('Error deleting record');
                }
            });
        }

        function exportData() {
            window.location.href = '?action=export';
        }

        function refreshData() {
            location.reload();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
$conn->close();
?>
