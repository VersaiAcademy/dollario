<?php
include '../templates/sidebar.php';
include '../templates/header.php';
// Database config
$pdo = new PDO("mysql:host=localhost;dbname=dollario_admin", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch all users excluding the balance column
$stmt = $pdo->prepare("SELECT id, username, email, role, status FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status change
if (isset($_GET['change_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $newStatus = $_GET['change_status'] == 'active' ? 'inactive' : 'active'; // Toggle the status
    $updateStmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
    $updateStmt->execute(['status' => $newStatus, 'id' => $id]);
    header("Location: sub_admins.php"); // Redirect to the same page to refresh the table
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub Admins List</title>
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
    .container{
        margin-left: 260px;
       padding: 20px;
    }
</style>
</head>
<body>
    <div class="container mt-5">
        <h2>Sub Admins List</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= htmlspecialchars($user['status']) ?></td>
                        <td>
                            <!-- Active/Inactive buttons -->
                            <?php if ($user['status'] == 'active'): ?>
                                <a href="?change_status=inactive&id=<?= $user['id'] ?>" class="btn btn-danger btn-sm">Deactivate</a>
                            <?php else: ?>
                                <a href="?change_status=active&id=<?= $user['id'] ?>" class="btn btn-success btn-sm">Activate</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
