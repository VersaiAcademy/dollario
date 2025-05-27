<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // ✅ Safe: sirf tabhi chalega jab session start nahi hua ho
}
require '../includes/config.php';
// Connection check
if(!isset($pdo)) {
    die("Database connection failed!");
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$referral_link = '';
$total_referrals = 0;
$completed_referrals = 0;
$earned_rewards = 0;
$referrals = [];

try {
    // Get user referral data
    $stmt = $pdo->prepare("
        SELECT 
            u.referral_code,
            COUNT(r.id) AS total,
            SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) AS completed
        FROM users u
        LEFT JOIN referrals r ON u.id = r.referrer_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $data = $stmt->fetch();
    
    $referral_code = $data['referral_code'];
    $referral_link = "https://yourdomain.com/register?ref=" . $referral_code;
    $total_referrals = $data['total'];
    $completed_referrals = $data['completed'];
    $earned_rewards = $completed_referrals * 10;

    // Handle withdrawal request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING);
        $wallet = filter_input(INPUT_POST, 'wallet', FILTER_SANITIZE_STRING);

        if ($amount < 50) {
            $error = "Minimum withdrawal amount is $50";
        } elseif ($amount > $earned_rewards) {
            $error = "Insufficient balance";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO withdrawals 
                (user_id, amount, method, wallet_address, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $amount, $method, $wallet]);
            $success = "Withdrawal request for $".number_format($amount,2)." submitted!";
        }
    }

    // Get referral history
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            u.email AS referee_email,
            r.status,
            r.created_at,
            r.completed_at
        FROM referrals r
        JOIN users u ON r.referee_id = u.id
        WHERE r.referrer_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $referrals = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include '../templates/sidebar.php';
include '../templates/header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Program</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-area { margin-left: 260px; padding: 0; }
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 500px; }
        .switch { position: relative; display: inline-block; width: 60px; height: 34px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background: #4CAF50; }
        input:checked + .slider:before { transform: translateX(26px); }
        @media (max-width: 768px) {
            .header{
                margin-left: 0px;
            }
            .content-area{
                margin-left: 0px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="content-area">
    <div class="container mx-auto  py-8">
        <?php if($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $success ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Referral Program</h1>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Your Referral Link</h3>
                    <div class="flex">
                        <input type="text" id="referralLink" value="<?= htmlspecialchars($referral_link) ?>" 
                               class="flex-grow px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
                        <button onclick="copyReferralLink()" class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">Total Referrals</h3>
                    <p class="text-3xl font-bold text-green-600"><?= $total_referrals ?></p>
                    <p class="text-sm text-gray-600"><?= $completed_referrals ?> completed</p>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-purple-800 mb-2">Earned Rewards</h3>
                    <p class="text-3xl font-bold text-purple-600">$<?= number_format($earned_rewards, 2) ?></p>
                    <button onclick="openModal('withdrawModal')" class="mt-2 bg-purple-600 text-white px-4 py-1 rounded-md text-sm hover:bg-purple-700 transition">
                        Withdraw
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Your Referrals</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reward</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($referrals as $referral): ?>
                            <tr data-ref-id="<?= $referral['id'] ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($referral['referee_email']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($referral['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $referral['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($referral['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= ucfirst($referral['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    $<?= $referral['status'] === 'completed' ? '10.00' : '0.00' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewReferralDetails('<?= $referral['id'] ?>')" class="text-blue-600 hover:text-blue-900">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mt-8">
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h2 class="text-xl font-semibold mb-4">How It Works</h2>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="bg-indigo-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-share-alt text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-medium mb-1">Share Your Link</h3>
                            <p class="text-gray-600 text-sm">Share your unique referral link with friends via social media, email, or direct messages</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-green-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-user-check text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-medium mb-1">They Sign Up</h3>
                            <p class="text-gray-600 text-sm">Friends sign up using your referral link and verify their account</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-gift text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-medium mb-1">Earn Rewards</h3>
                            <p class="text-gray-600 text-sm">Earn $10 for each friend who completes their first transaction</p>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    
</div>


<!-- Withdraw Modal -->
<div id="withdrawModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Withdraw Earnings</h3>
            <button onclick="closeModal('withdrawModal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" action="">
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Available Balance</p>
                    <p class="text-2xl font-bold text-gray-800">$<?= number_format($earned_rewards, 2) ?></p>
                </div>
                
                <div>
                    <label for="withdrawAmount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" name="amount" id="withdrawAmount" min="50" max="<?= $earned_rewards ?>" step="0.01" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Minimum $50.00" required>
                </div>
                
                <div>
                    <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="method" id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select payment method</option>
                        <option value="paypal">PayPal</option>
                        <option value="crypto">Cryptocurrency</option>
                    </select>
                </div>
                
                <div id="walletDetails" class="hidden">
                    <label for="wallet" class="block text-sm font-medium text-gray-700 mb-1">Wallet/Email</label>
                    <input type="text" name="wallet" id="wallet" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Enter wallet address or email" required>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('withdrawModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                    Request Withdrawal
                </button>
            </div>
        </form>
    </div>
    
</div>



<!-- Notification -->
<div id="notification" class="fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg hidden"></div>

<script>
// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Copy referral link
function copyReferralLink() {
    const link = document.getElementById('referralLink');
    link.select();
    document.execCommand('copy');
    showNotification('Link copied to clipboard!', 'green');
}

// Show payment method details
document.getElementById('paymentMethod').addEventListener('change', function() {
    const walletDiv = document.getElementById('walletDetails');
    walletDiv.classList.toggle('hidden', this.value === '');
});

// Show notification
function showNotification(message, color = 'green') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg bg-${color}-100 text-${color}-700`;
    notification.classList.remove('hidden');
    
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);
}

// View referral details (AJAX example)
function viewReferralDetails(refId) {
    fetch(`get_referral.php?id=${refId}`)
        .then(response => response.json())
        .then(data => {
            // Populate modal with data
            console.log('Referral details:', data);
            showNotification('Showing details for referral #' + refId, 'blue');
        })
        .catch(error => {
            showNotification('Error loading details', 'red');
        });
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

</body>
</html>