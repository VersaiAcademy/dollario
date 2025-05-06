<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dollario_admin";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$sql = "SELECT subadmin_id, first_name, last_name, email, role, status, created_at FROM sub_admins ORDER BY created_at DESC";
$result = $conn->query($sql);

$subadmins = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subadmins[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "data" => $subadmins
]);

$conn->close();
?>
