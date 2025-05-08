<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DollaRio Pro - KYC Verification</title>
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
      --error: #ef4444;
      --success: #22c55e;
      --warning: #f59e0b;
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
      padding: 32px;
      margin-left: 280px;
    }

    /* KYC Page Styles */
    .kyc-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 32px;
    }

    .kyc-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .kyc-status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      background: var(--background);
    }

    .kyc-pending {
      color: var(--warning);
      background: rgba(245, 158, 11, 0.1);
    }

    .kyc-approved {
      color: var(--success);
      background: rgba(34, 197, 94, 0.1);
    }

    .kyc-rejected {
      color: var(--error);
      background: rgba(239, 68, 68, 0.1);
    }

    .rejection-reason {
      font-size: 0.9rem;
      font-weight: normal;
    }

    .kyc-steps {
      display: flex;
      justify-content: space-between;
      margin-bottom: 40px;
      position: relative;
    }

    .kyc-steps::before {
      content: '';
      position: absolute;
      top: 20px;
      left: 0;
      right: 0;
      height: 2px;
      background: var(--background);
      z-index: 1;
    }

    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      z-index: 2;
      flex: 1;
    }

    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--background);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      margin-bottom: 8px;
      border: 2px solid var(--background);
    }

    .step.active .step-number {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    .step-content {
      text-align: center;
    }

    .step-content h3 {
      font-size: 1rem;
      margin-bottom: 4px;
    }

    .step-content p {
      font-size: 0.8rem;
      color: var(--text-secondary);
    }

    .document-upload-section {
      background: var(--surface);
      padding: 32px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
      margin-bottom: 24px;
    }

    .upload-card {
      margin-bottom: 24px;
    }

    .upload-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }

    .upload-header h3 {
      font-size: 1.1rem;
      font-weight: 600;
    }

    .upload-status {
      font-size: 0.9rem;
      color: var(--secondary);
      font-weight: 500;
    }

    .upload-area {
      border: 2px dashed var(--background);
      border-radius: 12px;
      padding: 24px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
    }

    .upload-area:hover {
      border-color: var(--primary);
    }

    .upload-button {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .upload-button .material-icons-round {
      font-size: 2.5rem;
      color: var(--primary);
    }

    .upload-hint {
      font-size: 0.8rem;
      color: var(--text-secondary);
      margin-top: 8px;
    }

    .preview-area {
      margin-top: 16px;
      display: none;
    }

    .preview-area img {
      max-width: 100%;
      max-height: 200px;
      border-radius: 8px;
      border: 1px solid var(--background);
    }

    .verification-status {
      background: var(--surface);
      padding: 32px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .status-card {
      display: flex;
      gap: 16px;
    }

    .status-icon .material-icons-round {
      font-size: 2.5rem;
      color: var(--primary);
    }

    .status-content h3 {
      margin-bottom: 8px;
    }

    .status-content p {
      color: var(--text-secondary);
      margin-bottom: 4px;
    }

    .btn {
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
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

    /* Responsive styles */
    @media (max-width: 1024px) {
      .main-content {
        margin-left: 0;
      }
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 24px;
      }
      
      .kyc-steps {
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
      }
      
      .kyc-steps::before {
        display: none;
      }
      
      .step {
        flex-direction: row;
        gap: 16px;
        width: 100%;
      }
      
      .step-content {
        text-align: left;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <?php include('../sidebar.php'); ?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="kyc-page">
      <div class="kyc-header">
        <h1 class="kyc-title">
          <span class="material-icons-round">verified_user</span>
          KYC Verification
        </h1>
        <div class="kyc-status-badge kyc-<?= $kycStatus ?>">
          <?= ucfirst($kycStatus) ?>
          <?php if ($kycStatus === 'rejected' && $rejectionReason): ?>
            <span class="rejection-reason">(Reason: <?= htmlspecialchars($rejectionReason) ?>)</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="kyc-steps">
        <div class="step active">
          <div class="step-number">1</div>
          <div class="step-content">
            <h3>Document Upload</h3>
            <p>Upload required documents for verification</p>
          </div>
        </div>
        
        <div class="step <?= $kycStatus !== 'pending' ? 'active' : '' ?>">
          <div class="step-number">2</div>
          <div class="step-content">
            <h3>Verification</h3>
            <p>Documents being verified by our team</p>
          </div>
        </div>
        
        <div class="step <?= $kycStatus === 'approved' ? 'active' : '' ?>">
          <div class="step-number">3</div>
          <div class="step-content">
            <h3>Completion</h3>
            <p>KYC process completed</p>
          </div>
        </div>
      </div>

      <?php if ($kycStatus === 'pending' || $kycStatus === 'rejected'): ?>
        <div class="document-upload-section">
          <h2>Upload Documents</h2>
          
          <form id="kycForm" enctype="multipart/form-data" action="process_kyc.php" method="POST">
            <!-- PAN Card Upload -->
            <div class="upload-card">
              <div class="upload-header">
                <h3>PAN Card</h3>
                <span class="upload-status">Required</span>
              </div>
              <div class="upload-area" id="panUploadArea">
                <input type="file" id="panFile" name="pan_file" accept="image/*,.pdf" required style="display: none;">
                <label for="panFile" class="upload-button">
                  <span class="material-icons-round">upload</span>
                  <span>Click to upload</span>
                </label>
                <p class="upload-hint">JPEG, PNG or PDF (Max 2MB)</p>
              </div>
              <div class="preview-area" id="panPreview"></div>
            </div>
            
            <!-- Aadhaar Card Upload -->
            <div class="upload-card">
              <div class="upload-header">
                <h3>Aadhaar Card (Front & Back)</h3>
                <span class="upload-status">Required</span>
              </div>
              <div class="upload-area" id="aadhaarUploadArea">
                <input type="file" id="aadhaarFile" name="aadhaar_file" accept="image/*,.pdf" required style="display: none;">
                <label for="aadhaarFile" class="upload-button">
                  <span class="material-icons-round">upload</span>
                  <span>Click to upload</span>
                </label>
                <p class="upload-hint">JPEG, PNG or PDF (Max 5MB)</p>
              </div>
              <div class="preview-area" id="aadhaarPreview"></div>
            </div>
            
            <!-- Bank Statement Upload -->
            <div class="upload-card">
              <div class="upload-header">
                <h3>Bank Statement</h3>
                <span class="upload-status">Required</span>
              </div>
              <div class="upload-area" id="bankUploadArea">
                <input type="file" id="bankFile" name="bank_file" accept="image/*,.pdf,.doc,.docx" required style="display: none;">
                <label for="bankFile" class="upload-button">
                  <span class="material-icons-round">upload</span>
                  <span>Click to upload</span>
                </label>
                <p class="upload-hint">PDF, DOC or JPEG (Max 5MB)</p>
              </div>
              <div class="preview-area" id="bankPreview"></div>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Submit for Verification</button>
            </div>
          </form>
        </div>
      <?php endif; ?>
      
      <div class="verification-status">
        <h2>Verification Status</h2>
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
              <p>We've integrated with Digio for automated verification. You'll receive an email once completed.</p>
            <?php endif; ?>
          </div>
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