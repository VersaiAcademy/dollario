<?php
header("Content-Type: application/json");

// JSON se input le rahe hain
$data = json_decode(file_get_contents("php://input"), true);

// Null check to avoid warnings
$first_name = isset($data['first_name']) ? $data['first_name'] : null;
$last_name = isset($data['last_name']) ? $data['last_name'] : null;
$email = isset($data['email']) ? $data['email'] : null;
$role = isset($data['role']) ? $data['role'] : null;
$status = isset($data['status']) ? $data['status'] : 'Active';
$phone = isset($data['phone']) ? $data['phone'] : null;  // Ensure phone is passed if needed

// Random ID generate kar lete hain (demo purpose)
$subadmin_id = 'ADM' . rand(1000, 9999);

// Database connection
$servername = "localhost";
$username = "root"; // Use your database username
$password = ""; // Use your database password
$dbname = "dollario_admin"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert query
$sql = "INSERT INTO sub_admins (subadmin_id, first_name, last_name, email, role, status, phone) 
        VALUES ('$subadmin_id', '$first_name', '$last_name', '$email', '$role', '$status', '$phone')";

// Check if query executed successfully
if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "success" => true,
        "subadmin_id" => $subadmin_id,
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "role" => $role,
        "status" => $status,
        "message" => "Sub-admin added successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $conn->error
    ]);
}

$conn->close();
?>
