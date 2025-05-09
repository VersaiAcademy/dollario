<?php
session_start();
require_once 'includes/db.php'; // Adjust the path to your DB config file


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone']);
    $userType = trim($_POST['userType']);
    $initialBalance = floatval($_POST['initialBalance']);
    $userStatus = trim($_POST['userStatus']);

    // Optional: Add further validations here

    if ($email && $firstName && $lastName && $phone && $userType && $userStatus !== '') {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, user_type, balance, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss", $firstName, $lastName, $email, $phone, $userType, $initialBalance, $userStatus);

        if ($stmt->execute()) {
            $_SESSION['success'] = "User added successfully.";
        } else {
            $_SESSION['error'] = "Error adding user: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Please fill all required fields correctly.";
    }
    header("Location: dashboard.php"); // Redirect back to dashboard
    exit;
}
?>
