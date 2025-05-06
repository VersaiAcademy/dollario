<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Upload directory
$uploadDir = '../uploads/kyc/' . $user_id . '/';

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Allowed MIME types
$allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];

// File name sanitizer
function sanitizeFileName($filename) {
    return preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($filename));
}

// File type validator
function isValidUpload($file) {
    global $allowedTypes;
    return in_array(mime_content_type($file['tmp_name']), $allowedTypes);
}

// Timestamp for file uniqueness
$timestamp = time();

// Initialize paths
$panPath = '';
$aadhaarPath = '';
$bankPath = '';

// Handle PAN Card
if (isset($_FILES['pan_file']) && $_FILES['pan_file']['error'] === UPLOAD_ERR_OK && isValidUpload($_FILES['pan_file'])) {
    $panFile = $_FILES['pan_file'];
    $sanitizedPanName = sanitizeFileName($panFile['name']);
    $panPath = $uploadDir . 'pan_' . $timestamp . '_' . $sanitizedPanName;
    move_uploaded_file($panFile['tmp_name'], $panPath);
}

// Handle Aadhaar Card
if (isset($_FILES['aadhaar_file']) && $_FILES['aadhaar_file']['error'] === UPLOAD_ERR_OK && isValidUpload($_FILES['aadhaar_file'])) {
    $aadhaarFile = $_FILES['aadhaar_file'];
    $sanitizedAadhaarName = sanitizeFileName($aadhaarFile['name']);
    $aadhaarPath = $uploadDir . 'aadhaar_' . $timestamp . '_' . $sanitizedAadhaarName;
    move_uploaded_file($aadhaarFile['tmp_name'], $aadhaarPath);
}

// Handle Bank Statement
if (isset($_FILES['bank_file']) && $_FILES['bank_file']['error'] === UPLOAD_ERR_OK && isValidUpload($_FILES['bank_file'])) {
    $bankFile = $_FILES['bank_file'];
    $sanitizedBankName = sanitizeFileName($bankFile['name']);
    $bankPath = $uploadDir . 'bank_' . $timestamp . '_' . $sanitizedBankName;
    move_uploaded_file($bankFile['tmp_name'], $bankPath);
}

// Optional: Save paths in the database
/*
include '../config.php'; // contains $pdo (PDO connection)
$stmt = $pdo->prepare("INSERT INTO kyc_documents (user_id, pan_path, aadhaar_path, bank_path) VALUES (?, ?, ?, ?)");
$stmt->execute([$user_id, $panPath, $aadhaarPath, $bankPath]);
*/

// Redirect to verification page
header('Location: verification.php');
exit();
?>
