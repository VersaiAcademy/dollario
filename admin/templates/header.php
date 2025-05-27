<?php
if (session_status() === PHP_SESSION_NONE) {
    
}
$_SESSION['user_name'] = 'Admin';
$_SESSION['notifications'] = 5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .header {
            background-color: #fefefb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-left: 260px;
            height: 60px;
            position: relative;
        }

        .search-box input {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            background-color: #d3d3d3;
            color: #888;
            width: 200px;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 17px;
            cursor: pointer;
            position: relative;
        }

        .notification {
            position: relative;
            font-size: 20px;
        }

        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #b58900;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 50%;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            top: 45px;
            right: 60px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 6px;
            min-width: 250px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 999;
        }

        .notification-dropdown p {
            margin: 0;
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .notification-dropdown p:last-child {
            border-bottom: none;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background-color: #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .avatar i {
            color: #333;
        }

        .username-dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 6px;
            overflow: hidden;
            min-width: 160px;
            z-index: 999;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px 16px;
            text-decoration: none;
            color: #333;
            font-size: 16px;
            transition: background 0.2s;
        }

        .dropdown-menu a i {
            margin-right: 8px;
            font-size: 18px;
        }

        .dropdown-menu a:hover {
            background-color: #ddd;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px;
            padding-right: 30px;
            box-sizing: border-box;
        }

        .search-box i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }
        .dropdown-menu.show {
    display: block;
}

    </style>
</head>
<body>
<div class="header">
    <div class="search-box">
        <input type="text" placeholder="Search users, transactions" />
        <i class="fas fa-search"></i>
    </div>

    <div class="user-section">
        <!-- 🔔 Notification Icon -->
        <div class="notification" onclick="toggleNotification()">
            🔔
            <span class="notification-count"><?= $_SESSION['notifications'] ?></span>

            <div class="notification-dropdown" id="notificationDropdown">
                <p>New user registered</p>
                <p>Deposit request received</p>
                <p>Password changed successfully</p>
                <p>Admin logged in</p>
                <p>New support ticket</p>
            </div>
        </div>

        <!-- 👤 User Dropdown -->
        <div class="username-dropdown" onclick="toggleDropdown()">
            <?= htmlspecialchars($_SESSION['user_name']) ?> ▼
            <div class="dropdown-menu" id="userDropdown">
                <a href="../profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="../settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="../login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        document.getElementById("userDropdown").classList.toggle("show");
        document.getElementById("notificationDropdown").style.display = "none";
    }

    function toggleNotification() {
        const dropdown = document.getElementById("notificationDropdown");
        const userDropdown = document.getElementById("userDropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        userDropdown.classList.remove("show");
    }

    document.addEventListener("click", function(e) {
        const notif = document.getElementById("notificationDropdown");
        const user = document.getElementById("userDropdown");
        const notifBtn = document.querySelector(".notification");
        const userBtn = document.querySelector(".username-dropdown");

        if (!notifBtn.contains(e.target)) {
            notif.style.display = "none";
        }
        if (!userBtn.contains(e.target)) {
            user.classList.remove("show");
        }
    });
</script>
</body>
</html>
