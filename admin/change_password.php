<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;

    if ($user_id && $old && $new && $new === $confirm) {
        $sql = "SELECT password FROM users WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($old, $hashed)) {
            $new_hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $update->bind_param("si", $new_hashed, $user_id);
            $update->execute();

            header("Location: profile.php?password_changed=1");
            exit;
        } else {
            echo "Old password is incorrect.";
        }
    } else {
        echo "Please fill all fields and confirm new password correctly.";
    }
}
?>
