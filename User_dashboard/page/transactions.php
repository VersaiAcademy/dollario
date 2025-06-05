<?php include('../sidebar.php'); ?>
<?php
require '../config/db.php'; // or the correct relative path


// Pagination logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$start = ($page - 1) * $records_per_page;

// Fetch total number of records
$countQuery = "SELECT COUNT(*) FROM transactions";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute();
$totalTransactions = $countStmt->fetchColumn();

// Fetch paginated records
$query = "SELECT * FROM transactions ORDER BY created_at DESC LIMIT :start, :limit";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaction History</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f1f5f9;
      margin: 0;
      padding: 0;
    }

    .container {
     
      background: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      margin-left: 260px;
    }

    .page-header {
      font-size: 26px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #111827;
    }

    .total-count {
      font-size: 16px;
      margin-bottom: 25px;
      color: #374151;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    table thead {
      background-color: #f3f4f6;
    }

    table th, table td {
      padding: 14px 16px;
      border-bottom: 1px solid #e5e7eb;
      text-align: left;
      font-size: 14px;
      color: #374151;
    }

    table tbody tr:hover {
      background-color: #f9fafb;
    }

    .pagination {
      text-align: center;
    }

    .pagination a {
      margin: 0 4px;
      padding: 8px 14px;
      font-size: 14px;
      text-decoration: none;
      color: #374151;
      background-color: #e5e7eb;
      border-radius: 6px;
      transition: background-color 0.3s, color 0.3s;
    }

    .pagination a:hover {
      background-color: #d1d5db;
    }

    .pagination a.active {
      background-color: #2563eb;
      color: #fff;
    }

    @media (max-width: 768px) {
      .container {
        margin-left: 0;
        margin: 20px;
        padding: 20px;
      }

      table th, table td {
        font-size: 13px;
        padding: 10px;
      }
       .sidebar {
    display: none;
  }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="page-header">📄 Transaction History</div>

  <div class="total-count">
    Total Transactions: <strong><?= $totalTransactions ?></strong>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>User ID</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Created At</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($transactions): ?>
        <?php foreach ($transactions as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['user_id']) ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td>₹<?= htmlspecialchars(number_format($row['amount'], 2)) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">No transactions found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="pagination">
    <?php
      $total_pages = ceil($totalTransactions / $records_per_page);
      for ($i = 1; $i <= $total_pages; $i++):
    ?>
      <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</div>

</body>
</html>

