<?php include('../sidebar.php'); ?>
<?php include('../auth_check.php'); ?>

<?php

require '../config/db.php'; // or the correct relative path

// Get current page from URL or default to dashboard
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Fetch user data from database (example)
$userId = 1; // In real app, this would come from session
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Fetch wallet balance
$wallet = ['inr_balance' => 0, 'usdt_balance' => 0]; // Default values

try {
    $walletStmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
    $walletStmt->execute([$userId]);
    $walletData = $walletStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($walletData) {
        $wallet = [
            'inr_balance' => $walletData['inr_balance'] ?? 0,
            'usdt_balance' => $walletData['usdt_balance'] ?? 0
        ];
    } else {
            $initStmt = $pdo->prepare("INSERT INTO wallets (user_id, inr_balance, usdt_balance) VALUES (?, 0, 0)");
        $initStmt->execute([$userId]);
    }
} catch (PDOException $e) {
    error_log("Wallet error: " . $e->getMessage());
}
// Fetch recent transactions
$transactions = [];
try {
    $txnStmt = $pdo->prepare("SELECT * FROM user_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $txnStmt->execute([$userId]);
    $transactions = $txnStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Transactions error: " . $e->getMessage());
}

// Fetch current market price from API (simulated)
$currentPrice = getCurrentUSDTPrice(); // Function to get live price
$priceChange24h = get24hPriceChange(); // Function to get 24h change

// Function to simulate getting current USDT price
function getCurrentUSDTPrice() {
    // In real app, you would call an API like Binance, CoinGecko, etc.
    $basePrice = 84.32;
    // Add some random fluctuation to simulate live market
    return $basePrice + (rand(-100, 100) / 100);
}

// Function to simulate 24h price change
function get24hPriceChange() {
    // In real app, get this from API
    $baseChange = 2.45;
    // Add some random fluctuation
    return $baseChange + (rand(-50, 50) / 100);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DollaRio Pro Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Your existing CSS styles here */
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

    /* ======== Sidebar ======== */
    .sidebar {
      width: 280px;
      padding: 32px 24px;
      box-shadow: 2px 0 12px rgba(0,0,0,0.05);
      transition: 0.3s;
    }

    .sidebar-header {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 48px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 14px 18px;
      margin: 8px 0;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      color: var(--text-secondary);
    }

    .nav-item.active,
    .nav-item:hover {
      background: rgba(99, 102, 241, 0.08);
      color: var(--primary);
    }

    /* ======== Main Content ======== */
    .main-content {
      flex: 1;
    
      display: grid;
      gap: 24px;
      margin-left: 260px;
    }

    /* ======== Dashboard Grid ======== */
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
    }

    /* ======== Cards ======== */
    .dashboard-card {
      background: var(--surface);
      padding: 28px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      transition: transform 0.2s ease;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .card-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    /* ======== Live Price Graph ======== */
    .price-graph {
      height: 150px;
      background: var(--background);
      border-radius: 12px;
      margin: 20px 0;
      position: relative;
      overflow: hidden;
    }

    /* ======== Transaction List ======== */
    .transaction-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .transaction-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      background: var(--background);
      border-radius: 12px;
    }

    .transaction-details {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    /* ======== Page Content Styles ======== */
    .page-content {
      display: none;
      grid-column: 1 / -1;
    }

    .page-content.active {
      display: block;
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
    }

    /* Responsive styles remain the same */
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -280px;
        z-index: 100;
        height: 100vh;
      }
      
      .sidebar.active {
        left: 0;
      }

      .main-content {
      
        width: 100%;
        margin-left: 0;
      }
    }

    /* Basic Styling */
/* Default header styles for larger screens */


  /* Hide header by default (for screens larger than 768px) */
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
    <button class="menu-btn" id="menuToggle">☰</button>
  </div>
</header>

    <!-- Dashboard View -->
    <div class="dashboard-grid <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" id="dashboard-view">
      <!-- Wallet Summary -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3 class="card-title">
            <span class="material-icons-round">account_balance_wallet</span>
            Wallet Summary
          </h3>
          <span class="material-icons-round">more_vert</span>
        </div>
        <div class="balance-display">
          <div style="font-size: 1.7rem; font-weight: 700; color: var(--primary);">
          <?php
