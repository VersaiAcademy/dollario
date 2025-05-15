<?php include '../templates/sidebar.php'; ?>
<?php include '../templates/header.php'; ?>
<?php
// Database connection (example using PDO)
$servername = "localhost"; // or $host = "localhost";
$username = "root";
$password = "";
$dbname = "dollario_admin";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password); // Use $servername here
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Function to get transactions from database
function getTransactions($pdo, $page = 1, $perPage = 5) {
    $offset = ($page - 1) * $perPage;
    
    $stmt = $pdo->prepare("SELECT * FROM transactions ORDER BY created_at DESC LIMIT :offset, :perPage"); // Changed to created_at
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to count total transactions
function countTransactions($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM transactions");
    return $stmt->fetchColumn();
}
// Count total users
$countUsersSql = "SELECT COUNT(DISTINCT lh.user_id) as total_users 
                  FROM login_history lh 
                  JOIN admin_users au ON lh.user_id = au.id";

$stmt = $pdo->query($countUsersSql);
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];


// Get current page from query string or default to 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$transactions = getTransactions($pdo, $currentPage);
$totalTransactions = countTransactions($pdo);
$totalPages = ceil($totalTransactions / 5);

// Count Pending KYC
$kycQuery = "SELECT COUNT(*) as pending_kyc FROM kyc_documents WHERE status = 'pending'";

$stmt = $pdo->query($kycQuery);
$pendingKycCount = $stmt->fetch(PDO::FETCH_ASSOC)['pending_kyc'];
// Get Active Investment Count
$investmentQuery = "SELECT COUNT(*) as active_investments FROM investments WHERE status = 'active'";
$stmt = $pdo->query($investmentQuery);
$activeInvestmentCount = $stmt->fetch(PDO::FETCH_ASSOC)['active_investments'];



?>



<head>
    <!-- Include jsPDF from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

