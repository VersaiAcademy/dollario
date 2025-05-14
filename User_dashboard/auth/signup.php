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
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_otp'])) {
        // OTP verification block
        $entered_otp = $_POST['otp'];
        if ($_SESSION['otp'] == $entered_otp) {
            $username = $_SESSION['username'];
            $email = $_SESSION['email'];
            $password = $_SESSION['password'];
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user with 'pending' status
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'user', 'active')");
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $success = "Registration successful! Your account is now active.";
                session_unset();
            } else {
                $error = "Failed to register user. Please try again.";
            }
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    } else {
        // Signup form submission block
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format!";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters!";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Email is already registered!";
            } else {
                // Store user data in session for OTP verification
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;

                // Generate and send OTP
                $otp = rand(100000, 999999); // 6-digit OTP
                $_SESSION['otp'] = $otp; // Store OTP in session

                // Send OTP via email or SMS
                // You can use an external service like Twilio or Mailgun here to send OTP
                // Example (just a placeholder):
                // sendOTP($email, $otp);

                $success = "OTP sent to your email. Please enter the OTP to complete registration.";
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
    <title>Sign Up - Dollario</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <form method="POST" action="signup.php">
        <h2>Create Account</h2>

        <?php if (!empty($error)): ?>
            <div class="error" style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="success" style="color: green; margin-bottom: 10px;"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (empty($success)): ?>
            <!-- Normal signup fields -->
            <label for="username">Username</label>
            <input type="text" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">

            <label for="email">Email</label>
            <input type="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Sign Up</button>
        <?php else: ?>
            <!-- OTP Verification fields -->
            <label for="otp">Enter OTP</label>
            <input type="text" name="otp" required>

            <button type="submit" name="verify_otp">Verify OTP</button>
        <?php endif; ?>

        <p class="link">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

</body>
</html>
