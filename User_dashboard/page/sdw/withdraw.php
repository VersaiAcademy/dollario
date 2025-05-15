<?php
// withdraw.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = $_POST['amount'];
  $account = $_POST['account'];

  // Process logic here...
  echo "<p style='color: green;'>Withdrawal of ₹$amount to account $account initiated.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Withdraw INR</title>
  <link rel="stylesheet" href="usdt.css">
</head>
<body>
  <h2>Withdraw INR</h2>
  <form method="POST">
    <label>Amount (INR):</label>
    <input type="number" name="amount" required><br><br>

    <label>Bank Account No:</label>
    <input type="text" name="account" required><br><br>

    <button type="submit">Withdraw</button>
  </form>
</body>
</html>
