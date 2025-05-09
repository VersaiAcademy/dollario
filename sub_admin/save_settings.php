<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notifications = $_POST['notifications'] ?? 'email';
    $language = $_POST['language'] ?? 'en';
    $theme = $_POST['theme'] ?? 'light';
    $twofa = $_POST['2fa'] ?? 'disabled';

    $user_id = $_SESSION['user_id'] ?? 0;

    if ($user_id) {
        $sql = "UPDATE users SET notification_pref=?, language=?, theme=?, twofa=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $notifications, $language, $theme, $twofa, $user_id);

        if ($stmt->execute()) {
            header("Location: settings.php?saved=1");
            exit;
        } else {
            echo "Failed to save settings.";
        }
    } else {
        echo "User not logged in.";
    }
}
?>
