 <?php include('../sidebar.php'); ?>
<?php 
// Start session to get user ID
session_start();

// Database connection
$host = 'localhost';
$dbname = 'dollario_admin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Get current page from URL
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'history';

// Get user ID from session (replace with your actual session variable)
$userId = $_SESSION['user_id'] ?? 1; // Fallback to 1 for testing

// Fetch user data
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

// Pagination setup
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Get filter parameters
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterCurrency = isset($_GET['currency']) ? $_GET['currency'] : 'all';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';

// Base query
$query = "SELECT * FROM user_transactions WHERE user_id = :user_id";
$countQuery = "SELECT COUNT(*) FROM user_transactions WHERE user_id = :user_id";
$params = [':user_id' => $userId];
$countParams = [':user_id' => $userId];

// Apply filters
if ($filterType !== 'all') {
    $query .= " AND type = :type";
    $countQuery .= " AND type = :type";
    $params[':type'] = $filterType;
    $countParams[':type'] = $filterType;
}

if ($filterCurrency !== 'all') {
    $query .= " AND currency = :currency";
    $countQuery .= " AND currency = :currency";
    $params[':currency'] = $filterCurrency;
    $countParams[':currency'] = $filterCurrency;
}

if ($filterStatus !== 'all') {
    $query .= " AND status = :status";
    $countQuery .= " AND status = :status";
    $params[':status'] = $filterStatus;
    $countParams[':status'] = $filterStatus;
}

// Add sorting and pagination
$query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

// Get total count
try {
    $countStmt = $pdo->prepare($countQuery);
    foreach ($countParams as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalTransactions = $countStmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Count error: " . $e->getMessage());
    $totalTransactions = 0;
}

// Fetch transactions
$transactions = [];
try {
    $stmt = $pdo->prepare($query);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Transaction query error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DollaRio Pro - Transaction History</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <style>
    :root {
      --primary: #6366f1;
      --secondary: #4f46e5;
      --background: #f8fafc;
      --surface: #ffffff;
      --text-primary: #1e293b;
      --text-secondary: #64748b;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: var(--background);
      min-height: 100vh;
      display: flex;
      -webkit-font-smoothing: antialiased;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      padding: 11px;
      display: grid;
      gap: 24px;
      margin-left: 260px;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .page-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    /* Filters */
    .filters {
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }

    .filter-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .filter-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
    }

    .filter-select {
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      background: var(--surface);
      color: var(--text-primary);
      font-size: 0.9rem;
    }

    .apply-filters {
      background: var(--primary);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.2s;
      font-size: 1rem;
    }

    .apply-filters:hover {
      background: var(--secondary);
    }

    /* Transactions Table */
    .transactions-table {
      width: 100%;
      background: var(--surface);
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      overflow: hidden;
    }

    .table-header {
      
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
      padding: 16px 24px;
      background: var(--background);
      font-weight: 600;
      color: var(--text-primary); 
      border-bottom: 1px solid #e2e8f0;
    }

    .table-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
      padding: 16px 24px;
      border-bottom: 1px solid #f1f5f9;
      align-items: center;
    }

    .table-row:last-child {
      border-bottom: none;
    }

    .transaction-type {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .type-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .type-buy {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }

    .type-sell {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
    }

    .type-deposit {
      background: rgba(99, 102, 241, 0.1);
      color: var(--primary);
    }

    .type-withdraw {
      background: rgba(245, 158, 11, 0.1);
      color: #f59e0b;
    }

    .transaction-amount {
      font-weight: 600;
    }

    .transaction-status {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      text-align: center;
    }

    .status-completed {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }

    .status-pending {
      background: rgba(245, 158, 11, 0.1);
      color: #f59e0b;
    }

    .status-failed {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
    }

    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 24px;
    }

    .pagination-button {
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      background: var(--surface);
      color: var(--text-primary);
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-block;
    }

    .pagination-button:hover {
      background: var(--background);
    }

    .pagination-button.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    .pagination-button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }



.sidebar.active {
  display: block;
}

    /* Responsive styles */
    @media (max-width: 1024px) {
      .main-content {
        margin-left: 0;
      }
      
      .table-header, .table-row {
        grid-template-columns: 1.5fr 1fr 1fr 1fr;
      }
      .table-header span:nth-child(4),
      .table-row .transaction-date {
        display: none;
      }
    }

   @media (max-width: 768px) {
  .sidebar {
    display: none;
  }
}

      
      .table-header, .table-row {
        grid-template-columns: 1.5fr 1fr 1fr;
      }
      .table-header span:nth-child(3),
      .table-row .transaction-status {
        display: none;
      }
    }

    @media (max-width: 480px) {
      .filters {
        flex-direction: column;
      }
      
      .table-header, .table-row {
        grid-template-columns: 1.5fr 1fr;
      }
      .table-header span:nth-child(2),
      .table-row .transaction-amount {
        display: none;
      }
    }

    header {
  display: none;
}


