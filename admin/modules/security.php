<?php include '../templates/sidebar.php'; ?>
<?php include '../templates/header.php'; ?>
<?php
// DB config
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'u973762102_admin';

$connection = mysqli_connect($host, $username, $password, $database);
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch settings
$query = "SELECT * FROM security_settings WHERE id = 1";
$result = mysqli_query($connection, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $settings = mysqli_fetch_assoc($result);
} else {
    $settings = [
        'encryption_status' => '',
        'ssl_status' => '',
        'ip_whitelist_status' => '',
        'rate_limiting_status' => ''
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $encryption_status = mysqli_real_escape_string($connection, $_POST['encryption_status']);
    $ssl_status = mysqli_real_escape_string($connection, $_POST['ssl_status']);
    $ip_whitelist_status = mysqli_real_escape_string($connection, $_POST['ip_whitelist_status']);
    $rate_limiting_status = mysqli_real_escape_string($connection, $_POST['rate_limiting_status']);

    $update_query = "UPDATE security_settings SET 
        encryption_status = '$encryption_status',
        ssl_status = '$ssl_status',
        ip_whitelist_status = '$ip_whitelist_status',
        rate_limiting_status = '$rate_limiting_status'
    WHERE id = 1";

    if (mysqli_query($connection, $update_query)) {
        echo "<script>alert('Security settings updated successfully!'); window.location.href='security.php';</script>";
        exit();
    } else {
        echo "Error updating settings: " . mysqli_error($connection);
    }
}
?>


<!-- HTML Form here -->


<!DOCTYPE html>
<html>
<head>
    <title>Security Settings</title>
</head>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .content {
        margin-top: 20px;
       margin-left: 260px;
        padding: 30px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
     
    }
    .content h2 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }

    label {
        display: block;
        margin-bottom: 6px;
        color: #555;
        font-weight: 500;
    }

    select, input[type="submit"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid #ccc;
        box-sizing: border-box;
        font-size: 16px;
    }

    input[type="submit"] {
        background-color: #007bff;
        color: #fff;
        border: none;
        transition: background-color 0.3s ease;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #0056b3;
    }
    @media (max-width: 768px) {
        .header{
            margin-left: 0;
        }
        .content{
            margin-left: 0;
        }
    }
</style>

<body>
    <section class="content">
        <h2>Update Security Settings</h2>
        <form action="security.php" method="POST">
            <label for="encryption_status">Encryption Status:</label>
            <select name="encryption_status" id="encryption_status">
                <option value="AES-256" <?= (isset($settings['encryption_status']) && $settings['encryption_status'] == 'AES-256') ? 'selected' : '' ?>>AES-256</option>
                <option value="None" <?= (isset($settings['encryption_status']) && $settings['encryption_status'] == 'None') ? 'selected' : '' ?>>None</option>
            </select>
            <br><br>

            <label for="ssl_status">SSL/TLS Status:</label>
            <select name="ssl_status" id="ssl_status">
                <option value="Active" <?= (isset($settings['ssl_status']) && $settings['ssl_status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= (isset($settings['ssl_status']) && $settings['ssl_status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
            <br><br>

            <label for="ip_whitelist_status">IP Whitelisting:</label>
            <select name="ip_whitelist_status" id="ip_whitelist_status">
                <option value="Enabled" <?= (isset($settings['ip_whitelist_status']) && $settings['ip_whitelist_status'] == 'Enabled') ? 'selected' : '' ?>>Enabled</option>
                <option value="Disabled" <?= (isset($settings['ip_whitelist_status']) && $settings['ip_whitelist_status'] == 'Disabled') ? 'selected' : '' ?>>Disabled</option>
            </select>
            <br><br>

            <label for="rate_limiting_status">Rate Limiting:</label>
            <select name="rate_limiting_status" id="rate_limiting_status">
                <option value="Active (Limit: 5 attempts/min)" <?= (isset($settings['rate_limiting_status']) && $settings['rate_limiting_status'] == 'Active (Limit: 5 attempts/min)') ? 'selected' : '' ?>>Active (Limit: 5 attempts/min)</option>
                <option value="Inactive" <?= (isset($settings['rate_limiting_status']) && $settings['rate_limiting_status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
            <br><br>

            <input type="submit" value="Update Security Settings">
        </form>
    </section>
</body>
</html>
