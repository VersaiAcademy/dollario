<?php
// Ensure the correct path is provided
include('../templates/sidebar.php'); 
include('../templates/header.php'); // Adjust the path if needed
?>

<?php
// DB connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "dollario_admin";

// Fix: use consistent connection variable name
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Apply filters
$conditions = [];

if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $conditions[] = "status = '$status'";
}

if (!empty($_GET['user_type'])) {
    $userType = $conn->real_escape_string($_GET['user_type']);
    $conditions[] = "user_type = '$userType'";
}

if (!empty($_GET['registration_date'])) {
    $dateFilter = $_GET['registration_date'];
    if ($dateFilter == 'today') {
        $conditions[] = "DATE(created_at) = CURDATE()";
    } elseif ($dateFilter == 'week') {
        $conditions[] = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($dateFilter == 'month') {
        $conditions[] = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
    }
}

$whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Example of correct query, assuming you meant 'username' instead of 'name'
// Corrected query, adjust according to actual column names
$sql = "SELECT id, username, email FROM admin_users"; // Make sure to adjust this based on your actual schema

// Fetch result
$result = $conn->query($sql);

// Loop through results
while ($user = $result->fetch_assoc()) {
    // Access the correct column names, e.g., 'username' instead of 'name'
    $userName = $user['username'];  // Correct column name
    $email = $user['email'];
    
    // Use these variables as needed in your code
    //echo "User: $userName, Email: $email <br>";
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f6fa;
            margin: 0;
           
        }
        .content-area {
            max-width: 1200px;
            margin: auto;
            margin-left: 260px;
        }
        .page-title {
            display: flex;
            align-items: center;
            font-size: 24px;
            margin-bottom: 20px;
            gap: 10px;
        }
        .filter-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
           
        }
        .filter-header {
            display: flex;
            align-items: center;
            font-weight: bold;
            margin-bottom: 15px;
            gap: 8px;
        }
        .filter-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-control {
            width: 100%;
            padding: 8px 10px;
            font-size: 16px;
            margin-top: 5px;
        }
        .filter-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-outline {
            background: #f0f0f0;
        }
        .btn-primary {
            background: #3f51b5;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f7f7f7;
        }



        /* Add your CSS styles here */
