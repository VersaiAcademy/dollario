<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin Auth check here
// if (!isset($_SESSION['is_admin'])) { die("Unauthorized"); }

// Corrected DB connection path (goes up one level from /modules/)
require_once __DIR__ . '/../includes/db.php';

// Fetch all help requests
$sql = "SELECT hr.*, u.username, u.email 
        FROM help_requests hr 
        JOIN users u ON hr.user_id = u.id 
        ORDER BY hr.created_at DESC";

$result = mysqli_query($conn, $sql);
?>



<!DOCTYPE html>
<html>
<head>
    <title>Help Requests - Admin</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
    </style>
</head>
<body>
    <h2>📩 Help Requests</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Date</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td>
                    <?= htmlspecialchars($row['username']) ?><br>
                    <small><?= htmlspecialchars($row['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