$totalBalance = $wallet['inr_balance'] + ($wallet['usdt_balance'] * $currentPrice);
echo '₹' . number_format($totalBalance, 2);
?>
          <div style="display: flex; gap: 24px; margin-top: 20px;">
            <div>
              <div style="color: var(--text-secondary);">USDT Balance</div>
              <div style="font-size: 1.2rem; font-weight: 600;"><?php echo number_format($wallet['usdt_balance'], 2); ?> USDT</div>
              <div style="color: var(--text-secondary); font-size: 0.9rem;">₹<?php echo number_format($wallet['usdt_balance'] * $currentPrice, 2); ?></div>
            </div>
            <div>
              <div style="color: var(--text-secondary);">INR Balance</div>
              <div style="font-size: 1.2rem; font-weight: 600;">₹<?php echo number_format($wallet['inr_balance'], 2); ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Market Overview -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3 class="card-title">
            <span class="material-icons-round">trending_up</span>
            Market Overview
          </h3>
          <div style="color: var(--primary); font-weight: 600;">Live</div>
        </div>
        <div class="price-graph">
          <canvas id="priceChart"></canvas>
        </div>
        <div class="market-stats">
          <div style="display: flex; justify-content: space-between; margin-top: 16px;">
            <div>
              <div style="color: var(--text-secondary);">Current Price</div>
              <div style="font-size: 1.5rem; font-weight: 700;">₹<?php echo number_format($currentPrice, 2); ?></div>
            </div>
            <div>
              <div style="color: var(--text-secondary);">24h Change</div>
              <div style="color: <?php echo $priceChange24h >= 0 ? '#22c55e' : '#ef4444'; ?>; font-weight: 700;">
                <?php echo ($priceChange24h >= 0 ? '+' : '') . number_format($priceChange24h, 2); ?>%
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3 class="card-title">
            <span class="material-icons-round">flash_on</span>
            Quick Actions
          </h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 20px;">
         <!-- Buy Crypto Button -->
<a href="javascript:void(0)" onclick="openBuyModal()" style="background: var(--primary); color: white; padding: 16px; border: none; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
  <span class="material-icons-round">download</span>
  Buy Crypto
</a>

<!-- Modal for Buying USDT -->
<div id="buyModal" style="display:none; position:fixed; top:20%; left:30%; background:#fff; padding:20px; border-radius:10px; z-index:999; box-shadow: 0 0 10px rgba(0,0,0,0.3); width: 350px;">
  <h3 style="margin-bottom: 16px;">Buy USDT</h3>
  <form action="?page=buysell&action=buy-process" method="POST">
    <input type="hidden" name="coin" value="USDT">
    
    <label>Amount (USDT):</label><br>
    <input type="number" name="usdt_amount" id="usdt_amount" placeholder="e.g. 10" required oninput="calculateTotal()" style="width:100%; padding:8px; margin-bottom: 12px;"><br>

    <label>Price (INR per USDT):</label><br>
    <input type="number" name="price" id="price" value="85" required oninput="calculateTotal()" style="width:100%; padding:8px; margin-bottom: 12px;"><br>

    <label><strong>Total (INR):</strong></label><br>
    <input type="text" id="total" name="total" readonly style="width:100%; padding:8px; margin-bottom: 20px; background: #f0f0f0; border: 1px solid #ccc;"><br>

    <button type="submit" style="background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 6px; width: 100%;">Buy USDT</button>
    <button type="button" onclick="closeBuyModal()" style="margin-top: 10px; width: 100%;">Cancel</button>
  </form>
</div>

<!-- JavaScript -->
<script>
function openBuyModal() {
  document.getElementById('buyModal').style.display = 'block';
}

function closeBuyModal() {
  document.getElementById('buyModal').style.display = 'none';
}

function calculateTotal() {
  let amount = parseFloat(document.getElementById('usdt_amount').value) || 0;
  let price = parseFloat(document.getElementById('price').value) || 0;
  document.getElementById('total').value = (amount * price).toFixed(2);
}
</script>

          <a href="../page/sdw/sell.php" style="background: var(--background); padding: 16px; border: none; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span class="material-icons-round">upload</span>
            Sell Crypto
          </a>
          <a href="../page/sdw/deposit.php" style="background: var(--background); padding: 16px; border: none; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span class="material-icons-round">account_balance</span>
            Deposit INR
          </a>
          <a href="../page/sdw/withdraw.php" style="background: var(--background); padding: 16px; border: none; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span class="material-icons-round">payments</span>
            Withdraw INR
          </a>
        </div>
      </div>

      <!-- Recent Activity -->
   <!-- Recent Activity -->
