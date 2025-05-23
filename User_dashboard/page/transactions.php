<?php include('../sidebar.php'); ?>
<?php
// Database connection
$host = 'localhost';
$dbname = 'dollario_admin';
$username = 'admin';
$password = 'Dollario1234567';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

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
  <style>
    body { font-family: Arial, sans-serif; background: #f6f8fb; }
    .container {  margin: 0 auto; background: #fff; padding: 20px; border-radius: 12px; margin-left: 260px; }
    .page-header { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
    .total-count { font-size: 18px; margin-bottom: 20px; color: #333; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table th, table td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
    table th { background-color: #f0f0f0; }
    .pagination a {
      margin: 0 5px; padding: 8px 12px; text-decoration: none; color: #333;
      background-color: #eee; border-radius: 5px;
    }
    .pagination a.active { background-color: #007bff; color: #fff; }
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
            <td><?= htmlspecialchars($row['amount']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No transactions found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
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
