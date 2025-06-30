<?php include '../templates/sidebar.php'; ?>
<?php include '../templates/header.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
 
}

// ✅ Database credentials
$host = 'localhost'; // Localhost for XAMPP
$username = 'root';
$password = ''; // Blank for XAMPP by default
$database = 'u973762102_admin'; // Ensure this DB is created locally

// ✅ Connect to database
$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}

// ✅ Check if tables exist before query
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'user_help_requests'");
if (mysqli_num_rows($checkTable) == 0) {
    die("❌ Table 'user_help_requests' does not exist. Please create it in your local DB.");
}

// ✅ Run query only if table exists
$sql = "SELECT hr.*, u.username, u.email 
        FROM user_help_requests hr 
        JOIN users u ON hr.user_id = u.id 
        ORDER BY hr.created_at DESC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("❌ Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Help Requests</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f9f9f9;
            margin: 0;
        }

        h2 {
            color: #333;
            text-align: center;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            padding: 0 10px;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin-left: 260px;
           
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #222;
            color: #fff;
        }

        td small {
            color: #777;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
                margin-left: 0px;
           
            }

            th, td {
                padding: 8px;
            }

            .table-container {
                padding: 0;
            }
            .header{
                margin-left: 0px;
            }
        }
    </style>
</head>
<body>
    <h2>📩 Help Requests</h2>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Info</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['username']) ?></strong><br>
                        <small><?= htmlspecialchars($row['email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
