<?php include '../templates/sidebar.php'; include '../templates/header.php';  ?>
<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "dollario_admin");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['delete_id'])) {
        // Delete sub-admin
        $stmt = $conn->prepare("DELETE FROM sub_admins WHERE id = ?");
        $stmt->bind_param("i", $_POST['delete_id']);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Delete failed']);
        }
        exit;
    }
    
    if (isset($_POST['toggle_status'])) {
        // Toggle status
        $stmt = $conn->prepare("UPDATE sub_admins SET status = IF(status='active','inactive','active') WHERE id = ?");
        $stmt->bind_param("i", $_POST['toggle_status']);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Status update failed']);
        }
        exit;
    }
    
    if (isset($_POST['add_subadmin'])) {
        // Add new sub-admin
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $role = $_POST['adminRole'];
        $status = $_POST['adminStatus'];
        
        $stmt = $conn->prepare("INSERT INTO sub_admins (first_name, last_name, email, phone, role, status, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $firstName, $lastName, $email, $phone, $role, $status);
        $success = $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        
        if ($success) {
            // Get the newly added sub-admin
            $result = $conn->query("SELECT * FROM sub_admins WHERE id = $newId");
            $newSubadmin = $result->fetch_assoc();
            echo json_encode(['success' => true, 'subadmin' => $newSubadmin]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Add failed']);
        }
        exit;
    }
}

// Fetch counts for summary cards
$total = $conn->query("SELECT COUNT(*) AS count FROM sub_admins")->fetch_assoc()['count'];
$active = $conn->query("SELECT COUNT(*) AS count FROM sub_admins WHERE status='active'")->fetch_assoc()['count'];
$inactive = $conn->query("SELECT COUNT(*) AS count FROM sub_admins WHERE status='inactive'")->fetch_assoc()['count'];
$last30 = $conn->query("SELECT COUNT(*) AS count FROM sub_admins WHERE created_at >= NOW() - INTERVAL 30 DAY")->fetch_assoc()['count'];

// Fetch all sub-admins for the table
$subadmins = [];
$result = $conn->query("SELECT * FROM sub_admins ORDER BY created_at DESC");
while($row = $result->fetch_assoc()) {
    $subadmins[] = $row;
}




// Only allow if sub-admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized access");
}

if (isset($_GET['verify_user_id'])) {
    $user_id = $_GET['verify_user_id'];

    // Activate user
    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    if ($stmt->execute([$user_id])) {

        // Log action
        $admin_id = $_SESSION['admin_id'];
        $action = "Verified user account";

        $log = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target_user_id) VALUES (?, ?, ?)");
        $log->execute([$admin_id, $action, $user_id]);

        header("Location: sub_admins.php?success=1");
        exit;
    } else {
        echo "Failed to verify user.";
    }
}

// Handle user approval
if (isset($_GET['approve_user'])) {
    $user_id = $_GET['approve_user'];
    
    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        // Log this action
        $admin_id = $_SESSION['admin_id'];
        $action = "Approved user account";
        $log = $pdo->prepare("INSERT INTO admin_actions (admin_id, action, target_user_id) VALUES (?, ?, ?)");
        $log->execute([$admin_id, $action, $user_id]);
        
        header("Location: sub_admins.php?success=user_approved");
        exit;
    } else {
        $error = "Failed to approve user.";
    }
}

