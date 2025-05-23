
<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Guest';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$profile_picture = $_SESSION['profile_picture'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            flex: 1 1 300px;
            min-width: 300px;
        }

        .card h2 {
            margin-top: 0;
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
        }

        .profile-pic {
            text-align: center;
        }

        .profile-pic img,
        .profile-pic .icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #ddd;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #555;
        }

        .profile-details {
            text-align: center;
            margin-top: 15px;
        }

        .profile-details p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }

        form label {
            font-weight: 600;
            margin-top: 12px;
            display: block;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form input[type="file"],
        form button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            font-size: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        form button {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border: none;
            margin-top: 15px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media(max-width: 768px) {
            .container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Profile Info -->
    <div class="card">
        <h2>👤 My Profile</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="profile-pic">
            <?php if (!empty($profile_picture)): ?>
                <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture">
            <?php else: ?>
                <div class="icon"><i class="fas fa-user-circle"></i></div>
            <?php endif; ?>
        </div>

        <div class="profile-details">
            <p><strong>Name:</strong> <?= htmlspecialchars($user_name) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
        </div>
    </div>

    <!-- Update Profile Form -->
    <div class="card">
        <h2>✏️ Update Profile</h2>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user_name) ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>

            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture">

            <button type="submit" name="update_profile">Update</button>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="card">
        <h2>🔒 Change Password</h2>
        <form method="post" action="change_password.php">
            <label for="old_password">Old Password</label>
            <input type="password" name="old_password" id="old_password" required>

            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" required>

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <button type="submit">Change Password</button>
        </form>
    </div>

</div>

</body>
</html>
