<?php
// deposit.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = $_POST['amount'];
  $mode = $_POST['mode'];

  // Process logic here...
  echo "<p style='color: green;'>You have deposited ₹$amount via $mode.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Deposit INR</title>
  <link rel="stylesheet" href="usdt.css">
</head>
<body>
  <h2>Deposit INR</h2>
  <form method="POST">
    <label>Amount (INR):</label>
    <input type="number" name="amount" required><br><br>

    <label>Payment Mode:</label>
    <select name="mode">
      <option value="UPI">UPI</option>
      <option value="Bank Transfer">Bank Transfer</option>
    </select><br><br>

    <button type="submit">Deposit</button>
  </form>
</body>
</html>
