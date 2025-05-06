<?php
session_start();
include '../includes/db.php'; // Your DB connection file

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this section.";
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch referral count
$countSql = "SELECT COUNT(*) as total FROM referrals WHERE user_id = ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$totalReferrals = $countResult['total'];

// Fetch referral user data
$sql = "SELECT u.name, u.email, r.referred_at 
        FROM referrals r
        JOIN users u ON r.referred_user_id = u.id
        WHERE r.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Referral Section -->
<div style="padding: 20px; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9; margin: 20px 0;">
  <h2>Referral Program</h2>
  <p>You have referred <strong><?php echo $totalReferrals; ?></strong> user(s).</p>

  <?php if ($totalReferrals > 0): ?>
  <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <thead>
      <tr style="background-color: #007BFF; color: white;">
        <th style="padding: 10px; border: 1px solid #ddd;">Name</th>
        <th style="padding: 10px; border: 1px solid #ddd;">Email</th>
        <th style="padding: 10px; border: 1px solid #ddd;">Referred At</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['name']); ?></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['email']); ?></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['referred_at']); ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p style="color: gray;">You haven't referred any users yet.</p>
  <?php endif; ?>
</div>
