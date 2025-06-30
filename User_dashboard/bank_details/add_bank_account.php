<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=u973762102_admin", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Replace with session
$userId = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];

    $stmt = $pdo->prepare("INSERT INTO bank_accounts (user_id, bank_name, account_number) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $bank_name, $account_number]);

    header("Location: ../page/profile.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  
</head>
<body>
    <form method="POST">
  <label>Bank Name:</label>
  <input type="text" name="bank_name" required><br><br>

  <label>Account Number:</label>
  <input type="text" name="account_number" required><br><br>

  <label>IFC CODE:</label>
  <input type="text" name="ifc_code" required><br><br>

  <button type="submit">Add Account</button>
</form>
</body>
</html>

