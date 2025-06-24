<?php
session_start();
include 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = dbConnect();

    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];

            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];

            $historyStmt = $conn->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $historyStmt->bind_param("iss", $user['id'], $ip_address, $user_agent);
            $historyStmt->execute();
            $historyStmt->close();

            header('Location: modules/dashboard.php');
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #fff;
        }

        .left-section {
            width: 50%;
            background: #000000;
            color: #D4AF37;
            padding: 60px;
            display: flex;
            flex-direction: column;
            position: relative;
            clip-path: ellipse(100% 100% at 0% 50%);
            z-index: 1;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: url('your-background.jpg') no-repeat center center / cover;
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

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            background-color: #f8f9fa;
        }

        .form-group input:focus {
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
            background-color: #D4AF37;
            color: white;
        }

        .btn-primary:hover {
            background-color: #D4AF37;
            transform: translateY(-2px);
        }

        .message {
            color: red;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
            color: #6c757d;
        }

        .register-link a {
            color: #36465D;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 1024px) {
            body {
                flex-direction: column;
            }

            .left-section {
                width: 100%;
                clip-path: none;
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
        }
    </style>
</head>
<body>

<div class="left-section">
    <img src="uploads/Dollario-logo .svg" alt="Admin Logo" class="logo">
    <div class="left-content">
        <h1 class="left-title">Welcome Back Admin</h1>
        <p class="left-subtitle">Sign in to manage your dashboard securely.</p>
    </div>
</div>

<div class="right-section">
    <h2 class="form-title">Admin Login</h2>
    <p class="form-subtitle">Access your admin panel</p>

    <?php if ($error): ?>
        <div class="message"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <input type="email" name="email" placeholder="Enter Email" required />
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Enter Password" required />
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
</div>

</body>
</html>
