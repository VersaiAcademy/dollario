<?php
// sell.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = $_POST['amount'];
  $crypto = $_POST['crypto'];

  // Process logic here...
  echo "<p style='color: green;'>You have requested to sell $amount $crypto.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Sell Crypto</title>
  <link rel="stylesheet" href="usdt.css">
</head>
<body>
  <h2>Sell Crypto</h2>
  <form method="POST">
    <label>Amount:</label>
    <input type="number" name="amount" required><br><br>

    <label>Cryptocurrency:</label>
    <select name="crypto">
      <option value="USDT">USDT</option>
      <option value="BTC">BTC</option>
    </select><br><br>

    <button type="submit">Sell</button>
  </form>
</body>
</html>
