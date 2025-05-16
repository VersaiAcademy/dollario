<?php
include "../includes/db.php";
include '../templates/sidebar.php'; 
include '../templates/header.php';  

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action'], $_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'] == "approve" ? "Approved" : "Rejected";
    $approved_at = $action === "Approved" ? ", approved_at = NOW()" : "";

    $update = "UPDATE inr_withdrawals SET status = '$action' $approved_at WHERE id = $id";
    $conn->query($update);
    header("Location: inr_withdrawals.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>INR Withdrawals - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .container {
        margin-left: 260px;
    }
</style>
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">INR Withdrawals</h2>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>User ID</th>
        <th>Amount (INR)</th>
        <th>Method</th>
        <th>Account</th>
        <th>Status</th>
        <th>Requested At</th>
        <th>Approved At</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = "SELECT * FROM inr_withdrawals ORDER BY requested_at DESC";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
          $count = 1;
          while ($row = $result->fetch_assoc()) {
              echo "<tr>
                  <td>{$count}</td>
                  <td>{$row['user_id']}</td>
                  <td>{$row['amount']}</td>
                  <td>{$row['method']}</td>
                  <td>{$row['account_details']}</td>
                  <td>{$row['status']}</td>
                  <td>{$row['requested_at']}</td>
                  <td>{$row['approved_at']}</td>
                  <td>";
              if ($row['status'] == 'Pending') {
                  echo "<form method='POST' style='display:inline-block;'>
                          <input type='hidden' name='id' value='{$row['id']}'>
                          <button name='action' value='approve' class='btn btn-success btn-sm'>Approve</button>
                        </form>
                        <form method='POST' style='display:inline-block;'>
                          <input type='hidden' name='id' value='{$row['id']}'>
                          <button name='action' value='reject' class='btn btn-danger btn-sm'>Reject</button>
                        </form>";
              } else {
                  echo "-";
              }
              echo "</td></tr>";
              $count++;
          }
      } else {
          echo "<tr><td colspan='9' class='text-center'>No withdrawal requests.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>
</body>
</html>
