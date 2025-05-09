<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'dollario_admin';
$username = 'root';
$password = '';

try {
    // Create a PDO instance to handle the connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error handling
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $user['id'];
        $hashed_password = $user['password'];
        $status = $user['status'];
    
        // Check if user is active and password matches
        if ($status === 'active' && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            header("Location: ../page/dashboard.php");  // Redirect to User Dashboard
            exit;
        } else {
            echo "Your account is not active or password is incorrect!";
        }
    } else {
        echo "User not found!";
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="style.css">  <!-- If login.php is in the same folder -->
</head>
<body>

    <div class="container">
        <form method="POST" action="login.php">
            <h2>User Login</h2>

            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>

            <p class="link">Don't have an account? <a href="signup.php">Sign Up</a></p>
        </form>
    </div>

</body>

</html>
