<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please login.");
}

require_once 'includes/db.php';  // Make sure this connects to your DB correctly

$user_id = $_SESSION['user_id'];
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    die("All fields are required.");
}

// Handle profile picture upload
$profile_pic_path = $_SESSION['profile_picture'] ?? '';
if (!empty($_FILES['profile_picture']['name'])) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $unique_filename = time() . '_' . basename($_FILES['profile_picture']['name']);
    $target_file = $upload_dir . $unique_filename;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        $profile_pic_path = $target_file;
    } else {
        die("Error uploading profile picture.");
    }
}

// Update the user's data in the database
$stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, profile_pic=? WHERE id=?");
$stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $profile_pic_path, $user_id);

if ($stmt->execute()) {
    // Update session variables
    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['profile_picture'] = $profile_pic_path;

    echo "Profile updated successfully.";
} else {
    echo "Error updating profile.";
}

$stmt->close();
$conn->close();
?>
