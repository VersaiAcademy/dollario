<?php include('../sidebar.php'); ?>
<?php include('submit_help.php'); ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'PHPGangsta/GoogleAuthenticator.php';

// ---------------------- Session Timeout -----------------------
$timeout = 900; // 15 minutes

if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeout) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session expired due to inactivity.'); window.location.href='security.php';</script>";
    exit();
}
$_SESSION['last_activity'] = time();

// ---------------------- Setup -----------------------
$email = "user@example.com"; // Replace with dynamic email if needed
$ga = new PHPGangsta_GoogleAuthenticator();
$_SESSION['2fa_secret'] = $_SESSION['2fa_secret'] ?? $ga->createSecret();
$secret = $_SESSION['2fa_secret'];
$qrCodeUrl = $ga->getQRCodeGoogleUrl('MySecureApp', $secret);

// ---------------------- OTP Form Submission -----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp = $_POST['otp'];
    $checkResult = $ga->verifyCode($secret, $otp, 2); // 2 = 2*30sec clock tolerance

    if ($checkResult) {
        $_SESSION['2fa_enabled'] = true;
        echo "<script>alert('✅ 2FA Enabled Successfully!'); window.location.href='security.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Invalid OTP!'); window.location.href='security.php';</script>";
        exit();
    }
}

// ---------------------- Logout All -----------------------
if (isset($_GET['logout_all'])) {
    session_unset();
    session_destroy();
    echo "<script>alert('Logged out from all devices.'); window.location.href='security.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Security Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    
    .container{
        margin-left: 260px;
        width: 100%;
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
  .sidebar {
    display: none;
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


<body class="bg-light">
  <header>
  <div class="logo-container">
       <img src="../image/Dollario-logo .svg" alt="" style="height: auto; width: 150px;">
  </div>
  <div class="menu-container">
    <button class="menu-btn" id="menuToggle">☰</button>
  </div>
</header>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const menuBtn = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar'); // or whatever class/id your menu has

    menuBtn.addEventListener('click', function () {
      sidebar.classList.toggle('active'); // Add or remove class to show/hide menu
    });
  });
</script>
<style>  /* Hide header by default (for screens larger than 768px) */
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
    
    background: none;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
  }
}



  </style>

<div class="container ">
    
    <h2>🔐 Security Settings</h2>

    <div class="card my-4">
        <div class="card-header">Session Management</div>
        <div class="card-body">
            <p>💤 Auto Logout after 15 minutes of inactivity is enabled.</p>
            <p>🧾 JWT Token Expiry: <strong>30 minutes (example only)</strong></p>
            <a href="?logout_all=true" class="btn btn-warning">Logout from All Devices</a>
        </div>
    </div>

    <div class="card my-4">
        <div class="card-header">Two-Factor Authentication (2FA)</div>
        <div class="card-body">
            <form method="post">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggle2FA" <?= isset($_SESSION['2fa_enabled']) && $_SESSION['2fa_enabled'] ? 'checked disabled' : '' ?>>
                    <label class="form-check-label" for="toggle2FA">
                        <?= isset($_SESSION['2fa_enabled']) && $_SESSION['2fa_enabled'] ? '2FA is Enabled' : 'Enable Google Authenticator' ?>
                    </label>
                </div>

                <div id="2faSetup" class="mt-4" style="display: <?= isset($_SESSION['2fa_enabled']) && $_SESSION['2fa_enabled'] ? 'none' : 'block' ?>;">
                    <p>📱 Scan this QR code in your Google Authenticator app:</p>
                    <img src="<?= $qrCodeUrl ?>" alt="QR Code" style="max-width:200px;">
                    <div class="mt-3">
                        <label>Enter OTP:</label>
                        <input type="text" name="otp" class="form-control" placeholder="123456" required>
                        <button type="submit" class="btn btn-success mt-2">Verify & Enable</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const toggle = document.getElementById('toggle2FA');
    const setupDiv = document.getElementById('2faSetup');

    if (toggle && !toggle.disabled) {
        toggle.addEventListener('change', () => {
            setupDiv.style.display = toggle.checked ? 'block' : 'none';
        });
    }
</script>
<script>
  const menuBtn = document.querySelector('.menu-btn');
  const sidebar = document.getElementById('sidebar');

  menuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('show-sidebar');
  });
</script>

</body>
</html>
