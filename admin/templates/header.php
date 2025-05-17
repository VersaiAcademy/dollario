<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_name'] = 'Admin User';
$_SESSION['notifications'] = 5;
?>


<style>
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

    .avatar {
        background-color: #ccc;
        border-radius: 50%;
        width: 35px;
        height: 35px;
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
        transition: background 0.2s;
    }

    .dropdown-menu a:hover {
        background-color: #f0f0f0;
    }

    .search-box {
    position: relative;
    width: 300px;
}

.search-box input {
    width: 100%;
    padding: 10px;
    padding-right: 30px; /* Space for the icon */
    box-sizing: border-box;
}

.search-box i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
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



        .dropdown-menu a {
    padding: 8px 16px;
    display: block;
    text-decoration: none;
    color: #333;
    font-size: 16px; /* Adjust text size */
}

.dropdown-menu a i {
    margin-right: 8px; /* Space between the icon and text */
    font-size: 18px; /* Icon size */
}

.dropdown-menu a:hover {
    background-color: #ddd;
}

    
</style>
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>


<div class="header">
<div class="search-box">
    <input type="text" placeholder="Search users, transactions" />
    <i class="fas fa-search"></i> <!-- Search Icon -->
</div>

    <div class="user-section" onclick="toggleDropdown()">
        <div class="notification">
            🔔
            <span class="notification-count"><?= $_SESSION['notifications'] ?></span>
        </div>
        <div class="avatar">
    <i class="fas fa-user"></i> <!-- User Icon inside the avatar div -->
</div>

        <div class="username-dropdown">
            <?= htmlspecialchars($_SESSION['user_name']) ?> ▼
            <div class="dropdown-menu" id="userDropdown">
    <a href="../profile.php"><i class="fas fa-user"></i> My Profile</a> <!-- Profile Icon -->
    <a href="../settings.php"><i class="fas fa-cog"></i> Settings</a> <!-- Settings Icon -->
    <a href="../login.php"><i class="fas fa-sign-out-alt"></i> Logout</a> <!-- Logout Icon -->
</div>

        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        const menu = document.getElementById("userDropdown");
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    }

    // Optional: Close dropdown if clicked outside
    document.addEventListener("click", function(e) {
        const dropdown = document.getElementById("userDropdown");
        const userSection = document.querySelector(".user-section");
        if (!userSection.contains(e.target)) {
            dropdown.style.display = "none";
        }
    });
</script>
