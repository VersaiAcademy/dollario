<?php
session_start(); // ✅ Required to access $_SESSION

// Database connection
$host = 'localhost';
$dbname = 'u973762102_dollario_admin';
$username = 'u973762102_admin';
$password = 'Dollario1234567';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in.");
    }

    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Fetch existing password hash from database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($old_password, $user['password'])) {
        // Update new password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$new_password, $user_id])) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "incorrect";
    }
}
?>
