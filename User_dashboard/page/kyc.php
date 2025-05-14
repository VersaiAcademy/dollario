<?php include('../sidebar.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include('../auth_check.php');

// DB setup
$host = 'localhost';
$dbname = 'dollario_admin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$kycStatus = 'not_submitted';
$rejectionReason = '';
$kycRecordExists = false;

try {
    $stmt = $pdo->prepare("SELECT * FROM user_kyc WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $kycData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($kycData) {
        $kycStatus = $kycData['status'];
        $rejectionReason = $kycData['rejection_reason'] ?? '';
        $kycRecordExists = true;
    }
} catch (PDOException $e) {
    error_log("KYC status error: " . $e->getMessage());
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadPath = __DIR__ . "/../uploads/kyc_documents/$user_id/";
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0775, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    function saveFile($fileInput, $prefix, $uploadPath, $allowedTypes) {
        if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$fileInput]['tmp_name'];
            $fileType = mime_content_type($fileTmp);

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Invalid file type for $prefix");
            }

            $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
            $filename = $prefix . '_' . time() . '.' . $ext;
            $destination = $uploadPath . $filename;

            if (!move_uploaded_file($fileTmp, $destination)) {
                throw new Exception("Failed to upload $prefix file.");
            }

            return "uploads/kyc_documents/" . $filename;
        }
        return null;
    }

    try {
        $panPath = saveFile('pan_file', 'pan', $uploadPath, $allowedTypes);
        $aadhaarPath = saveFile('aadhaar_file', 'aadhaar', $uploadPath, $allowedTypes);
        $bankPath = saveFile('bank_file', 'bank', $uploadPath, $allowedTypes);

        if ($kycRecordExists) {
            $stmt = $pdo->prepare("UPDATE user_kyc SET pan_path=?, aadhaar_path=?, bank_path=?, status='pending', rejection_reason=NULL WHERE user_id=?");
            $stmt->execute([$panPath, $aadhaarPath, $bankPath, $user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_kyc (user_id, pan_path, aadhaar_path, bank_path, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $panPath, $aadhaarPath, $bankPath]);
        }

        header("Location: kyc.php?success=1");
        exit;

    } catch (Exception $e) {
        header("Location: kyc.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}


$kycStatus = $kycStatus ?? 'pending';
$rejectionReason = $rejectionReason ?? '';

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DollaRio Pro - KYC Verification</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 0;
    }

    .kyc-wrapper {
      max-width: 800px;
      margin-left: 260px;
      background: #fff;
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    .kyc-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .kyc-header h2 {
      display: flex;
      align-items: center;
      font-size: 24px;
      gap: 10px;
    }

    .kyc-badge {
      padding: 6px 16px;
      border-radius: 50px;
      font-size: 14px;
      font-weight: 600;
      color: #fff;
    }

    .approved { background: #2ecc71; }
    .pending { background: #f1c40f; }
    .rejected { background: #e74c3c; }

    .upload-section {
      display: grid;
      gap: 20px;
      grid-template-columns: 1fr 1fr;
    }

    .upload-box {
      border: 2px dashed #ccc;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
    }

    .upload-box:hover {
      border-color: #0066ff;
      background: #f9fbff;
    }

    .upload-box input {
      opacity: 0;
      position: absolute;
      width: 100%;
      height: 100%;
      cursor: pointer;
      top: 0;
      left: 0;
    }

    .upload-box .material-icons-round {
      font-size: 36px;
      color: #0066ff;
      margin-bottom: 8px;
    }

    .kyc-btn {
      margin-top: 30px;
      width: 100%;
      padding: 14px;
      background: #0066ff;
      color: #fff;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .kyc-btn:hover {
      background: #0052cc;
    }

    .kyc-status, .success-text, .error-text {
      margin-top: 30px;
      padding: 15px;
      border-radius: 10px;
    }

    .kyc-status {
      background: #f9f9f9;
      border-left: 4px solid #0066ff;
    }

    .success-text {
      background-color: #e0f8e9;
      color: #2e7d32;
      border-left: 4px solid #2ecc71;
    }

    .error-text {
      background-color: #ffe8e8;
      color: #c0392b;
      border-left: 4px solid #e74c3c;
    }

    .faq-section {
      margin-top: 40px;
    }

    .faq-section h3 {
      margin-bottom: 15px;
      font-size: 20px;
      color: #333;
    }

    .faq-section ul {
      padding-left: 20px;
    }

    .faq-section ul li {
      margin-bottom: 10px;
    }

    @media(max-width: 768px) {
      .upload-section {
        grid-template-columns: 1fr;
      }

      .kyc-wrapper {
        margin-left: 20px;
        margin-right: 20px;
      }
    }

    @media (max-width: 768px) {
  .sidebar {
    display: none;
  }
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
      

  <div class="kyc-wrapper">
    <div class="kyc-header">
      <h2><span class="material-icons-round">verified_user</span>KYC Verification</h2>
      <span class="kyc-badge <?= $kycStatus ?>"><?= ucfirst($kycStatus) ?></span>
    </div>

    <?php if (!empty($rejectionReason)): ?>
      <p class="error-text"><strong>Rejection Reason:</strong> <?= htmlspecialchars($rejectionReason) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <p class="success-text">✅ KYC documents submitted successfully!</p>
    <?php endif; ?>

    <?php if ($kycStatus !== 'approved'): ?>
      <form action="kyc.php" method="POST" enctype="multipart/form-data">
        <div class="upload-section">
          <label class="upload-box">
            <span class="material-icons-round">credit_card</span>
            <div>PAN Card</div>
            <input type="file" name="pan_file" required>
          </label>

          <label class="upload-box">
            <span class="material-icons-round">badge</span>
            <div>Aadhaar Card</div>
            <input type="file" name="aadhaar_file" required>
          </label>

          <label class="upload-box">
            <span class="material-icons-round">account_balance</span>
            <div>Bank Statement</div>
            <input type="file" name="bank_file" required>
          </label>
        </div>

        <button type="submit" class="kyc-btn">Submit Documents</button>
      </form>
      
    <?php else: ?>
      <div class="kyc-status">
        ✅ Your KYC is already approved. No further action is needed.
      </div>
    <?php endif; ?>
    

    <div class="faq-section">
      <h3>Frequently Asked Questions (KYC)</h3>
      <ul>
        <li><strong>Why is KYC needed?</strong> <br>It helps us verify your identity and comply with financial regulations.</li>
        <li><strong>How long does KYC approval take?</strong> <br>Usually within 24–48 hours after document submission.</li>
        <li><strong>What if my KYC gets rejected?</strong> <br>We will notify you with a reason so you can re-submit with corrections.</li>
        <li><strong>Is my data secure?</strong> <br>Yes, we use encrypted and secure servers to protect your documents.</li>
      </ul>
    </div>
  </div> <script>
   const menuBtn = document.querySelector('.menu-btn');
const sidebar = document.querySelector('.sidebar');

menuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});


  </script>

</body>
</html>
