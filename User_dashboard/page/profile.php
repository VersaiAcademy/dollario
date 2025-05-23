<?php include('../sidebar.php'); ?>

<?php
require '../config/db.php'; // or the correct relative path

// Get current page from URL
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'profile';

// Fetch current logged-in user (replace with session logic in real app)
$userId = 1;
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Fetch wallet balance
$walletStmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
$walletStmt->execute([$userId]);
$wallet = $walletStmt->fetch(PDO::FETCH_ASSOC) ?? ['inr_balance' => 0, 'usdt_balance' => 0];

// Fetch KYC status
$kycStmt = $pdo->prepare("SELECT status FROM kyc_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$kycStmt->execute([$userId]);
$kyc = $kycStmt->fetch(PDO::FETCH_ASSOC);
if (!$kyc) {
    $kyc = ['status' => 'not_verified'];
}

// Current USDT price (simulated)
$currentPrice = 84.50 + (rand(-100, 100) / 100);

// ✅ Fetch all users
$usersStmt = $pdo->query("SELECT * FROM users");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['success']) && $_GET['success'] == 'profile_updated') {
    echo "<p style='color:green;'>Profile updated successfully!</p>";
} elseif (isset($_GET['error']) && $_GET['error'] == 'update_failed') {
    echo "<p style='color:red;'>Failed to update profile. Try again.</p>";
}

// bank detals
$bankStmt = $pdo->prepare("SELECT * FROM bank_accounts WHERE user_id = ? ORDER BY is_primary DESC, added_on DESC");
$bankStmt->execute([$userId]);
$bankAccounts = $bankStmt->fetchAll(PDO::FETCH_ASSOC);

