<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$host = '46.202.161.91';
$dbname = 'u973762102_admin';
$username = 'u973762102_dollario';
$password = '876543Kamlesh';

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userId = $_SESSION['user_id'];
$bankId = $_GET['bank_id'] ?? null;

if (!$bankId) {
    echo "Invalid bank account.";
    exit;
}

// Fetch statements
$stmt = $pdo->prepare("SELECT * FROM bank_statements WHERE bank_id = ? AND user_id = ?");
$stmt->execute([$bankId, $userId]);
$statements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bank Statements</title>
</head>
<body>
    <h2>Bank Statements</h2>

    <?php if (count($statements) > 0): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Type</th>
            </tr>
            <?php foreach ($statements as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['amount']) ?></td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No statements found for this account.</p>
    <?php endif; ?>
</body>
</html>