.data-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
          margin-left: 260px;
       margin-top: 20px;
            
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-title {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline {
            border: 1px solid #ddd;
            background: white;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
            border: none;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.875rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-completed {
            background: #e6f7ee;
            color: #00a854;
        }
        
        .status-processing {
            background: #fff7e6;
            color: #fa8c16;
        }
        
        .status-rejected {
            background: #fff1f0;
            color: #f5222d;
        }
        
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        
        .pagination-controls {
            display: flex;
            gap: 8px;
        }
        
        .page-item {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .page-item.active {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        
        .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="content-area">
    <div class="page-title">
        <span class="material-icons-round">people</span>
        <span>All Users</span>
    </div>

    <!-- Filter Form -->
    <form method="GET">
        <div class="filter-card">
            <div class="filter-header">
                <span class="material-icons-round">filter_alt</span>
                <span>Filter Users</span>
            </div>
            <div class="filter-body">
                <div class="filter-row">
                    <div class="form-group">
                        <label for="userStatusFilter">Status</label>
                        <select id="userStatusFilter" name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="verified" <?= ($_GET['status'] ?? '') == 'verified' ? 'selected' : '' ?>>Verified</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending KYC</option>
                            <option value="rejected" <?= ($_GET['status'] ?? '') == 'rejected' ? 'selected' : '' ?>>KYC Rejected</option>
                            <option value="suspended" <?= ($_GET['status'] ?? '') == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="registrationDate">Registration Date</label>
                        <select id="registrationDate" name="registration_date" class="form-control">
                            <option value="">All Time</option>
                            <option value="today" <?= ($_GET['registration_date'] ?? '') == 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= ($_GET['registration_date'] ?? '') == 'week' ? 'selected' : '' ?>>This Week</option>
                            <option value="month" <?= ($_GET['registration_date'] ?? '') == 'month' ? 'selected' : '' ?>>This Month</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userTypeFilter">User Type</label>
                        <select id="userTypeFilter" name="user_type" class="form-control">
                            <option value="">All Types</option>
                            <option value="investor" <?= ($_GET['user_type'] ?? '') == 'investor' ? 'selected' : '' ?>>Investor</option>
                            <option value="trader" <?= ($_GET['user_type'] ?? '') == 'trader' ? 'selected' : '' ?>>Trader</option>
                            <option value="agent" <?= ($_GET['user_type'] ?? '') == 'agent' ? 'selected' : '' ?>>Agent</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <a href="all_users.php" class="btn btn-outline">
                        <span class="material-icons-round">refresh</span>
                        Reset
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons-round">filter_alt</span>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </form>


    <!-- Applied Filters Summary -->
<?php
$activeFilters = [];

if (!empty($_GET['status'])) {
    $activeFilters[] = "Status: " . ucfirst($_GET['status']);
}
if (!empty($_GET['registration_date'])) {
    $labels = ['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month'];
    $activeFilters[] = "Registration Date: " . ($labels[$_GET['registration_date']] ?? $_GET['registration_date']);
}
if (!empty($_GET['user_type'])) {
    $activeFilters[] = "User Type: " . ucfirst($_GET['user_type']);
}

if (count($activeFilters) > 0): ?>
    <div style="margin-bottom: 20px; font-size: 16px; color: #333;">
        <strong>Filters Applied:</strong> <?= implode(' | ', $activeFilters); ?>
    </div>
<?php endif; ?>


    <!-- Users Table -->
 
</div>

<!--Users Found--->
 <div class="data-table-container">
                <div class="table-header">
                    <div class="table-title">Users Found</div>
                    <div class="table-actions">
                        <button class="btn btn-outline" id="filterUsersBtn">
                            <span class="material-icons-round">filter_list</span>
                            Filter
                        </button>
                        <button class="btn btn-outline" id="exportUsersBtn">
                            <span class="material-icons-round">download</span>
                            Export
                        </button>
                        <!--<button class="btn btn-primary" onclick="openModal('addUserModal')">
                            <span class="material-icons-round">add</span>
                            Add User
                        </button>-->
                    </div>
                </div>
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>   
                    </thead>
                    <tbody>
                    <?php
// Database connection (update DB name if needed)
$mysqli = new mysqli("localhost", "root", "", "dollario_admin");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "SELECT * FROM admin_users ORDER BY created_at DESC LIMIT 5"; // adjust LIMIT as needed
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userId = "#USR" . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
        $name = $row['username'] ?? 'N/A';
        $phone = $row['phone'] ?? 'N/A';
        $joined = isset($row['created_at']) ? date('d M, h:i A', strtotime($row['created_at'])) : 'N/A';
        $status = $row['status'] ?? 'Unknown';
        $img = !empty($row['avatar']) ? $row['avatar'] : "../images/default-user.jpg"; // fallback if image not available

        // status badge class logic
        $statusClass = "";
        if (strtolower($status) == "verified") $statusClass = "status-approved";
        elseif (strtolower($status) == "pending kyc") $statusClass = "status-pending";
        elseif (strtolower($status) == "kyc rejected") $statusClass = "status-rejected";

        echo "<tr data-user-id='{$userId}'>
            <td>{$userId}</td>
            <td>
                <div style='display: flex; align-items: center; gap: 10px;'>
                    <img src='{$img}' alt='User' style='width: 30px; height: 30px; border-radius: 50%;'>
                    <span>{$name}</span>
                </div>
            </td>
            <td>{$phone}</td>
            <td>{$joined}</td>
            <td><span class='status-badge {$statusClass}'>{$status}</span></td>
            <td>
                <button class='btn btn-sm btn-outline' data-tooltip='View Details' onclick=\"viewUserDetails('{$userId}')\">
                    <span class='material-icons-round'>visibility</span>
                </button>
                <button class='btn btn-sm btn-outline' data-tooltip='Edit User' onclick=\"editUser('{$userId}')\">
                    <span class='material-icons-round'>edit</span>
                </button>
                <button class='btn btn-sm btn-outline' data-tooltip='Send Message' onclick=\"messageUser('{$userId}')\">
                    <span class='material-icons-round'>mail</span>
                </button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No users found.</td></tr>";
}
?>


</tbody>
                </table>
                <div class="pagination">
                    <div class="pagination-info">Showing 1 to 5 of 42 entries</div>
                    <div class="pagination-controls">
                        <div class="page-item disabled">
                            <span class="material-icons-round">chevron_left</span>
                        </div>
                        <div class="page-item active">1</div>
                        <div class="page-item">2</div>
                        <div class="page-item">3</div>
                        <div class="page-item">4</div>
                        <div class="page-item">5</div>
                        <div class="page-item">
                            <span class="material-icons-round">chevron_right</span>
                        </div>
                    </div>
                </div>
            </div>

            <script>
document.getElementById('exportUsersBtn').addEventListener('click', function () {
    const table = document.getElementById('usersTable');
    
    html2canvas(table).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');

        const pageWidth = pdf.internal.pageSize.getWidth();
        const imgWidth = pageWidth - 20; // 10mm margin on each side
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
        pdf.save("users-list.pdf");
    });
});
</script>



</body>
</html>

<?php $conn->close(); ?>
