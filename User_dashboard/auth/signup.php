<?php
session_start();
if (isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'user') {
    header("Location: ../dashboard.php");
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'dollario_admin';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered!";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user with pending status
            $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'user', 'pending')");
            
            if ($insertStmt->execute([$username, $email, $hashedPassword])) {
                // Success - redirect to login with message
                header("Location: login.php?signup=success&pending=1");
                exit;
            } else {
                $error = "Failed to register user. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="container">
    <form method="POST" action="signup.php">
        <h2>Create Account</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <label for="username">Username</label>
        <input type="text" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
        
        <label for="email">Email</label>
        <input type="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        
        <label for="password">Password</label>
        <input type="password" name="password" required>
        
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" required>
        
        <button type="submit">Sign Up</button>
        
        <p class="link">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

</body>
</html>
