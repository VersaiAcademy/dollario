<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['pending_email'])) {
    header("Location: signup.php");
    exit;
}

$email = $_SESSION['pending_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST['otp']);

    $stmt = $pdo->prepare("SELECT otp FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $entered_otp == $user['otp']) {
        $pdo->prepare("UPDATE users SET status = 'active', otp = NULL WHERE email = ?")->execute([$email]);
        unset($_SESSION['pending_email']);
        header("Location: login.php?verified=1");
        exit;
    } else {
        $error = "Incorrect OTP. Please try again.";
    }
}
?>

<!-- Simple OTP Form -->
<form method="POST">
    <h2>Verify OTP</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <button type="submit">Verify</button>
</form>
