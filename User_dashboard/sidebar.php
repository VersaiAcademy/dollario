<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dollario Admin Sidebar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f2f5;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      background: #0e1a2b;
      color: white;
      position: fixed;
      padding: 20px 0;
      overflow-y: auto;
    }
     .sidebar.active {
    left: 0;
  }

    .sidebar .logo {
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      padding: 10px 0;
      margin-bottom: 10px;
    }

    .menu {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .menu li.section {
      padding: 10px 20px;
      font-size: 13px;
      text-transform: uppercase;
      font-weight: bold;
      color: #aaa;
      background: #0b1624;
    }

    .menu li a {
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      padding: 12px 20px;
      transition: background 0.3s;
    }

    .menu li a:hover,
    .menu li a.active {
      background: #1d2e49;
    }

    .menu li a .material-icons {
      margin-right: 15px;
      font-size: 20px;
    }

    /* ✅ Hides sidebar below 768px */
    @media (max-width: 768px) {
      .sidebar {
       
      }
    }
    
  </style>
</head>
<body>

  <div class="sidebar"> 
    
    <div class="logo">
      
      <img src="../image/Dollario-logo .svg" alt="" style="height: auto; width: 150px;">
      
    </div>
    <ul class="menu">
      <li class="section">Main</li>
      <li><a href="dashboard.php" class="active"><span class="material-icons">dashboard</span> Dashboard</a></li>
      <li><a href="transactions.php"><span class="material-icons">admin_panel_settings</span> Transactions History</a></li>
      <li><a href="kyc.php"><span class="material-icons">security</span> KYC</a></li>
      <li><a href="trading.php"><span class="material-icons">receipt</span> Trading</a></li>
      <li><a href="profile.php"><span class="material-icons">settings</span> Profile</a></li>
      <li><a href="referral.php"><span class="material-icons">settings</span> Referral</a></li>
      <li><a href="notifications.php"><span class="material-icons">notifications</span> Notifications</a></li>
      <li><a href="security.php"><span class="material-icons">lock</span> Security</a></li>
    </ul>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const menuBtn = document.querySelector('.menu-btn');
    const sidebar = document.querySelector('.sidebar');

    console.log('menuBtn:', menuBtn);
    console.log('sidebar:', sidebar);

    if (menuBtn && sidebar) {
      menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
      });
    } else {
      console.warn('Menu button or sidebar not found');
    }
  });
</script>


</body>
</html>
