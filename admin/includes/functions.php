<?php
// Database connection function
function dbConnect() {
    //$servername = "localhost";
    //$username = "root";
    //$password = "";
    //$dbname = "dollario_admin";  // Aapka database ka naam
    $host = '46.202.161.91';
$dbname = 'u973762102_admin';
$username = 'u973762102_dollario';
$password = '876543Kamlesh';

    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}



?>
