<?php
// referral_system.php

// Check if config.php exists in the same directory
if (file_exists(__DIR__ . '../includes/config.php')) {
    require __DIR__ . '../includes/config.php';
} else {
    die("Configuration file not found. Please create config.php with database credentials.");
}

// Now you can use the database connection
$conn = dbConnect();

// Your referral system code here
// For example:
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET requests
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_referrals':
            $userId = $_GET['user_id'] ?? 0;
            $stmt = $conn->prepare("SELECT * FROM referrals WHERE referrer_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $referrals = $result->fetch_all(MYSQLI_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $referrals]);
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST requests
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_referral':
            $referrerId = $_POST['referrer_id'] ?? 0;
            $refereeEmail = $_POST['referee_email'] ?? '';
            
            // Validate input
            if (empty($refereeEmail)) {

                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Referee email is required']);
                exit;
            }
            
            // Insert new referral
            $stmt = $conn->prepare("INSERT INTO referrals (referrer_id, referee_email, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("is", $referrerId, $refereeEmail);
            
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Referral created successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create referral']);
            }
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

$conn->close();