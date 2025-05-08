<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dollario_admin');

// Connect to database
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_subadmin':
                addSubAdmin($pdo);
                break;
            case 'update_subadmin':
                updateSubAdmin($pdo);
                break;
            case 'toggle_status':
                toggleSubAdminStatus($pdo);
                break;
            case 'reset_password':
                resetSubAdminPassword($pdo);
                break;
        }
    }
}

// Functions
function addSubAdmin($pdo) {
    $required = ['first_name', 'last_name', 'email', 'phone', 'role', 'status'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "All fields are required"]);
            return;
        }
    }
    
    // Generate admin ID
    $admin_id = '#ADM' . str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM sub_admins WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => "Email already exists"]);
        return;
    }
    
    // Default password (should be changed on first login)
    $password_hash = password_hash('Dollario@123', PASSWORD_DEFAULT);
    
    try {
        $pdo->beginTransaction();
        
        // Insert sub-admin
        $stmt = $pdo->prepare("INSERT INTO sub_admins (admin_id, first_name, last_name, email, phone, password_hash, role, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $admin_id,
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $password_hash,
            $_POST['role'],
            $_POST['status']
        ]);
        
        $sub_admin_id = $pdo->lastInsertId();
        
        // Insert permissions
        $permissions = [
            'users_view', 'users_edit', 'users_kyc',
            'deposits_view', 'withdrawals_view', 'withdrawals_approve',
            'content_manage', 'notifications_send',
            'reports_view', 'reports_export'
        ];
        
        foreach ($permissions as $permission) {
            $granted = isset($POST['perm' . $permission]) ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO sub_admin_permissions (sub_admin_id, permission_key, granted) 
                                  VALUES (?, ?, ?)");
            $stmt->execute([$sub_admin_id, $permission, $granted]);
        }
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => "Sub-admin added successfully"]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
    }
}

function updateSubAdmin($pdo) {
    // Similar to addSubAdmin but with UPDATE queries
    // Implementation omitted for brevity
}

function toggleSubAdminStatus($pdo) {
    if (empty($_POST['admin_id'])) {
        echo json_encode(['success' => false, 'message' => "Admin ID required"]);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE sub_admins SET status = IF(status='active','inactive','active') WHERE admin_id = ?");
        $stmt->execute([$_POST['admin_id']]);
        
        echo json_encode(['success' => true, 'message' => "Status updated"]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
    }
}

function resetSubAdminPassword($pdo) {
    if (empty($_POST['admin_id'])) {
        echo json_encode(['success' => false, 'message' => "Admin ID required"]);
        return;
    }
    
    $new_password = 'Dollario@123'; // Should generate a random temp password
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("UPDATE sub_admins SET password_hash = ? WHERE admin_id = ?");
        $stmt->execute([$password_hash, $_POST['admin_id']]);
        
        echo json_encode(['success' => true, 'message' => "Password reset to: $new_password"]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
    }
}

// Get sub-admins for display
function getSubAdmins($pdo, $filters = []) {
    $query = "SELECT * FROM sub_admins WHERE 1=1";
    $params = [];
    
    if (!empty($filters['status'])) {
        $query .= " AND status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['role'])) {
        $query .= " AND role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($filters['search'])) {
        $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR admin_id LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get sub-admin permissions
function getSubAdminPermissions($pdo, $sub_admin_id) {
    $stmt = $pdo->prepare("SELECT permission_key FROM sub_admin_permissions WHERE sub_admin_id = ? AND granted = 1");
    $stmt->execute([$sub_admin_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get counts for dashboard
function getSubAdminCounts($pdo) {
    $counts = [
        'total' => 0,
        'active' => 0,
        'inactive' => 0,
        'recent' => 0
    ];
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM sub_admins");
    $counts['total'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM sub_admins WHERE status = 'active'");
    $counts['active'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM sub_admins WHERE status = 'inactive'");
    $counts['inactive'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM sub_admins WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $counts['recent'] = $stmt->fetchColumn();
    
    return $counts;
}

// Get filtered sub-admins based on request
$filters = [
    'status' => $_GET['status'] ?? '',
    'role' => $_GET['role'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$sub_admins = getSubAdmins($pdo, $filters);
$counts = getSubAdminCounts($pdo);

$pageTitle = "Sub-Admins | Dollario Admin";
$activePage = "Sub-Admins";
?>