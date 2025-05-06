<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

// Fetch user data from session (you can fetch this from the database as well)
$user_name = $_SESSION['user_name'] ?? 'Guest';
$email = $_SESSION['email'] ?? 'example@example.com';
$phone = $_SESSION['phone'] ?? '1234567890';
$profile_picture = $_SESSION['profile_picture'] ?? '';

// If profile data is in the database, you can also fetch it here.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .profile-container { max-width: 500px; margin: auto; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        input, button { width: 100%; padding: 10px; margin: 8px 0; }
        img { max-width: 150px; border-radius: 50%; }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>👤 My Profile</h2>
    
    <!-- Display user profile information -->
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" required><br>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" required><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br>

    <label for="phone">Phone:</label>
    <input type="text" id="phone" name="phone" required><br>

    <label for="profile_picture">Profile Picture:</label>
    <input type="file" id="profile_picture" name="profile_picture"><br>

    <button type="submit" name="update_profile">Update Profile</button>
</form>

    <hr>

    <h3>Change Password</h3>
    <form method="post" action="change_password.php">
        <input type="password" name="old_password" placeholder="Old Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit">Change Password</button>
    </form>
</div>

</body>
</html>