</head>
<style>
    .card {
        margin-bottom: 20px;
        border-radius: 8px;
    }
    .card-body {
        padding: 20px;
    }
    .card-title {
        font-size: 1.25rem;
    }
    .card-text {
        font-size: 1.5rem;
        font-weight: 700;
        color:#333333;
    }
    .row {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .col-md-3 {
        margin-bottom: 15px;
    }
   
    .card-text1.text-success {
    color: #3E534A;
    font-size: 14px;
    
    margin-top: 10px;
}
.text-success {
    color: inherit !important;
}

/* Add your CSS styles here */
.data-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
          
            
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
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 600px;
            max-width: 90%;
        }
        
        .modal-header {
            padding: 16px 24px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: bold;
        }
        
        .modal-close {
            cursor: pointer;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-row {
            display: flex;
            gap: 16px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
        }


</style>


<div style="margin-left:260px; padding:15px;">
<h2><i class="fas fa-chart-line"></i> Dashboard Overview</h2>
 <!-- Row for Dashboard Overview -->
 <!-- Row for Dashboard Overview -->
 <div class="row">
        <!-- Total Users -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                <h5 class="card-title" style="color: #7f8c8d;"><i class="fas fa-users"></i> Total Users</h5>

                    <p class="card-text display-4"><?php echo $totalUsers; ?></p>
                    <p class="card-text1 text-success"><i class="fas fa-trending-up"></i> 12.5% from last month</p>
                </div>
            </div>
        </div>

        <!-- Active Investment -->
       <div class="col-md-3">
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h5 class="card-title" style="color: #7f8c8d;"><i class="fas fa-wallet"></i> Active Investment</h5>
            <p class="card-text display-4"><?= $activeInvestmentCount ?></p>
            <p class="card-text1 text-success"><i class="fas fa-trending-up"></i>
            8.3% from last week</p>
        </div>
    </div>
</div>

<!-- Pending KYC Card -->
<div class="col-md-3">
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h5 class="card-title" style="color: #7f8c8d;">
                <i class="fas fa-id-card-alt"></i> Pending KYC
            </h5>
            <p class="card-text display-4"><?= $pendingKycCount ?></p>
            <p class="card-text1 text-success">
                <i class="fas fa-trending-up"></i> Live data
            </p>
        </div>
    </div>
</div>



        <!-- USDT/INR Rate -->
        <div class="col-md-3">
            <div class="card shadow-sm  mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title" style="color: #7f8c8d;"><i class="fas fa-dollar-sign"></i> USDT/INR Rate</h5>
                    <p class="card-text display-4">80.50</p>
                    <p class="card-text1 text-success"><i class="fas fa-trending-up"></i> 0.5% from last hour</p>
                </div>
            </div>
        </div>
    </div>
    <!---recent user--->
    <div class="data-table-container">
                <div class="table-header">
                    <div class="table-title">Recent Users</div>
                    <div class="table-actions">
                        <button class="btn btn-outline" id="filterUsersBtn">
                            <span class="material-icons-round">filter_list</span>
                            Filter
                        </button>
                        <button class="btn btn-outline" id="exportUsersBtn">
    <span class="material-icons-round">download</span>
    Export
</button>


                        <button class="btn btn-primary" onclick="openModal('addUserModal')">
                            <span class="material-icons-round">add</span>
                            Add User
                        </button>
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

<!---transaction--->
<div class="data-table-container">
        <div class="table-header">
            <div class="table-title">Recent Transactions</div>
            <div class="table-actions">
                <button class="btn btn-outline" id="filterTransactionsBtn">
                    <span class="material-icons-round">filter_list</span>
                    Filter
                </button>
                <button class="btn btn-outline" id="exportTransactionsBtn">
                    <span class="material-icons-round">download</span>
                    Export
                </button>
            </div>
        </div>
        <table id="transactionsTable">
            <thead>
                <tr>
                    <th>TX ID</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                <tr data-tx-id="<?php echo htmlspecialchars($transaction['id']); ?>">
                    <td>#<?php echo htmlspecialchars($transaction['id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($transaction['status']); ?>">
                            <?php echo htmlspecialchars($transaction['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M, h:i A', strtotime($transaction['date'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline" data-tooltip="View Details"
                            onclick="viewTransaction('<?php echo $transaction['id']; ?>')">
                            <span class="material-icons-round">visibility</span>
                        </button>
                        <button class="btn btn-sm btn-outline" data-tooltip="Receipt"
                            onclick="downloadReceipt('<?php echo $transaction['id']; ?>')">
                            <span class="material-icons-round">receipt</span>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <div class="pagination-info">
                Showing <?php echo (($currentPage - 1) * 5) + 1; ?> to <?php echo min($currentPage * 5, $totalTransactions); ?> of <?php echo $totalTransactions; ?> entries
            </div>
            <div class="pagination-controls">
                <?php if ($currentPage > 1): ?>
                    <div class="page-item" onclick="window.location.href='?page=<?php echo $currentPage - 1; ?>'">
                        <span class="material-icons-round">chevron_left</span>
                    </div>
                <?php else: ?>
                    <div class="page-item disabled">
                        <span class="material-icons-round">chevron_left</span>
                    </div>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <div class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>" 
                         onclick="window.location.href='?page=<?php echo $i; ?>'">
                        <?php echo $i; ?>
                    </div>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <div class="page-item" onclick="window.location.href='?page=<?php echo $currentPage + 1; ?>'">
                        <span class="material-icons-round">chevron_right</span>
                    </div>
                <?php else: ?>
                    <div class="page-item disabled">
                        <span class="material-icons-round">chevron_right</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
     
     <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
     
    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add New User</div>
                <div class="modal-close" onclick="closeModal('addUserModal')">
                    <span class="material-icons-round">close</span>
                </div>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="POST" action="add_user.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="userType">User Type</label>
                        <select id="userType" name="userType" class="form-control" required>
                            <option value="">Select User Type</option>
                            <option value="investor">Investor</option>
                            <option value="trader">Trader</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="initialBalance">Initial Balance (₹)</label>
                        <input type="number" id="initialBalance" name="initialBalance" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label for="userStatus">Account Status</label>
                        <select id="userStatus" name="userStatus" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('addUserModal')">Cancel</button>
                <button class="btn btn-primary" onclick="document.getElementById('addUserForm').submit()">Add User</button>
            </div>
        </div>
    </div>

    <script>
        // JavaScript functions
        function viewTransaction(txId) {
            alert('Viewing transaction: ' + txId);
            // In a real application, you would redirect to a details page or show a modal
            // window.location.href = 'transaction_details.php?id=' + txId;
        }
        
        function downloadReceipt(txId) {
            alert('Downloading receipt for transaction: ' + txId);
            // In a real application, this would download a PDF receipt
            // window.location.href = 'generate_receipt.php?id=' + txId;
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        
        // Filter button functionality
        document.getElementById('filterTransactionsBtn').addEventListener('click', function() {
            alert('Filter functionality would go here');
            // In a real application, this would open a filter modal/sidebar
        });
        
        // Export button functionality
        document.getElementById('exportTransactionsBtn').addEventListener('click', function() {
            alert('Export functionality would go here');
            // In a real application, this would export data to CSV/Excel
            // window.location.href = 'export_transactions.php';
        });
    </script>
   <script>
    document.getElementById('exportUsersBtn').addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Fetch table data
        const table = document.getElementById('usersTable');
        let tableContent = [];

        // Loop through the table rows to get the content
        for (let i = 1; i < table.rows.length; i++) {
            const row = table.rows[i];
            const rowData = [];

            // Get cell data for each row
            for (let j = 0; j < row.cells.length - 1; j++) {  // Excluding the last cell (Actions column)
                rowData.push(row.cells[j].innerText);
            }

            tableContent.push(rowData);
        }

        // Add the table headers
        const headers = ["User ID", "Name", "Phone", "Joined", "Status"];
        doc.autoTable({
            head: [headers],
            body: tableContent,
            startY: 20,  // Adjust the starting Y position if needed
            theme: 'grid', // Use grid style for the table
        });

        // Save the generated PDF
        doc.save('users_report.pdf');
    });
</script>

<?php include '../templates/footer.php'; ?>