// Fetch pending users for admin to review
$pendingUsers = [];
$result = $pdo->query("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $pendingUsers[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub-Admins | Dollario Admin</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .content-area {
            margin-left: 260px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .page-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .page-title span:first-child {
            margin-right: 10px;
            color: #4e73df;
        }
        .page-title span:last-child {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
        }
        .card-body {
            padding: 20px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        .btn-primary {
            background-color: #4e73df;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3b5ab7;
        }
        .btn-outline {
            background-color: transparent;
            border-color: #d1d5db;
            color: #4b5563;
        }
        .btn-outline:hover {
            background-color: #f3f4f6;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-danger {
            background-color: #e74a3b;
            color: white;
        }
        .btn-danger:hover {
            background-color: #be2617;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
         
            
            border-bottom: 1px solid #eee;
        }
        th {
            font-weight: 600;
            color: #4b5563;
            background-color: #f9fafb;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-active {
            background-color: #e6f7ee;
            color: #10b981;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #ef4444;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #4b5563;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-control:focus {
            outline: none;
            border-color: #4e73df;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }
        .modal-close {
            cursor: pointer;
            color: #6b7280;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .content-area {
                margin-left: 0;
                padding: 15px;
            }
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Content Area -->
    <div class="content-area">
        <div class="page-title">
            <span class="material-icons-round">admin_panel_settings</span>
            <span>Sub-Admins</span>
        </div>

        <!-- Alert Messages -->
        <div id="successAlert" class="alert alert-success"></div>
        <div id="errorAlert" class="alert alert-danger"></div>

        <!-- Sub-Admins Summary -->
        <div class="card">
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background: #f0f7ff; padding: 15px; border-radius: 8px;">
                        <div style="font-size: 14px; color: #4b5563; margin-bottom: 5px;">Total Sub-Admins</div>
                        <div id="totalCount" style="font-size: 24px; font-weight: 600; color: #2d3748;"><?php echo $total; ?></div>
                    </div>
                    <div style="background: #f0f7ff; padding: 15px; border-radius: 8px;">
                        <div style="font-size: 14px; color: #4b5563; margin-bottom: 5px;">Active</div>
                        <div id="activeCount" style="font-size: 24px; font-weight: 600; color: #10b981;"><?php echo $active; ?></div>
                    </div>
                    <div style="background: #f0f7ff; padding: 15px; border-radius: 8px;">
                        <div style="font-size: 14px; color: #4b5563; margin-bottom: 5px;">Inactive</div>
                        <div id="inactiveCount" style="font-size: 24px; font-weight: 600; color: #ef4444;"><?php echo $inactive; ?></div>
                    </div>
                    <div style="background: #f0f7ff; padding: 15px; border-radius: 8px;">
                        <div style="font-size: 14px; color: #4b5563; margin-bottom: 5px;">Last 30 Days</div>
                        <div id="last30Count" style="font-size: 24px; font-weight: 600; color: #2d3748;"><?php echo $last30; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sub-Admins Table -->
        <div class="card">
    <div class="card-header">
        <div class="card-title">Pending User Approvals</div>
    </div>
    <div class="card-body">
        <?php if (empty($pendingUsers)): ?>
            <p>No pending user approvals</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Signup Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingUsers as $user): ?>
                    <tr>
                        <td>USR<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-success" 
                                    onclick="window.location.href='?approve_user=<?= $user['id'] ?>'">
                                Approve
                            </button>
                            <button class="btn btn-danger">
                                Reject
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
    </div>

    <!-- Add Sub-Admin Modal -->
    <div class="modal" id="addSubadminModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add New Sub-Admin</div>
                <div class="modal-close" onclick="closeModal('addSubadminModal')">
                    <span class="material-icons-round">close</span>
                </div>
            </div>
            <form id="addSubadminForm">
                <div class="modal-body">
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
                        <label for="adminRole">Admin Role</label>
                        <select id="adminRole" name="adminRole" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="super">Super Admin</option>
                            <option value="financial">Financial Admin</option>
                            <option value="support">Support Admin</option>
                            <option value="content">Content Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adminStatus">Status</label>
                        <select id="adminStatus" name="adminStatus" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSubadminModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Sub-Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="confirmDeleteModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title">Confirm Delete</div>
                <div class="modal-close" onclick="closeModal('confirmDeleteModal')">
                    <span class="material-icons-round">close</span>
                </div>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this sub-admin? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('confirmDeleteModal')">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <script>
        // Global variable to store the sub-admin ID to delete
        let subadminToDelete = null;

        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Show alert message
        function showAlert(type, message) {
            const alert = document.getElementById(type + 'Alert');
            alert.textContent = message;
            alert.style.display = 'block';
            
            // Hide after 5 seconds
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        // Confirm delete
        function confirmDelete(id) {
            subadminToDelete = id;
            openModal('confirmDeleteModal');
        }

        // Delete sub-admin
        function deleteSubadmin() {
            if (!subadminToDelete) return;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'delete_id=' + subadminToDelete
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from table
                    document.getElementById('row-' + subadminToDelete).remove();
                    
                    // Update counts
                    updateCounts();
                    
                    // Show success message
                    showAlert('success', 'Sub-admin deleted successfully');
                } else {
                    showAlert('error', data.error || 'Failed to delete sub-admin');
                }
                closeModal('confirmDeleteModal');
                subadminToDelete = null;
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while deleting sub-admin');
                closeModal('confirmDeleteModal');
                subadminToDelete = null;
            });
        }

        // Toggle status (active/inactive)
        function toggleStatus(id) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'toggle_status=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to reflect changes (could be optimized with DOM manipulation)
                    location.reload();
                } else {
                    showAlert('error', data.error || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while updating status');
            });
        }

        // Update counts after changes
        function updateCounts() {
            fetch('')
            .then(response => response.text())
            .then(html => {
                // Create a temporary DOM parser to extract the counts
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Update each count
                document.getElementById('totalCount').textContent = 
                    doc.getElementById('totalCount')?.textContent || '0';
                document.getElementById('activeCount').textContent = 
                    doc.getElementById('activeCount')?.textContent || '0';
                document.getElementById('inactiveCount').textContent = 
                    doc.getElementById('inactiveCount')?.textContent || '0';
                document.getElementById('last30Count').textContent = 
                    doc.getElementById('last30Count')?.textContent || '0';
            })
            .catch(error => {
                console.error('Error updating counts:', error);
            });
        }

        // Add new sub-admin
        document.getElementById('addSubadminForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('add_subadmin', '1');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and reset form
                    closeModal('addSubadminModal');
                    this.reset();
                    
                    // Add new row to table
                    const subadmin = data.subadmin;
                    const statusClass = subadmin.status === 'active' ? 'status-active' : 'status-inactive';
                    const statusBtnClass = subadmin.status === 'active' ? 'btn-danger' : 'btn-success';
                    const statusBtnText = subadmin.status === 'active' ? 'Deactivate' : 'Activate';
                    const statusBtnIcon = subadmin.status === 'active' ? 'block' : 'check_circle';
                    const createdDate = new Date(subadmin.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    
                    const newRow = document.createElement('tr');
                    newRow.id = 'row-' + subadmin.id;
                    newRow.innerHTML = `
                        <td>ADM${String(subadmin.id).padStart(4, '0')}</td>
                        <td>${subadmin.first_name} ${subadmin.last_name}</td>
                        <td>${subadmin.email}</td>
                        <td>${subadmin.role}</td>
                        <td>${createdDate}</td>
                        <td>
                            <span class="status-badge ${statusClass}">
                                ${subadmin.status.charAt(0).toUpperCase() + subadmin.status.slice(1)}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline" onclick="viewSubadmin(${subadmin.id})">
                                <span class="material-icons-round">visibility</span> View
                            </button>
                            <button class="btn btn-sm btn-outline" onclick="editSubadmin(${subadmin.id})">
                                <span class="material-icons-round">edit</span> Edit
                            </button>
                            <button class="btn btn-sm ${statusBtnClass}" 
                                    onclick="toggleStatus(${subadmin.id})">
                                <span class="material-icons-round">${statusBtnIcon}</span> ${statusBtnText}
                            </button>
                            <button class="btn btn-sm btn-outline btn-danger" 
                                    onclick="confirmDelete(${subadmin.id})">
                                <span class="material-icons-round">delete</span> Delete
                            </button>
                        </td>
                    `;
                    
                    // Add to beginning of table
                    const tbody = document.querySelector('#subadminsTable tbody');
                    if (tbody.rows.length === 1 && tbody.rows[0].cells[0].colSpan) {
                        // If the table has the "No sub-admins" row, remove it
                        tbody.removeChild(tbody.rows[0]);
                    }
                    tbody.insertBefore(newRow, tbody.firstChild);
                    
                    // Update counts
                    updateCounts();
                    
                    // Show success message
                    showAlert('success', 'Sub-admin added successfully');
                } else {
                    showAlert('error', data.error || 'Failed to add sub-admin');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while adding sub-admin');
            });
        });

        // Set up delete confirmation button
        document.getElementById('confirmDeleteBtn').addEventListener('click', deleteSubadmin);

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Placeholder functions for view/edit
        function viewSubadmin(id) {
            alert('View sub-admin ' + id);
        }

        function editSubadmin(id) {
            alert('Edit sub-admin ' + id);
        }
    </script>
    <script>
    document.querySelector('.btn-outline').addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Title
        doc.setFontSize(16);
        doc.text("Sub-Admins Report", 14, 15);

        // AutoTable
        doc.autoTable({
            startY: 25,
            head: [['Admin ID', 'Name', 'Email', 'Role', 'Created At', 'Status']],
            body: Array.from(document.querySelectorAll('#subadminsTable tbody tr')).map(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 6) {
                    return [
                        cells[0].innerText.trim(),
                        cells[1].innerText.trim(),
                        cells[2].innerText.trim(),
                        cells[3].innerText.trim(),
                        cells[4].innerText.trim(),
                        cells[5].innerText.trim(),
                    ];
                }
            }).filter(row => row !== undefined)
        });

        // Save the PDF
        doc.save('subadmins_report.pdf');
    });
</script>

</body>
</html>