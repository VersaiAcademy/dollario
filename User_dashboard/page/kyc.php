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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DollaRio Pro - KYC Verification</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <style>
   body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #f4f7fb;
  margin: 0;
  padding: 0;
}

.kyc-container {
  max-width: 700px;
  margin: 40px auto;
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.kyc-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.kyc-header h1 {
  display: flex;
  align-items: center;
  font-size: 24px;
  gap: 10px;
}

.kyc-badge {
  padding: 6px 14px;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
  text-transform: capitalize;
  color: white;
}

.kyc-badge.approved { background-color: #4CAF50; }

.kyc-badge.rejected { background-color: #F44336; }

.success-text { color: green; font-weight: 500; margin-bottom: 15px; }
.error-text { color: red; font-weight: 500; margin-bottom: 15px; }
.info-text { color: #333; font-weight: 500; margin-bottom: 15px; }

.kyc-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-bottom: 30px;
}

.kyc-form label {
  font-weight: 600;
}

.kyc-form input[type="file"] {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.kyc-form button {
  margin-top: 10px;
  padding: 12px;
  border: none;
  border-radius: 6px;
  background-color: #1e88e5;
  color: white;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.kyc-form button:hover {
  background-color: #0d6efd;
}

.status-card {
  display: flex;
  gap: 20px;
  margin-top: 20px;
  background-color: #eef2f7;
  padding: 20px;
  border-radius: 10px;
  align-items: center;
}

.status-icon span {
  font-size: 48px;
  color: #1e88e5;
}

.status-content h3 {
  margin: 0 0 10px 0;
}

.note {
  font-style: italic;
  color: #666;
}

  </style>
</head>
<body>
  <!-- Sidebar -->
  <?php include('../sidebar.php'); ?>
  

  <!-- Main Content -->
 <main class="main-content">
  <div class="kyc-container">
    <div class="kyc-header">
      <h1>
        <span class="material-icons-round">verified_user</span>
        KYC Verification
      </h1>
      <span class="kyc-badge <?= $kycStatus ?>">
        <?= ucfirst($kycStatus) ?>
      </span>
    </div>

    <?php if (!empty($rejectionReason)): ?>
      <p class="error-text"><strong>Rejection Reason:</strong> <?= htmlspecialchars($rejectionReason) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <p class="success-text">KYC documents submitted successfully!</p>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <p class="error-text">Error: <?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <?php if ($kycStatus === 'approved'): ?>
      <div class="info-text">Your KYC is approved. No further action is needed.</div>
    <?php else: ?>
      <form action="kyc.php" method="POST" enctype="multipart/form-data" class="kyc-form">
        <label>PAN Card:</label>
        <input type="file" name="pan_file" required>

        <label>Aadhaar Card:</label>
        <input type="file" name="aadhaar_file" required>

        <label>Bank Statement:</label>
        <input type="file" name="bank_file" required>

        <button type="submit">Submit KYC</button>
      </form>
    <?php endif; ?>

    <div class="status-card">
      <div class="status-icon">
        <span class="material-icons-round">
          <?= $kycStatus === 'approved' ? 'verified' : ($kycStatus === 'rejected' ? 'error' : 'pending_actions') ?>
        </span>
      </div>
      <div class="status-content">
        <h3>
          <?= $kycStatus === 'approved' ? 'KYC Verification Completed' : 
             ($kycStatus === 'rejected' ? 'KYC Verification Rejected' : 'KYC Verification in Progress') ?>
        </h3>
        <p>
          <?php if ($kycStatus === 'approved'): ?>
            Your KYC verification has been successfully completed. You now have full access to all platform features.
          <?php elseif ($kycStatus === 'rejected'): ?>
            Your KYC verification was rejected. Please review the reason and submit new documents.
          <?php else: ?>
            Your documents are being verified by our team. This process usually takes 24-48 hours.
          <?php endif; ?>
        </p>
        <?php if ($kycStatus === 'pending'): ?>
          <p class="note">We’ve integrated with Digio for automated verification. You'll receive an email once completed.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
  <script>
  // Document Upload Preview Functionality
  document.addEventListener('DOMContentLoaded', function() {
    // PAN Card Upload
    document.getElementById('panFile').addEventListener('change', function(e) {
      handleFileUpload(e, 'panPreview', 2);
    });
    
    // Aadhaar Card Upload
    document.getElementById('aadhaarFile').addEventListener('change', function(e) {
      handleFileUpload(e, 'aadhaarPreview', 5);
    });
    
    // Bank Statement Upload
    document.getElementById('bankFile').addEventListener('change', function(e) {
      handleFileUpload(e, 'bankPreview', 5);
    });
  });

  function handleFileUpload(event, previewId, maxSizeMB) {
    const file = event.target.files[0];
    const previewArea = document.getElementById(previewId);
    
    if (!file) return;
    
    // Validate file size
    if (file.size > maxSizeMB * 1024 * 1024) {
      alert(`File size should be less than ${maxSizeMB}MB`);
      event.target.value = ''; // Clear the file input
      return;
    }
    
    // Show preview
    previewArea.style.display = 'block';
    
    if (file.type.match('image.*')) {
      const reader = new FileReader();
      reader.onload = function(e) {
        previewArea.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
      };
      reader.readAsDataURL(file);
    } else if (file.type === 'application/pdf') {
      previewArea.innerHTML = `
        <div class="pdf-preview">
          <span class="material-icons-round">picture_as_pdf</span>
          <p>${file.name}</p>
        </div>
      `;
    } else {
      previewArea.innerHTML = `
        <div class="doc-preview">
          <span class="material-icons-round">description</span>
          <p>${file.name}</p>
        </div>
      `;
    }
  }
  </script>
</body>
</html>