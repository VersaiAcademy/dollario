<?php
session_start();
include 'includes/functions.php';

// Define the escape function if not already in functions.php
function escape($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

// Initialize variables to avoid undefined warnings
$first_name = '';
$last_name = '';
$phone = '';
$country = '';
$email = '';
$username = '';
$password = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = escape($_POST['first_name'] ?? '');
    $last_name = escape($_POST['last_name'] ?? '');
    $phone = escape($_POST['phone'] ?? '');
    $country = escape($_POST['country'] ?? '');
    $email = escape($_POST['email'] ?? '');
    $username = escape($_POST['username'] ?? '');
    $password = escape($_POST['password'] ?? '');
    $confirm_password = escape($_POST['confirm_password'] ?? '');

    // Example validation
    if ($password !== $confirm_password) {
        echo "<p style='color:red;'>Passwords do not match!</p>";
    } else {
        // Continue registration logic...
    }
}

    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $conn = dbConnect();

        $sql = "SELECT * FROM admin_users WHERE username = '$username' OR email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            $sql = "INSERT INTO admin_users (first_name, last_name, phone, country, email, username, password) 
                    VALUES ('$first_name', '$last_name', '$phone', '$country', '$email', '$username', '$hashed_password')";
            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful. Redirecting to login...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Error: " . $conn->error;
            }
        }

        $conn->close();
    } else {
        $error = "Passwords do not match!";
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="password"], input[type="email"], input[type="tel"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-top: 15px;
            color: red;
        }
        .success {
            color: green;
        }
        .link {
            text-align: center;
            margin-top: 10px;
        }
        .link a {
            text-decoration: none;
            color: #007BFF;
        }
        .link a:hover {
            text-decoration: underline;
        }
        select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

    </style>
</head>
<body>

<div class="container">
    <h2>Admin Registration</h2>
    <?php if ($error): ?>
        <div class="message"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="message success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="">
    <div class="form-group">
        <input type="text" name="first_name" placeholder="First Name" required />
    </div>
    <div class="form-group">
        <input type="text" name="last_name" placeholder="Last Name" required />
    </div>
    <div class="form-group">
        <input type="tel" name="phone" placeholder="Phone Number" required />
    </div>
    <div class="form-group">
        <select name="country" required>
            <option value="">Select Country</option>
            <option value="India">India</option>
            <option value="United States">United States</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="Australia">Australia</option>
            <option value="Canada">Canada</option>
        </select>
    </div>
    <div class="form-group">
        <input type="email" name="email" placeholder="Email Address" required />
    </div>
    <div class="form-group">
        <input type="text" name="username" placeholder="Username" required />
    </div>
    <div class="form-group">
        <input type="password" name="password" placeholder="Password" required />
    </div>
    <div class="form-group">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    </div>
    <button type="submit">Register</button>
</form>


    <div class="link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>