/* Show header only on phone view (768px and below) */
@media (max-width: 768px) {
  header {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color:#0e1a2b; /* You can change this */
    color: white;
  }

  .logo-container {
    flex: 1;
    text-align: left;
  }

  .menu-container {
    display: flex;
    justify-content: flex-end;
  }

  .menu-btn {
    display: block;
    background: none;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
  }
}



  </style>
</head>
<body>
  <!-- Sidebar -->
 
 

  <!-- Main Content -->
  <main class="main-content">
       <header>
  <div class="logo-container">
    <img src="../image/dollario-logo.png" alt="Logo" class="logo" style="width: 200px;">
  </div>
  <div class="menu-container">
    <button class="menu-btn">☰</button>
  </div>
</header>
    
    <div class="page-header">
      <h1 class="page-title">
        <span class="material-icons-round">history</span>
        Transaction History
      </h1>
      <button onclick="downloadPDF()" style="background: var(--background); padding: 8px 16px; border-radius: 8px; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer;">
        <span class="material-icons-round">print</span>
        Export
      </button>
    </div>

    <!-- Filters -->
    <form method="get" class="filters">
      <input type="hidden" name="page" value="history">
      
      <div class="filter-group">
        <span class="filter-label">Type:</span>
        <select name="type" class="filter-select">
          <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Types</option>
          <option value="buy" <?= $filterType === 'buy' ? 'selected' : '' ?>>Buy</option>
          <option value="sell" <?= $filterType === 'sell' ? 'selected' : '' ?>>Sell</option>
          <option value="deposit" <?= $filterType === 'deposit' ? 'selected' : '' ?>>Deposit</option>
          <option value="withdraw" <?= $filterType === 'withdraw' ? 'selected' : '' ?>>Withdraw</option>
        </select>
      </div>
      
      <div class="filter-group">
        <span class="filter-label">Currency:</span>
        <select name="currency" class="filter-select">
          <option value="all" <?= $filterCurrency === 'all' ? 'selected' : '' ?>>All Currencies</option>
          <option value="INR" <?= $filterCurrency === 'INR' ? 'selected' : '' ?>>INR</option>
          <option value="USDT" <?= $filterCurrency === 'USDT' ? 'selected' : '' ?>>USDT</option>
        </select>
      </div>
      
      <div class="filter-group">
        <span class="filter-label">Status:</span>
        <select name="status" class="filter-select">
          <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
          <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
          <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="failed" <?= $filterStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
        </select>
      </div>
      
      <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <button type="submit" class="apply-filters">
          Apply Filters
        </button>
        
        <?php if ($filterType !== 'all' || $filterCurrency !== 'all' || $filterStatus !== 'all'): ?>
          <a href="?page=history" style="background: var(--background); padding: 8px 16px; border-radius: 8px; text-decoration: none; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
            <span class="material-icons-round">clear</span>
            Clear Filters
          </a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Transactions Table -->
    <div class="transactions-table">
      <div class="table-header">
        <span>Transaction</span>
        <span>Amount</span>
        <span>Date & Time</span>
        <span>Transaction ID</span>
        <span>Status</span>
      </div>
      
      <?php if (!empty($transactions)): ?>
        <?php foreach ($transactions as $txn): ?>
          <div class="table-row">
            <div class="transaction-type">
              <div class="type-icon type-<?= $txn['type'] ?>">
                <span class="material-icons-round">
                  <?= match($txn['type']) {
                    'buy' => 'download',
                    'sell' => 'upload',
                    'deposit' => 'account_balance',
                    'withdraw' => 'payments',
                    default => 'swap_horiz'
                  } ?>
                </span>
              </div>
              <div>
                <div style="font-weight: 500; text-transform: capitalize;"><?= $txn['type'] ?></div>
                <div style="color: var(--text-secondary); font-size: 0.9rem;"><?= htmlspecialchars($txn['description'] ?? 'Transaction') ?></div>
              </div>
            </div>
            
            <div class="transaction-amount" style="color: <?= in_array($txn['type'], ['deposit', 'buy']) ? '#22c55e' : '#ef4444' ?>">
  <?= (in_array($txn['type'], ['deposit', 'buy']) ? '+' : '-') ?>
  <?= ($txn['currency'] === 'INR' ? '₹' : '') . number_format($txn['amount'], 2) ?>
  <?= $txn['currency'] === 'USDT' ? ' USDT' : '' ?>
