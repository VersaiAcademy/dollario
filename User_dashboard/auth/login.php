<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'dollario_admin';
$username = 'admin';
$password = 'Dollario1234567';

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
    <title>Dollario - User Login</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
</head>
<style>
    * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #ffffff;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        .left-section {
            width: 50%;
            background: #08172E;
            color: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            clip-path: ellipse(100% 100% at 0% 50%);
            z-index: 1;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url('../images/backgroundimg.jpg') no-repeat center center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }

        .right-section {
            width: 50%;
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            width: 220px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .left-content {
            margin-top: 180px;
            max-width: 500px;
        }

        .left-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 25px;
            line-height: 1.3;
        }

        .left-subtitle {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .form-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #36465D;
        }

        .form-subtitle {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: #36465D;
            box-shadow: 0 0 0 3px rgba(54, 70, 93, 0.1);
            outline: none;
            background-color: white;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .btn-primary {
            background-color: #36465D;
            color: white;
        }

        .btn-primary:hover {
            background-color: #283444;
            transform: translateY(-2px);
        }

        .login-link,
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
            color: #6c757d;
        }

        .login-link a,
        .register-link a {
            color: #36465D;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover,
        .register-link a:hover {
            text-decoration: underline;
        }

        .forgot-password {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        .forgot-password a {
            font-size: 14px;
            color: #36465D;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .terms {
            margin-top: 30px;
            font-size: 13px;
            text-align: center;
            color: #6c757d;
        }

        .terms a {
            color: #36465D;
            text-decoration: underline;
        }

        @media (max-width: 1024px) {
            body {
                flex-direction: column;
            }

            .left-section {
                width: 100%;
                clip-path: none;
                padding: 40px;
                display: none;
            }

            .right-section {
                width: 100%;
                padding: 60px 40px;
            }
        }

        @media (max-width: 768px) {
            .right-section {
                padding: 50px 30px;
            }

            .form-title {
                font-size: 24px;
            }

            .form-subtitle {
                font-size: 14px;
                margin-bottom: 30px;
            }
        }
</style>
<body>
    <div class="left-section">
        <img src="../image/dollario-logo.png" alt="HubPay Logo" class="logo">
        <div class="left-content">
            <h1 class="left-title">Welcome Back to Dollario</h1>
            <p class="left-subtitle">Log in and continue your crypto journey.</p>
        </div>
    </div>

    <div class="right-section">
        <h2 class="form-title">User Login</h2>
        <p class="form-subtitle">Access your account securely</p>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p class="register-link">Don't have an account? <a href="signup.php">Sign Up</a></p>
        <p class="terms">By signing in, you agree to our <a href="#">Terms and Conditions</a> &amp; <a href="#">Privacy Policy</a>.</p>
    </div>
</body>
</html>