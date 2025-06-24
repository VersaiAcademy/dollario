<?php include('../sidebar.php'); ?>
<?php include('submit_help.php'); ?>
<?php
// notification.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB connection
$host = '46.202.161.91';
$dbname = 'u973762102_admin';
$username = 'u973762102_dollario';
$password = '876543Kamlesh';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user settings
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Initialize default settings if none exist
$default_settings = [
    'deposit_alert' => 0,
    'withdrawal_alert' => 0,
    'login_alert' => 0,
    'transaction_email' => 0,
    'marketing_email' => 0,
    'sms_alerts' => 0,
    'push_notifications' => 1,
    'email_sms_toggle' => 0
];

// Check if settings exist for this user
$query = "SELECT * FROM user_notifications WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_settings = $result->fetch_assoc() ?? $default_settings;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deposit_alert = isset($_POST['deposit_alert']) ? 1 : 0;
    $withdrawal_alert = isset($_POST['withdrawal_alert']) ? 1 : 0;
    $login_alert = isset($_POST['login_alert']) ? 1 : 0;
    $transaction_email = isset($_POST['transaction_email']) ? 1 : 0;
    $marketing_email = isset($_POST['marketing_email']) ? 1 : 0;
    $sms_alerts = isset($_POST['sms_alerts']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    $email_sms_toggle = isset($_POST['email_sms_toggle']) ? 1 : 0;

    // Check if record exists
    $check_query = "SELECT id FROM user_notifications WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $exists = $check_stmt->get_result()->num_rows > 0;

    if ($exists) {
        // Update existing record
        $update_query = "UPDATE user_notifications SET 
                        deposit_alert = ?, 
                        withdrawal_alert = ?,
                        login_alert = ?, 
                        transaction_email = ?, 
                        marketing_email = ?,
                        sms_alerts = ?,
                        push_notifications = ?,
                        email_sms_toggle = ? 
                        WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iiiiiiiii", 
            $deposit_alert, 
            $withdrawal_alert,
            $login_alert, 
            $transaction_email, 
            $marketing_email,
            $sms_alerts,
            $push_notifications,
            $email_sms_toggle, 
            $user_id);
    } else {
        // Insert new record
        $update_query = "INSERT INTO user_notifications 
                        (user_id, deposit_alert, withdrawal_alert, login_alert, transaction_email, marketing_email, sms_alerts, push_notifications, email_sms_toggle) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iiiiiiiii", 
            $user_id, 
            $deposit_alert,
            $withdrawal_alert,
            $login_alert, 
            $transaction_email, 
            $marketing_email,
            $sms_alerts,
            $push_notifications,
            $email_sms_toggle);
    }

    if ($update_stmt->execute()) {
        $success_message = "Your notification settings have been updated successfully!";
        // Refresh settings
        $stmt->execute();
        $result = $stmt->get_result();
        $user_settings = $result->fetch_assoc() ?? $default_settings;
    } else {
        $error_message = "There was an error updating your settings. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Settings | Dollario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --border-radius: 0.375rem;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 4px 8px -1px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            
            box-shadow: var(--shadow);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--primary);
        }

        .nav-links a i {
            font-size: 1rem;
        }

        /* Main Content */
        .main-content {
            padding: 2rem 0;
            margin-left: 260px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            color: var(--dark);
            font-weight: 600;
        }

        /* Notification Card */
        .notification-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-title i {
            color: var(--primary);
        }

        .notification-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .notification-label {
            font-weight: 500;
            color: var(--dark);
        }

        .notification-description {
            font-size: 0.875rem;
            color: var(--gray);
        }

        /* Toggle Switch */
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
            border-radius: 34px;
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
            background-color: var(--primary);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: #0e7490;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: rgba(247, 37, 133, 0.1);
            color: #be185d;
            border-left: 4px solid var(--danger);
        }

        /* Footer */
        footer {
            background-color: var(--white);
            padding: 1.5rem 0;
            margin-top: 2rem;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-links a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .copyright {
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-container, .footer-content {
                flex-direction: column;
                gap: 1rem;

            }
            .main-content{
                margin-left: 0%;
            }
    
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .notification-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
             .sidebar {
    display: none;
  }
        }
        
    </style>
</head>
<body>
    
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="dashboard.php"></a>
            <nav class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="notification.php" class="active"><i class="fas fa-bell"></i> Notifications</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content"> 
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Notification Settings</h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- In-App Notifications Card -->
                <div class="notification-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-mobile-alt"></i> In-App Notifications</h2>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">Deposit Alerts</span>
                            <span class="notification-description">Receive push notifications for deposit confirmations</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="deposit_alert" <?php echo $user_settings['deposit_alert'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">Withdrawal Alerts</span>
                            <span class="notification-description">Receive push notifications for withdrawal confirmations</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="withdrawal_alert" <?php echo $user_settings['withdrawal_alert'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">Login Alerts</span>
                            <span class="notification-description">Get notified about unrecognized devices/IPs</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="login_alert" <?php echo $user_settings['login_alert'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">Push Notifications</span>
                            <span class="notification-description">Enable all push notifications</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="push_notifications" <?php echo $user_settings['push_notifications'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Email Notifications Card -->
                <div class="notification-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-envelope"></i> Email Notifications</h2>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">Transaction Emails</span>
                            <span class="notification-description">Customizable templates for transactional emails</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="transaction_email" <?php echo $user_settings['transaction_email'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">Marketing Emails</span>
                            <span class="notification-description">Receive promotional offers and updates</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="marketing_email" <?php echo $user_settings['marketing_email'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- SMS Notifications Card -->
                <div class="notification-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-sms"></i> SMS Notifications</h2>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">SMS Alerts</span>
                            <span class="notification-description">Receive important alerts via SMS</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="sms_alerts" <?php echo $user_settings['sms_alerts'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <span class="notification-label">Toggle Email/SMS</span>
                            <span class="notification-description">Enable/disable all email and SMS alerts</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="email_sms_toggle" <?php echo $user_settings['email_sms_toggle'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </main>

  
</body>
</html>