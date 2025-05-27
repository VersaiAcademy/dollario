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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin-login_history</title>
    <style>
/* Wrapper */
div[style*="margin-left:260px"] {
  background-color: #ffffff;
  font-family: 'Roboto', sans-serif;
  padding: 30px;
}

/* Heading */
div[style*="margin-left:260px"] h2 {
  font-size: 24px;
  color: #1a1a1a;
  margin-bottom: 10px;
}

div[style*="margin-left:260px"] p {
  font-size: 14px;
  color: #666;
  margin-bottom: 20px;
}

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Table header */
table th {
  background-color: #f2f2f2;
  color: #333;
  padding: 14px 12px;
  text-align: left;
  font-weight: 600;
  font-size: 14px;
  border-bottom: 2px solid #e0e0e0;
}

/* Table body */
table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
  font-size: 14px;
  color: #444;
}

/* Row hover */
table tr:hover td {
  background-color: #fafafa;
}
@media screen and (max-width: 768px) {
  .header{
    margin-left: 0px;
  }
}

 .dash-over{
        margin-left:260px;
     }
       @media(max-width:768px) {
        .dash-over{
            margin-left:0px;
             padding:15px;
        }
      }

      
    </style>
</head>
<body>
    

<div id="content" class="dash-over">
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
</class=>
</body>
</html>

<?php include '../templates/footer.php'; ?>