</div>


            <div class="transaction-date" style="color: var(--text-secondary);">
              <?= date('d M Y, h:i A', strtotime($txn['created_at'])) ?>
            </div>
            
            <div style="font-family: monospace; color: var(--text-secondary); font-size: 0.9rem;">
              <?= substr($txn['txn_id'] ?? 'N/A', 0, 8) . '...' ?>
            </div>
            
            <div class="transaction-status status-<?= $txn['status'] ?? 'pending' ?>">
              <?= ucfirst($txn['status'] ?? 'pending') ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
          <span class="material-icons-round" style="font-size: 3rem; opacity: 0.5;">inbox</span>
          <p style="margin-top: 16px;">No transactions found</p>
          <?php if ($filterType !== 'all' || $filterCurrency !== 'all' || $filterStatus !== 'all'): ?>
            <a href="?page=history" style="display: inline-block; margin-top: 16px; color: var(--primary); text-decoration: none;">
              Clear filters to see all transactions
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalTransactions > $limit): ?>
      <div class="pagination">
        <a href="?page=history&p=<?= max(1, $page - 1) ?>&type=<?= $filterType ?>&currency=<?= $filterCurrency ?>&status=<?= $filterStatus ?>"
           class="pagination-button" <?= $page <= 1 ? 'disabled' : '' ?>>
          <span class="material-icons-round">chevron_left</span>
        </a>
        
        <?php 
          $totalPages = ceil($totalTransactions / $limit);
          $startPage = max(1, $page - 2);
          $endPage = min($totalPages, $page + 2);
          
          if ($startPage > 1): ?>
            <a href="?page=history&p=1&type=<?= $filterType ?>&currency=<?= $filterCurrency ?>&status=<?= $filterStatus ?>" 
               class="pagination-button">1</a>
            <?php if ($startPage > 2): ?>
              <span class="pagination-button" disabled>...</span>
            <?php endif;
          endif;
          
          for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=history&p=<?= $i ?>&type=<?= $filterType ?>&currency=<?= $filterCurrency ?>&status=<?= $filterStatus ?>"
               class="pagination-button <?= $i == $page ? 'active' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor;
          
          if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
              <span class="pagination-button" disabled>...</span>
            <?php endif; ?>
            <a href="?page=history&p=<?= $totalPages ?>&type=<?= $filterType ?>&currency=<?= $filterCurrency ?>&status=<?= $filterStatus ?>"
               class="pagination-button">
              <?= $totalPages ?>
            </a>
          <?php endif; ?>
        
        <a href="?page=history&p=<?= min($totalPages, $page + 1) ?>&type=<?= $filterType ?>&currency=<?= $filterCurrency ?>&status=<?= $filterStatus ?>"
           class="pagination-button" <?= $page >= $totalPages ? 'disabled' : '' ?>>
          <span class="material-icons-round">chevron_right</span>
        </a>
      </div>
    <?php endif; ?>
  </main>

  <script>
    function downloadPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      
      // Add title
      doc.setFontSize(18);
      doc.text('Transaction History', 14, 15);
      
      // Add date
      doc.setFontSize(10);
      doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 22);
      
      // Add table headers
      doc.setFontSize(12);
      doc.text('Transaction', 14, 32);
      doc.text('Amount', 60, 32);
      doc.text('Date', 100, 32);
      doc.text('Status', 140, 32);
      
      // Add transactions
      let y = 40;
      doc.setFontSize(10);
      
      <?php foreach ($transactions as $txn): ?>
        doc.text('<?= ucfirst($txn['type']) ?>', 14, y);
        doc.text('<?= ($txn['currency'] === 'INR' ? '₹' : '') . number_format($txn['amount'], 2) . ($txn['currency'] === 'USDT' ? ' USDT' : '') ?>', 60, y);
        doc.text('<?= date('d M Y', strtotime($txn['created_at'])) ?>', 100, y);
        doc.text('<?= ucfirst($txn['status'] ?? 'pending') ?>', 140, y);
        y += 7;
        
        // Add page break if needed
        if (y > 280) {
          doc.addPage();
          y = 20;
        }
      <?php endforeach; ?>
      
      // Save the PDF
      doc.save('transaction_history_<?= date('Y-m-d') ?>.pdf');
    }
  </script>
    <script>
   const menuBtn = document.querySelector('.menu-btn');
const sidebar = document.querySelector('.sidebar');

menuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});


  </script>
</body>
</html>