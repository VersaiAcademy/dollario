<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Dollario/login.php"); // <- THIS is the fix
    exit;
}
// Show content if logged in
echo "Welcome to Security Page!";


// Security settings
$pageTitle = "Security | Dollario Admin";
$activePage = "security";

// Get security settings from database
$settings = [];
$settingsQuery = "SELECT setting_name, setting_value FROM security_settings WHERE is_active = 1";
$settingsResult = $conn->query($settingsQuery);
if ($settingsResult) {
    while ($row = $settingsResult->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
} else {
    // Handle query error
    die("Error fetching security settings: " . $conn->error);
}

// Get blocked IPs
$blockedIps = [];
$ipsQuery = "SELECT ip_address, reason, blocked_at, is_permanent, 
             IF(is_permanent = 1, 'Permanent', 
                IF(block_until > NOW(), DATE_FORMAT(block_until, '%b %d, %Y'), 'Expired')) as block_duration
             FROM blocked_ips 
             WHERE is_active = 1 AND (is_permanent = 1 OR block_until > NOW())
             ORDER BY blocked_at DESC";
$ipsResult = $conn->query($ipsQuery);
if ($ipsResult) {
    while ($row = $ipsResult->fetch_assoc()) {
        $blockedIps[] = $row;
    }
}

// Get security logs
$securityLogs = [];
$logsQuery = "SELECT l.activity_type, l.description, l.ip_address, l.created_at, 
              u.name as user_name
              FROM security_logs l
              LEFT JOIN admin_users u ON l.user_id = u.id
              ORDER BY l.created_at DESC
              LIMIT 10";

$logsResult = $conn->query($logsQuery);
if ($logsResult) {
    while ($row = $logsResult->fetch_assoc()) {
        $securityLogs[] = $row;
    }
}

// Get admin users count
$adminUsersQuery = "SELECT COUNT(*) as total, SUM(2fa_enabled) as with_2fa FROM admin_users WHERE is_active = 1";
$adminUsersResult = $conn->query($adminUsersQuery);
$adminUsersData = $adminUsersResult ? $adminUsersResult->fetch_assoc() : ['total' => 0, 'with_2fa' => 0];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        // Update security settings
        foreach ($_POST['settings'] as $name => $value) {
            $value = $conn->real_escape_string($value);
            $query = "UPDATE security_settings 
                      SET setting_value = '$value', 
                          updated_by = " . $_SESSION['admin_id'] . ",
                          updated_at = NOW()
                      WHERE setting_name = '$name'";
            $conn->query($query);
        }
        $_SESSION['message'] = "Security settings updated successfully!";
        header("Location: security.php");
        exit;
    }
    
    if (isset($_POST['block_ip'])) {
        // Block IP address
        $ip = $conn->real_escape_string($_POST['ip_address']);
        $reason = $conn->real_escape_string($_POST['reason']);
        $customReason = isset($_POST['custom_reason']) ? $conn->real_escape_string($_POST['custom_reason']) : '';
        $duration = $conn->real_escape_string($_POST['block_duration']);
        
        $finalReason = ($reason === 'other') ? $customReason : $reason;
        
        // Calculate block until date
        $blockUntil = null;
        $isPermanent = 0;
        
        switch ($duration) {
            case '1h':
                $blockUntil = date('Y-m-d H:i:s', strtotime('+1 hour'));
                break;
            case '1d':
                $blockUntil = date('Y-m-d H:i:s', strtotime('+1 day'));
                break;
            case '7d':
                $blockUntil = date('Y-m-d H:i:s', strtotime('+7 days'));
                break;
            case '30d':
                $blockUntil = date('Y-m-d H:i:s', strtotime('+30 days'));
                break;
            case 'permanent':
                $isPermanent = 1;
                break;
        }
        
        $query = "INSERT INTO blocked_ips (ip_address, reason, blocked_by, block_until, is_permanent)
                  VALUES ('$ip', '$finalReason', " . $_SESSION['admin_id'] . ", ";
        $query .= ($blockUntil ? "'$blockUntil'" : "NULL") . ", $isPermanent)
                  ON DUPLICATE KEY UPDATE 
                  reason = '$finalReason',
                  blocked_by = " . $_SESSION['admin_id'] . ",
                  block_until = " . ($blockUntil ? "'$blockUntil'" : "NULL") . ",
                  is_permanent = $isPermanent,
                  is_active = 1";
        
        $conn->query($query);
        
        // Log the action
        $logQuery = "INSERT INTO security_logs (user_id, activity_type, description, ip_address)
                     VALUES (" . $_SESSION['admin_id'] . ", 'ip_blocked', 'Blocked IP address $ip', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($logQuery);
        
        $_SESSION['message'] = "IP address blocked successfully!";
        header("Location: security.php");
        exit;
    }
    
    if (isset($_POST['unblock_ip'])) {
        $ip = $conn->real_escape_string($_POST['ip_address']);
        $query = "UPDATE blocked_ips SET is_active = 0, unblocked_by = " . $_SESSION['admin_id'] . ", unblocked_at = NOW() WHERE ip_address = '$ip'";
        $conn->query($query);
        
        // Log the action
        $logQuery = "INSERT INTO security_logs (user_id, activity_type, description, ip_address)
                     VALUES (" . $_SESSION['admin_id'] . ", 'ip_unblocked', 'Unblocked IP address $ip', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($logQuery);
        
        $_SESSION['message'] = "IP address unblocked successfully!";
        header("Location: security.php");
        exit;
    }
    
    if (isset($_POST['run_scan'])) {
        // Simulate security scan
        $scanType = $conn->real_escape_string($_POST['scan_type']);
        
        // Log the scan
        $logQuery = "INSERT INTO security_logs (user_id, activity_type, description, ip_address)
                     VALUES (" . $_SESSION['admin_id'] . ", 'security_scan', 'Ran $scanType security scan', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($logQuery);
        
        $_SESSION['message'] = "Security scan completed successfully! No critical issues found.";
        header("Location: security.php");
        exit;
    }
}

// Now include the header and other templates after all processing is done
include '../templates/header.php';
include '../templates/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cedarville+Cursive&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        /* Security Status Cards */
        .security-status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .security-status-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .security-status-card:hover {
            transform: translateY(-5px);
        }
        
        .security-status-card.success {
            border-left: 4px solid #28a745;
        }
        
        .security-status-card.warning {
            border-left: 4px solid #ffc107;
        }
        
        .security-status-card.danger {
            border-left: 4px solid #dc3545;
        }
        
        .security-status-title {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: #4b5563;
            font-weight: 500;
        }
        
        .security-status-title .material-icons-round {
            margin-right: 10px;
            font-size: 24px;
        }
        
        .security-status-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1f2937;
        }
        
        .security-status-description {
            color: #6b7280;
            font-size: 14px;
        }
        
        /* Security Activity */
        .security-activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .security-activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background-color: #e5e7eb;
            color: #4b5563;
        }
        
        .security-activity-icon.warning {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .security-activity-icon.danger {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .security-activity-content {
            flex: 1;
        }
        
        .security-activity-title {
            font-weight: 500;
            margin-bottom: 5px;
            color: #1f2937;
        }
        
        .security-activity-meta {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #6b7280;
        }
        
        /* Security Settings */
        .security-setting {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .security-setting-info {
            flex: 1;
        }
        
        .security-setting-title {
            font-weight: 500;
            margin-bottom: 5px;
            color: #1f2937;
        }
        
        .security-setting-description {
            font-size: 14px;
            color: #6b7280;
        }
        
        /* Switch styling */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #4e73df;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 18px;
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
        
        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4b5563;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        /* Button styles */
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .btn-primary {
            background-color: #4e73df;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a56b5;
        }
        
        .btn-outline {
            background-color: transparent;
            border-color: #ddd;
            color: #4b5563;
        }
        
        .btn-outline:hover {
            background-color: #f9fafb;
        }
        
        .btn-danger {
            background-color: #e74a3b;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #be2617;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 13px;
        }
        
        /* Alert styles */
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <!-- Content Area -->
    <div class="content-area">
        <div class="page-title">
            <span class="material-icons-round">security</span>
            <span>Security</span>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Security Status Overview -->
        <div class="security-status-cards">
            <div class="security-status-card success">
                <div class="security-status-title">
                    <span class="material-icons-round">lock</span>
                    <span>System Security</span>
                </div>
                <div class="security-status-value">Secure</div>
                <div class="security-status-description">All critical systems are protected</div>
            </div>
            
            <div class="security-status-card <?php echo ($adminUsersData['with_2fa'] < $adminUsersData['total']) ? 'warning' : 'success'; ?>">
                <div class="security-status-title">
                    <span class="material-icons-round">admin_panel_settings</span>
                    <span>Admin Accounts</span>
                </div>
                <div class="security-status-value"><?php echo htmlspecialchars($adminUsersData['total']); ?> Active</div>
                <div class="security-status-description"><?php echo htmlspecialchars($adminUsersData['with_2fa']); ?> with 2FA enabled</div>
            </div>
            
            <div class="security-status-card">
                <div class="security-status-title">
                    <span class="material-icons-round">warning</span>
                    <span>Security Alerts</span>
                </div>
                <div class="security-status-value">2 New</div>
                <div class="security-status-description">Require your attention</div>
            </div>
            
            <div class="security-status-card">
                <div class="security-status-title">
                    <span class="material-icons-round">update</span>
                    <span>Last Security Scan</span>
                </div>
                <div class="security-status-value">Today, 10:30 AM</div>
                <div class="security-status-description">No critical issues found</div>
            </div>
        </div>

        <!-- Recent Security Activity -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Security Activity</div>
                <button class="btn btn-outline" onclick="window.location.reload()">
                    <span class="material-icons-round">refresh</span>
                    Refresh
                </button>
            </div>
            <div class="card-body">
                <?php foreach ($securityLogs as $log): ?>
                    <div class="security-activity-item">
                        <div class="security-activity-icon <?php 
                            echo strpos(strtolower($log['activity_type']), 'failed') !== false ? 'warning' : 
                                 (strpos(strtolower($log['activity_type']), 'suspicious') !== false ? 'danger' : ''); ?>">
                            <span class="material-icons-round">
                                <?php 
                                    switch(strtolower($log['activity_type'])) {
                                        case 'login':
                                            echo 'login';
                                            break;
                                        case 'failed_login':
                                            echo 'warning';
                                            break;
                                        case 'password_change':
                                            echo 'password';
                                            break;
                                        case 'ip_blocked':
                                        case 'suspicious_activity':
                                            echo 'security';
                                            break;
                                        case 'security_scan':
                                            echo 'update';
                                            break;
                                        default:
                                            echo 'info';
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="security-activity-content">
                            <div class="security-activity-title"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $log['activity_type']))); ?></div>
                            <div class="security-activity-meta">
                                <span>
                                    <?php if ($log['ip_address']): ?>
                                        IP: <?php echo htmlspecialchars($log['ip_address']); ?> 
                                    <?php endif; ?>
                                    <?php if ($log['user_name']): ?>
                                        - Admin: <?php echo htmlspecialchars($log['user_name']); ?>
                                    <?php endif; ?>
                                </span>
                                <span><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($log['created_at']))); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="padding: 15px; display: flex; justify-content: center; border-top: 1px solid #eee;">
                <button class="btn btn-outline" onclick="window.location.href='security_logs.php'">
                    <span class="material-icons-round">expand_more</span>
                    View All Activity
                </button>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Security Settings</div>
            </div>
            <form method="POST" action="security.php">
                <div class="card-body">
                    <div class="security-setting">
                        <div class="security-setting-info">
                            <div class="security-setting-title">Two-Factor Authentication</div>
                            <div class="security-setting-description">Require 2FA for all admin accounts</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="settings[2fa_required]" value="1" <?php echo ($settings['2fa_required'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    
                    <div class="security-setting">
                        <div class="security-setting-info">
                            <div class="security-setting-title">IP Whitelisting</div>
                            <div class="security-setting-description">Restrict admin access to specific IP addresses</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="settings[ip_whitelisting]" value="1" <?php echo ($settings['ip_whitelisting'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    
                    <div class="security-setting">
                        <div class="security-setting-info">
                            <div class="security-setting-title">Password Policy</div>
                            <div class="security-setting-description">Require strong passwords (min 12 chars, special characters)</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="settings[strong_passwords]" value="1" <?php echo ($settings['strong_passwords'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    
                    <div class="security-setting">
                        <div class="security-setting-info">
                            <div class="security-setting-title">Login Attempts Limit</div>
                            <div class="security-setting-description">Block after 5 failed login attempts</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="settings[login_attempts_limit]" value="1" <?php echo ($settings['login_attempts_limit'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    
                    <div class="security-setting">
                        <div class="security-setting-info">
                            <div class="security-setting-title">Session Timeout</div>
                            <div class="security-setting-description">Automatically logout after 30 minutes of inactivity</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="settings[session_timeout]" value="1" <?php echo ($settings['session_timeout'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    
                    <div class="security-setting">
                        <div class="security-setting-info">
                            <div class="security-setting-title">Activity Logging</div>
                            <div class="security-setting-description">Record all admin activities for audit</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="settings[activity_logging]" value="1" <?php echo ($settings['activity_logging'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div style="padding: 15px; border-top: 1px solid #eee;">
                    <button type="submit" name="save_settings" class="btn btn-primary">
                        <span class="material-icons-round">save</span>
                        Save Security Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- IP Blocklist -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Blocked IP Addresses</div>
                <button class="btn btn-outline" onclick="openModal('addIpModal')">
                    <span class="material-icons-round">add</span>
                    Add IP
                </button>
            </div>
            <div class="card-body">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #4b5563; background-color: #f9fafb;">IP Address</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #4b5563; background-color: #f9fafb;">Reason</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #4b5563; background-color: #f9fafb;">Blocked On</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #4b5563; background-color: #f9fafb;">Duration</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #4b5563; background-color: #f9fafb;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blockedIps as $ip): ?>
                            <tr>
                                <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                                <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($ip['reason']); ?></td>
                                <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($ip['blocked_at']))); ?></td>
                                <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($ip['block_duration']); ?></td>
                                <td style="padding: 12px 15px; border-bottom: 1px solid #eee;">
                                    <form method="POST" action="security.php" style="display: inline;">
                                        <input type="hidden" name="ip_address" value="<?php echo htmlspecialchars($ip['ip_address']); ?>">
                                        <button type="submit" name="unblock_ip" class="btn btn-sm btn-outline" onclick="return confirm('Are you sure you want to unblock this IP?')">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($blockedIps)): ?>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: #6b7280;">No blocked IP addresses found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Security Scan Button -->
        <div style="margin-top: 30px; text-align: center;">
            <button class="btn btn-primary" onclick="openModal('securityScanModal')">
                <span class="material-icons-round">security</span>
                Run Security Scan
            </button>
        </div>
    </div>

    <!-- Add IP to Blocklist Modal -->
    <div class="modal" id="addIpModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Block IP Address</div>
                <div class="modal-close" onclick="closeModal('addIpModal')">
                    <span class="material-icons-round">close</span>
                </div>
            </div>
            <form id="addIpForm" method="POST" action="security.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="ipAddress">IP Address</label>
                        <input type="text" id="ipAddress" name="ip_address" class="form-control" placeholder="Enter IP address" required>
                    </div>
                    <div class="form-group">
                        <label for="blockReason">Reason</label>
                        <select id="blockReason" name="reason" class="form-control" required>
                            <option value="">Select reason</option>
                            <option value="failed_attempts">Multiple failed login attempts</option>
                            <option value="suspicious_activity">Suspicious activity</option>
                            <option value="malicious">Known malicious IP</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="customReason">Custom Reason (if other selected)</label>
                        <input type="text" id="customReason" name="custom_reason" class="form-control" placeholder="Enter custom reason" disabled>
                    </div>
                    <div class="form-group">
                        <label for="blockDuration">Block Duration</label>
                        <select id="blockDuration" name="block_duration" class="form-control" required>
                            <option value="1h">1 Hour</option>
                            <option value="1d">1 Day</option>
                            <option value="7d">7 Days</option>
                            <option value="30d">30 Days</option>
                            <option value="permanent" selected>Permanent</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addIpModal')">Cancel</button>
                    <button type="submit" name="block_ip" class="btn btn-danger">Block IP</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Scan Modal -->
    <div class="modal" id="securityScanModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Run Security Scan</div>
                <div class="modal-close" onclick="closeModal('securityScanModal')">
                    <span class="material-icons-round">close</span>
                </div>
            </div>
            <form id="securityScanForm" method="POST" action="security.php">
                <div class="modal-body">
                    <div style="text-align: center; padding: 20px;">
                        <span class="material-icons-round" style="font-size: 48px; color: #4e73df;">security</span>
                        <h3 style="margin: 15px 0 10px;">System Security Scan</h3>
                        <p style="color: #6b7280; margin-bottom: 20px;">
                            This will perform a comprehensive scan of your system for security vulnerabilities and configuration issues.
                        </p>
                        
                        <div class="form-group">
                            <label for="scanType">Scan Type</label>
                            <select id="scanType" name="scan_type" class="form-control">
                                <option value="quick">Quick Scan (Recommended)</option>
                                <option value="full">Full System Scan</option>
                                <option value="custom">Custom Scan</option>
                            </select>
                        </div>
                        
                        <div style="margin-top: 25px; background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left;">
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <span class="material-icons-round" style="margin-right: 10px; color: #4e73df;">info</span>
                                <span>Last scan: Today, 10:30 AM - No critical issues found</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="material-icons-round" style="margin-right: 10px; color: #4e73df;">schedule</span>
                                <span>Estimated time: 2-5 minutes</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('securityScanModal')">Cancel</button>
                    <button type="submit" name="run_scan" class="btn btn-primary">
                        <span class="material-icons-round">play_arrow</span>
                        Start Scan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Toggle custom reason field based on selection
        document.getElementById('blockReason').addEventListener('change', function() {
            const customReasonField = document.getElementById('customReason');
            customReasonField.disabled = this.value !== 'other';
            if (this.value !== 'other') {
                customReasonField.value = '';
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Toggle switch styling
        const switches = document.querySelectorAll('.switch input[type="checkbox"]');
        switches.forEach(switchEl => {
            const slider = switchEl.nextElementSibling;
            
            // Initialize slider position based on checkbox state
            if (switchEl.checked) {
                slider.style.backgroundColor = '#4e73df';
            } else {
                slider.style.backgroundColor = '#ccc';
            }
            
            switchEl.addEventListener('change', function() {
                if (this.checked) {
                    slider.style.backgroundColor = '#4e73df';
                } else {
                    slider.style.backgroundColor = '#ccc';
                }
            });
        });
    </script>
</body>
</html>
<?php include '../templates/footer.php'; ?>