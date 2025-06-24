<?php include('../sidebar.php'); ?>
<?php include('submit_help.php'); ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User not logged in.");
}

// DB connection
$host = '46.202.161.91';
$dbname = 'u973762102_admin';
$username = 'u973762102_dollario';
$password = '876543Kamlesh';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch referral code
$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($referral_code);
$stmt->fetch();
$stmt->close();

// Fetch referral earnings
$result = $conn->query("SELECT SUM(amount) as total_earnings FROM referral_bonus WHERE referred_by = $user_id");
$earnings = $result->fetch_assoc()['total_earnings'] ?? 0.00;

// Fetch downline users for Level 1
$level1 = [];
$sql1 = "SELECT * FROM users WHERE referred_by = $user_id";
$res1 = $conn->query($sql1);
if ($res1) {
    while ($row1 = $res1->fetch_assoc()) {
        $level1[] = $row1;
    }
} else {
    die("Error fetching Level 1 referrals: " . $conn->error);
}

// Fetch Level 2 and 3
$level2 = $level3 = [];

foreach ($level1 as $l1) {
    $res2 = $conn->query("SELECT * FROM users WHERE referred_by = " . $l1['id']);
    if ($res2) {
        while ($row2 = $res2->fetch_assoc()) {
            $level2[] = $row2;
            $res3 = $conn->query("SELECT * FROM users WHERE referred_by = " . $row2['id']);
            if ($res3) {
                while ($row3 = $res3->fetch_assoc()) {
                    $level3[] = $row3;
                }
            }
        }
    }
}

// Fetch bonus history
$bonus_query = $conn->query("SELECT * FROM referral_bonus WHERE referred_by = $user_id ORDER BY created_at DESC");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
     
        .container {
          margin-left: 260px;
           
          
        }
        h2 {
            font-size: 2rem;
            color: #444;
            text-align: center;
            margin-bottom: 40px;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f4f7fc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .card-header h3 {
            font-size: 1.5rem;
            color: #2a2a2a;
        }
        .card-header .referral-link {
            font-size: 1rem;
            color: #007bff;
            cursor: pointer;
            text-decoration: none;
        }
        .referral-code {
            font-size: 1.2rem;
            font-weight: bold;
            color: #444;
            background-color: #f1f4f9;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 10px;
        }
        .stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .stat h4 {
            font-size: 1.1rem;
            color: #888;
        }
        .stat .value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        .accordion {
            background-color: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .accordion-header {
            padding: 15px;
            background-color: #f7f9fc;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: bold;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .accordion-body {
            padding: 10px;
            display: none;
            border-top: 1px solid #ddd;
        }
        .accordion-body table {
            width: 100%;
            border-collapse: collapse;
        }
        .accordion-body table th, .accordion-body table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .accordion-body table th {
            background-color: #f1f4f9;
        }
        .copy-btn {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .copy-btn:hover {
            background-color: #0056b3;
        }
        .btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
  header {
    display: flex;
  }
  .container{
    margin-left: 0px;
  }
}

/* Default: hide header on desktop */
header {
  display: none;
  background-color: #0e1a2b;
  padding: 10px 20px;
  align-items: center;
  justify-content: space-between;
  color: white;
}

/* Show header only on phones/tablets */
@media (max-width: 768px) {
  header {
    display: flex;
  }
  .container{
    margin-left: 0px;
  }
   .sidebar {
    display: none;
  }
}

.logo-container img.logo {
  width: 150px;
  height: auto;
}

.menu-container .menu-btn {
  font-size: 28px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
}
    </style>
</head>
<body>

<div class="container">
     <header>
  <div class="logo-container">
    <img src="../image/dollario-logo.png" alt="Logo" class="logo" style="width: 200px;">
  </div>
  <div class="menu-container">
    <button class="menu-btn">☰</button>
  </div>
</header>

    

    <!-- Referral Code Section -->
    <div class="card">
        <!--<h2>Your Referral Dashboard</h2>-->
        <div class="card-header">
            <h3>Referral Code</h3>
            <a href="#" class="referral-link" onclick="copyReferralLink()">Copy Referral Link</a>
        </div>
        <p>Your unique referral code is:</p>
        <div class="referral-code">
            <?= htmlspecialchars($referral_code) ?>
        </div>
    </div>

    <!-- Referral Earnings -->
    <div class="card">
        <div class="stat">
            <h4>Total Referral Earnings</h4>
            <div class="value">₹<?= number_format($earnings, 2) ?></div>
        </div>
        <div class="stat">
            <h4>Referral Link</h4>
            <div class="value">
                <a href="https://yourdomain.com/register.php?ref=<?= htmlspecialchars($referral_code) ?>" class="copy-btn">Copy Link</a>
            </div>
        </div>
    </div>

    <!-- Downline Users -->
    <div class="accordion">
        <div class="accordion-header">Level 1 Referrals</div>
        <div class="accordion-body">
            <?php if (!empty($level1)): ?>
                <table>
                    <tr><th>Name</th><th>Email</th><th>Joined</th></tr>
                    <?php foreach ($level1 as $u): ?>
                        <tr><td><?= htmlspecialchars($u['name']) ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><?= $u['created_at'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No Level 1 referrals.</p>
            <?php endif; ?>
        </div>

        <div class="accordion-header">Level 2 Referrals</div>
        <div class="accordion-body">
            <?php if (!empty($level2)): ?>
                <table>
                    <tr><th>Name</th><th>Email</th><th>Joined</th></tr>
                    <?php foreach ($level2 as $u): ?>
                        <tr><td><?= htmlspecialchars($u['name']) ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><?= $u['created_at'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No Level 2 referrals.</p>
            <?php endif; ?>
        </div>

        <div class="accordion-header">Level 3 Referrals</div>
        <div class="accordion-body">
            <?php if (!empty($level3)): ?>
                <table>
                    <tr><th>Name</th><th>Email</th><th>Joined</th></tr>
                    <?php foreach ($level3 as $u): ?>
                        <tr><td><?= htmlspecialchars($u['name']) ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><?= $u['created_at'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No Level 3 referrals.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bonus History -->
    <div class="card">
        <h3>Bonus History</h3>
        <?php if ($bonus_query && $bonus_query->num_rows > 0): ?>
            <table>
                <tr><th>Amount</th><th>Description</th><th>Date</th></tr>
                <?php while ($bonus = $bonus_query->fetch_assoc()): ?>
                    <tr>
                        <td>₹<?= number_format($bonus['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($bonus['description']) ?></td>
                        <td><?= $bonus['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No bonus history available.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    function copyReferralLink() {
        const referralCode = document.querySelector('.referral-code').textContent;
        const referralLink = "https://yourdomain.com/register.php?ref=" + referralCode;
        navigator.clipboard.writeText(referralLink)
            .then(() => alert("Referral link copied to clipboard!"))
            .catch(err => alert("Failed to copy referral link: " + err));
    }

    // Accordion functionality
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const body = this.nextElementSibling;
            body.style.display = (body.style.display === "none" || body.style.display === "") ? "block" : "none";
        });
    });
</script>

</body>
</html>


