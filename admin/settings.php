<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .settings-container { max-width: 500px; margin: auto; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        input, select, button { width: 100%; padding: 10px; margin: 8px 0; }
    </style>
</head>
<body>

<div class="settings-container">
    <h2>⚙️ Account Settings</h2>

    <form method="post" action="save_settings.php">
        <label>Notification Preference</label>
        <select name="notifications">
            <option value="email">Email</option>
            <option value="sms">SMS</option>
            <option value="both">Both</option>
        </select>

        <label>Language</label>
        <select name="language">
            <option value="en">English</option>
            <option value="hi">Hindi</option>
        </select>

        <label>Theme</label>
        <select name="theme">
            <option value="light">Light</option>
            <option value="dark">Dark</option>
        </select>

        <label>Two-Factor Authentication</label>
        <select name="2fa">
            <option value="enabled">Enabled</option>
            <option value="disabled">Disabled</option>
        </select>

        <button type="submit">Save Settings</button>
    </form>
</div>

</body>
</html>
