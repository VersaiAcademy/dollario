<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // If user is already logged in, redirect to dashboard
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="styles.css"> <!-- Optional CSS file for styling -->
</head>
<body>

<h2>User Login</h2>
<form method="POST" action="login.php">
    <label for="email">Email</label>
    <input type="email" name="email" required>

    <label for="password">Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="signup.php">Sign Up</a></p>


</body>
</html>
