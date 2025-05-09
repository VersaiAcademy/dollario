<?php
// Database connection function
function dbConnect() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dollario_admin";  // Aapka database ka naam

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}



?>