<div class="dashboard-card">
    <div class="card-header">
        <h3 class="card-title">
            <span class="material-icons-round">notifications</span>
            Recent Activity
        </h3>
        <?php if (!empty($transactions)): ?>
            <a href="?page=history" style="text-decoration: none; color: var(--primary); font-size: 0.9rem;">View All</a>
        <?php endif; ?>
    </div>
    <div class="transaction-list">
        <?php if (!empty($transactions)): ?>
            <?php foreach ($transactions as $txn): ?>
                <div class="transaction-item">
                    <div class="transaction-details">
                        <span class="material-icons-round" style="color: <?php echo in_array($txn['type'], ['deposit', 'buy']) ? '#22c55e' : '#ef4444'; ?>;">
                            <?php echo in_array($txn['type'], ['deposit', 'buy']) ? 'arrow_circle_up' : 'arrow_circle_down'; ?>
                        </span>
                        <div>
                            <div><?php echo htmlspecialchars($txn['description'] ?? 'Transaction'); ?></div>
                            <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                <?php echo date('d M Y, h:i A', strtotime($txn['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <div style="font-weight: 600; color: <?php echo in_array($txn['type'], ['deposit', 'buy']) ? '#22c55e' : '#ef4444'; ?>;">
                        <?php echo in_array($txn['type'], ['deposit', 'buy']) ? '+' : '-'; ?>
                        <?php echo ($txn['currency'] === 'INR' ? '₹' : '') . number_format($txn['amount'], 2); ?>
                        <?php echo $txn['currency'] === 'USDT' ? ' USDT' : ''; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                No recent transactions
            </div>
        <?php endif; ?>
    </div>
</div>
    </div>

    <!-- Other page content views remain the same -->
  </main>

  <script>
  

    // Price Chart
    const ctx = document.getElementById('priceChart').getContext('2d');
    const priceChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: Array.from({length: 24}, (_, i) => {
          const d = new Date();
          d.setHours(d.getHours() - 24 + i);
          return d.getHours() + ':00';
        }),
        datasets: [{
          label: 'USDT/INR',
          data: Array.from({length: 24}, () => {
            const base = <?php echo $currentPrice; ?>;
            return (base + (Math.random() - 0.5) * 2).toFixed(2);
          }),
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          tension: 0.4,
          fill: true,
          pointRadius: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            display: false
          },
          y: {
            display: false
          }
        }
      }
    });

    // Simulate live price updates
    setInterval(() => {
      // Update chart
      const newData = priceChart.data.datasets[0].data.slice(1);
      const lastPrice = parseFloat(newData[newData.length - 1]);
      newData.push((lastPrice + (Math.random() - 0.5) * 0.5).toFixed(2));
      priceChart.data.datasets[0].data = newData;
      priceChart.update();
      
      // Update price displays
      const currentPrice = parseFloat(newData[newData.length - 1]);
      const previousPrice = parseFloat(newData[newData.length - 2]);
      const change = ((currentPrice - previousPrice) / previousPrice * 100).toFixed(2);
      
      document.querySelectorAll('.market-stats div:nth-child(1) div:last-child').forEach(el => {
        el.textContent = '₹' + currentPrice.toFixed(2);
      });
      
      document.querySelectorAll('.market-stats div:nth-child(2) div:last-child').forEach(el => {
        el.textContent = (change >= 0 ? '+' : '') + change + '%';
        el.style.color = change >= 0 ? '#22c55e' : '#ef4444';
      });
    }, 5000);

    // Amount calculation for buy/sell
    const amountInput = document.getElementById('amount');
    if (amountInput) {
      amountInput.addEventListener('input', () => {
        const amount = parseFloat(amountInput.value) || 0;
        const usdtAmount = document.getElementById('usdt-amount');
        const inrAmount = document.getElementById('inr-amount');
        
        if (usdtAmount) {
          const currentPrice = parseFloat(document.querySelector('.market-stats div:nth-child(1) div:last-child').textContent.replace('₹', ''));
          usdtAmount.value = (amount / currentPrice).toFixed(2);
        }
        
        if (inrAmount) {
          const currentPrice = parseFloat(document.querySelector('.market-stats div:nth-child(1) div:last-child').textContent.replace('₹', ''));
          inrAmount.value = (amount * currentPrice).toFixed(2);
        }
      });
    }
  </script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const menuBtn = document.querySelector('.menu-btn');
    const sidebar = document.querySelector('.sidebar');

    if (menuBtn && sidebar) {
      menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
      });
    }
  });
</script>
</body>
</html>