<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=dollario_admin", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Replace this with session user_id
$userId = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bank_id'])) {
    $bankId = $_POST['bank_id'];

    // Reset all accounts to not primary
    $pdo->prepare("UPDATE bank_accounts SET is_primary = 0 WHERE user_id = ?")->execute([$userId]);

    // Set selected account to primary
    $pdo->prepare("UPDATE bank_accounts SET is_primary = 1 WHERE id = ? AND user_id = ?")->execute([$bankId, $userId]);

    header("Location: profile.php?success=primary_updated");
    exit;
}
?>
