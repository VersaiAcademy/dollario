<?php
include '../config/db.php'; // path सही करें अगर ज़रूरत हो

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user['id'];
            $token = bin2hex(random_bytes(16));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // Insert token into password_resets table
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $token, $expiry]);

            $reset_link = "http://localhost/Dollario/User_dashboard/auth/reset_password.php?token=$token";

            // Show message with reset link (In real app: send via email)
            $message = "Reset link sent to your email.<br><a href='$reset_link'>Click here to reset</a>";
        } else {
            $message = "No user found with this email.";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        /* आपके स्टाइल यहाँ */
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            font-weight: bold;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'Reset link sent') !== false) ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" required placeholder="Enter your email">
        <button type="submit">Send Reset Link</button>
    </form>
</div>
</body>
</html>