// kyc 


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DollaRio Pro - My Profile</title>
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
      --success: #22c55e;
      --warning: #f59e0b;
      --danger: #ef4444;
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

    /* ======== Main Content ======== */
    .main-content {
      flex: 1;
    
      display: grid;
    
      margin-left: 260px;
    }

    /* ======== Page Header ======== */
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

    /* ======== Profile Card ======== */
    .profile-card {
      background: var(--surface);
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      overflow: hidden;
    }

    .profile-header {
      padding: 24px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
    }

    .profile-info h2 {
      font-size: 1.5rem;
      margin-bottom: 4px;
    }

    .profile-info p {
      opacity: 0.9;
      font-size: 0.9rem;
    }

    .profile-body {
      padding: 24px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    /* ======== Info Sections ======== */
    .info-section {
      margin-bottom: 24px;
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid #f1f5f9;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 120px 1fr;
      gap: 12px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
    }

    .info-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
    }

    .info-value {
      font-weight: 500;
      color: var(--text-primary);
    }

    /* ======== KYC Status ======== */
    .kyc-status {
      padding: 12px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-top: 12px;
    }

    .kyc-verified {
      background: rgba(34, 197, 94, 0.1);
      color: var(--success);
    }

    .kyc-pending {
      background: rgba(245, 158, 11, 0.1);
      color: var(--warning);
    }

    .kyc-not-verified {
      background: rgba(239, 68, 68, 0.1);
      color: var(--danger);
    }

    /* ======== Wallet Summary ======== */
    .wallet-summary {
      background: var(--background);
      border-radius: 12px;
      padding: 16px;
      margin-top: 12px;
    }

    .wallet-item {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .wallet-item:last-child {
      border-bottom: none;
    }

    /* ======== Action Buttons ======== */
    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 24px;
    }

    .btn {
      padding: 10px 16px;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
      border: none;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }

    .btn-outline:hover {
      background: rgba(99, 102, 241, 0.1);
    }

    /* ======== Responsive Styles ======== */
    @media (max-width: 1024px) {
      .profile-body {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .main-content {
       
        margin-left: 0;
      }

      .profile-header {
        flex-direction: column;
        text-align: center;
      }

      .action-buttons {
        flex-direction: column;
      }
    }

    @media (max-width: 480px) {
      .info-grid {
        grid-template-columns: 1fr;
        gap: 8px;
      }
    }

    /* Default: hide header on desktop */
header {
  display: none;
  background-color: #0e1a2b;
  padding: 10px 20px;
  align-items: center;
  justify-content: space-between;
  color: white;
}

/* Show header only on phones/tablets */
@media (max-width: 768px) {
  header {
    display: flex;
  }
  .container{
    margin-left: 0px;
  }
}

.logo-container img.logo {
  width: 150px;
  height: auto;
}

.menu-container .menu-btn {
  font-size: 28px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
}

  </style>
</head>
<body>
  <!-- Sidebar -->
  <?php include('../sidebar.php'); ?>

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
        <span class="material-icons-round">person</span>
        My Profile
      </h1>
      <div>
        <button onclick="window.print()" style="background: var(--background); padding: 8px 16px; border-radius: 8px; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <span class="material-icons-round">print</span>
          Print Profile
        </button>
      </div>
    </div>

    <!-- Profile Card -->
    <div class="profile-card">
      <div class="profile-header">
        <div class="profile-avatar">
          <span class="material-icons-round">person</span>
        </div>
        <div class="profile-info">
          <h2><?php echo htmlspecialchars($user['username'] ?? 'Not set'); ?></h2>
          <p>Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></p>
        </div>
      </div>

      <div class="profile-body">
        <!-- Personal Information -->
       
  <div class="info-section">
    <h3 class="section-title">
      <span class="material-icons-round">badge</span>
      Personal Information
    </h3>

    <div class="info-grid">
      <div class="info-item">
        <span class="info-label">Full Name</span>
        <span class="info-value"><?php echo htmlspecialchars($user['username'] ?? 'Not set'); ?></span>
      </div>

      <div class="info-item">
        <span class="info-label">Email</span>
        <span class="info-value"><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></span>
      </div>

      <div class="info-item">
        <span class="info-label">Phone</span>
        <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></span>
      </div>

      <div class="info-item">
        <span class="info-label">Date of Birth</span>
        <span class="info-value">
          <?php echo isset($user['dob']) ? date('d M Y', strtotime($user['dob'])) : 'Not set'; ?>
        </span>
      </div>
    </div>

   <style>
  .form-section {
    display: none;
    margin-top: 20px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    background-color: #fff;
    animation: fadeIn 0.4s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .form-section h3 {
    margin-bottom: 15px;
    font-size: 22px;
    font-weight: 600;
    color: #333;
  }

  .form-section form input {
    width: 100%;
    padding: 10px 15px;
    margin-bottom: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
  }

  .form-section form button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
  }

  .form-section form button:hover {
    background-color: #0056b3;
  }

  .action-buttons {
    margin-top: 20px;
    display: flex;
    gap: 15px;
  }

  .btn.btn-outline {
    border: 1px solid #007bff;
    background-color: transparent;
    color: #007bff;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 15px;
  }

  .btn.btn-outline:hover {
    background-color: #007bff;
    color: #fff;
  }
</style>

<div class="action-buttons">
  <button class="btn btn-outline" onclick="showSection('edit-profile')">
    <span class="material-icons-round">edit</span>
    Edit Profile
  </button>
  <button class="btn btn-outline" onclick="showSection('change-password')">
    <span class="material-icons-round">lock</span>
    Change Password
  </button>
</div>

<!-- Edit Profile Section -->
<div id="edit-profile" class="form-section">
  <h3>Edit Profile</h3>
 <form action="../includes/edit_profile.php" method="POST">
  <input type="text" name="name" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email Address" required>
  <input type="text" name="phone" placeholder="Phone Number" required>
  <button type="submit">Save Changes</button>
</form>


</div>

<!-- Change Password Section -->
<div id="change-password" class="form-section">
  <h3>Change Password</h3>
 <form action="../includes/change_password.php" method="POST">
  <input type="password" name="old_password" placeholder="Old Password" required>
  <input type="password" name="new_password" placeholder="New Password" required>
  <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
  <button type="submit">Update Password</button>
</form>


</div>

<script>
  function showSection(sectionId) {
    // Hide all sections first
    document.querySelectorAll('.form-section').forEach(el => {
      el.style.display = 'none';
    });

    // Show the selected section with animation
    const section = document.getElementById(sectionId);
    if (section) {
      section.style.display = 'block';
    }
  }

  // Optionally show nothing by default
  showSection(null); 
</script>


  </div>



        <!-- Account Details -->
        <div class="info-section">
          <h3 class="section-title">
            <span class="material-icons-round">verified_user</span>
            Account Verification
          </h3>
          
          <?php if ($kyc['status'] === 'verified'): ?>
            <div class="kyc-status kyc-verified">
              <span class="material-icons-round">check_circle</span>
              <div>
                <strong>Identity Verified</strong>
                <div style="font-size: 0.9rem;">
                  Verified on <?php echo date('d M Y', strtotime($kyc['verified_at'])); ?>
                </div>
              </div>
            </div>
          <?php elseif ($kyc['status'] === 'pending'): ?>
            <div class="kyc-status kyc-pending">
              <span class="material-icons-round">pending</span>
              <div>
                <strong>Verification Pending</strong>
                <div style="font-size: 0.9rem;">
                  Under review by our team
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="kyc-status kyc-not-verified">
              <span class="material-icons-round">warning</span>
              <div>
                <strong>Not Verified</strong>
                <div style="font-size: 0.9rem;">
                  Complete KYC for full access
                </div>
              </div>
            </div>
          <?php endif; ?>
          
          <h3 class="section-title" style="margin-top: 24px;">
            <span class="material-icons-round">account_balance_wallet</span>
            Wallet Summary
          </h3>
          
          <div class="wallet-summary">
            <div class="wallet-item">
              <span>USDT Balance</span>
              <strong><?php echo number_format($wallet['usdt_balance'], 4); ?> USDT</strong>
            </div>
            <div class="wallet-item">
              <span>INR Balance</span>
              <strong>₹<?php echo number_format($wallet['inr_balance'], 2); ?></strong>
            </div>
            <div class="wallet-item">
              <span>Total Value</span>
              <strong>₹<?php echo number_format($wallet['inr_balance'] + ($wallet['usdt_balance'] * $currentPrice), 2); ?></strong>
            </div>
          </div>
          
          <div class="action-buttons">
          <?php if ($kyc['status'] !== 'verified'): ?>
  <a href="kyc.php" class="btn btn-primary">
    <span class="material-icons-round">verified</span>
    Complete KYC
  </a>
<?php endif; ?>
            <button class="btn btn-outline">
              <span class="material-icons-round">receipt</span>
              View Statements
            </button>
          </div>
        </div>

        <!-- Security Settings -->
      <div class="info-section">
  <h3 class="section-title">
    <span class="material-icons-round">security</span>
    Security Settings
  </h3>

  <div class="info-grid">
    <div class="info-item">
      <span class="info-label">2FA Authentication</span>
      <span class="info-value">
      <?php echo ($user['two_fa_enabled'] ?? 0) ? 'Enabled' : 'Not enabled'; ?>

      </span>
    </div>

    <div class="info-item">
      <span class="info-label">Last Login</span>
      <span class="info-value">
        <?php echo date('d M Y, h:i A', strtotime($user['last_login'] ?? 'now')); ?>
        <span style="color: var(--text-secondary); font-size: 0.8rem;">
          (<?php echo htmlspecialchars($user['ip_address'] ?? $_SERVER['REMOTE_ADDR']); ?>)
        </span>
      </span>
    </div>

    <div class="info-item">
      <span class="info-label">Account Status</span>
      <span class="info-value" style="color: <?php echo ($user['status'] === 'active') ? 'var(--success)' : 'red'; ?>">
        <?php echo ucfirst($user['status']); ?>
      </span>
    </div>
  </div>

  <div class="action-buttons">
    <button class="btn btn-outline">
      <span class="material-icons-round">admin_panel_settings</span>
          <?php echo ($user['two_fa_enabled'] ?? 0) ? 'Enabled' : 'Not enabled'; ?>
    </button>

    <button class="btn btn-outline">
      <span class="material-icons-round">devices</span>
      Manage Devices
    </button>
  </div>
</div>


        <!-- Bank Accounts -->
        <div class="info-section">
  <h3 class="section-title">
    <span class="material-icons-round">account_balance</span>
    Bank Accounts
  </h3>

  <?php if ($bankAccounts): ?>
    <?php foreach ($bankAccounts as $bank): ?>
      <div style="background: var(--background); padding: 16px; border-radius: 12px; margin-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <div>
            <strong><?= htmlspecialchars($bank['bank_name']) ?></strong>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">
              <?= str_repeat('X', strlen($bank['account_number']) - 4) . substr($bank['account_number'], -4) ?>
            </div>
          </div>
          <?php if ($bank['is_primary']): ?>
            <span class="material-icons-round" style="color: var(--success);">check_circle</span>
          <?php endif; ?>
        </div>

        <div style="font-size: 0.9rem; color: var(--text-secondary);">
          <div><?= $bank['is_primary'] ? 'Primary account for deposits & withdrawals' : '' ?></div>
          <div>Added on <?= date('d M Y', strtotime($bank['added_on'])) ?></div>
        </div>

        <div class="action-buttons" style="margin-top: 10px;">
          <?php if (!$bank['is_primary']): ?>
            <form method="post" action="set_primary_account.php">
              <input type="hidden" name="bank_id" value="<?= $bank['id'] ?>">
              <button class="btn btn-outline" type="submit">
                <a href="set_primary.php?account_id=123" class="btn btn-outline">Set as Primary</a>

              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="color: var(--text-secondary);">No bank accounts added yet.</p>
  <?php endif; ?>

  <div class="action-buttons">
    <a href="../bank_details/add_bank_account.php" class="btn btn-outline">
      <span class="material-icons-round">add</span>
      Add Bank Account
    </a>
  </div>
</div>
      </div>
    </div>
  </main>
</body>
</html>