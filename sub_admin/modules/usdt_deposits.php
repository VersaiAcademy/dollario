<?php include '../templates/header.php'; include '../templates/sidebar.php'; ?>
<?php
// usdt-deposit.php
$pageTitle = "USDT Deposits | Your Platform";
require '../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';


use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

// Ethereum node configuration
$web3 = new Web3(new HttpProvider(new HttpRequestManager('http://localhost:8545', 30)));
$usdtContractAddress = '0xdAC17F958D2ee523a2206206994597C13D831ec7'; // USDT contract address
$usdtAbi = '[{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"type":"function"}]';

// Handle deposit tracking
function checkDeposits() {
    global $web3, $usdtContractAddress, $usdtAbi, $pdo;
    
    // Get all user wallets
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        $contract = new Contract($web3->provider, $usdtAbi);
        $wallet = $user['usdt_wallet'];
        
        // Get USDT balance
        $contract->at($usdtContractAddress)->call('balanceOf', $wallet, function ($err, $result) use ($wallet, $user) {
            if ($err !== null) return;
            
            $balance = hexdec($result[0]->toString()) / pow(10,6); // USDT has 6 decimals
            if ($balance > 0) {
                // Check existing transactions
                $stmt = $pdo->prepare("SELECT * FROM usdt_deposits WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($existing) === 0) {
                    // Add new deposit (in real implementation, track actual transactions)
                    $txHash = '0x'.bin2hex(random_bytes(32)); // Simulated tx hash
                    $stmt = $pdo->prepare("INSERT INTO usdt_deposits (user_id, tx_hash, amount) VALUES (?, ?, ?)");
                    $stmt->execute([$user['id'], $txHash, $balance]);
                }
            }
        });
    }
}

// Update confirmations
function updateConfirmations() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM usdt_deposits WHERE status = 'pending'");
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($deposits as $deposit) {
        // Simulate block confirmations (replace with actual blockchain check)
        $newConfirmations = $deposit['confirmations'] + 1;
        $status = $newConfirmations >= 3 ? 'confirmed' : 'pending';
        
        $stmt = $pdo->prepare("UPDATE usdt_deposits SET confirmations = ?, status = ? WHERE id = ?");
        $stmt->execute([$newConfirmations, $status, $deposit['id']]);
    }
}

// Run checks every time page loads (should be cron job in production)
checkDeposits();
updateConfirmations();

// Get all deposits
$stmt = $pdo->query("SELECT u.username, d.* FROM usdt_deposits d JOIN users u ON d.user_id = u.id");
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?></title>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .deposit-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .deposit-table th, .deposit-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .deposit-table th { background-color: #f8f9fa; }
        .status-pending { color: #ffc107; }
        .status-confirmed { color: #28a745; }
        .refresh-btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>USDT Deposits</h1>
        <button class="refresh-btn" onclick="location.reload()">Refresh Status</button>
        
        <table class="deposit-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Transaction Hash</th>
                    <th>Amount</th>
                    <th>Confirmations</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deposits as $deposit): ?>
                <tr>
                    <td><?= htmlspecialchars($deposit['username']) ?></td>
                    <td><?= substr($deposit['tx_hash'], 0, 12) ?>...<?= substr($deposit['tx_hash'], -6) ?></td>
                    <td><?= number_format($deposit['amount'], 6) ?> USDT</td>
                    <td><?= $deposit['confirmations'] ?>/3</td>
                    <td class="status-<?= $deposit['status'] ?>">
                        <?= ucfirst($deposit['status']) ?>
                    </td>
                    <td><?= date('Y-m-d H:i:s', strtotime($deposit['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php include '../templates/footer.php'; ?>