<?php
include '../templates/sidebar.php';
include '../templates/header.php';
include '../includes/db.php'; // your DB connection

// Fetch login history with user info, including username
$sql = "SELECT lh.*, au.email, au.username 
        FROM login_history lh 
        JOIN admin_users au ON lh.user_id = au.id 
        ORDER BY lh.login_time DESC";
$result = $conn->query($sql);

// Total login records
$countSql = "SELECT COUNT(*) as total_records FROM login_history lh 
             JOIN admin_users au ON lh.user_id = au.id";
$totalRecords = $conn->query($countSql)->fetch_assoc()['total_records'];

// Total distinct users
$countUsersSql = "SELECT COUNT(DISTINCT lh.user_id) as total_users FROM login_history lh 
                  JOIN admin_users au ON lh.user_id = au.id";
$totalUsers = $conn->query($countUsersSql)->fetch_assoc()['total_users'];
?>

<div style='margin-left:260px; padding:20px;'>
    <h2>Login History</h2>
    <p>Total Records: <?php echo $totalRecords; ?> | Distinct Users: <?php echo $totalUsers; ?></p>
    <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">
        <tr>
            <th>UserName</th>
            <th>Email</th>
            <th>Login Time</th>
            <th>IP Address</th>
            <th>User Agent</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['login_time']); ?></td>
            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
            <td><?php echo htmlspecialchars($row['user_agent']); ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php include '../templates/footer.php'; ?>
