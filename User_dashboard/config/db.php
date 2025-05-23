<?php
$host = 'localhost';
$dbname = 'u973762102_dollario_admin';
$username = 'u973762102_admin';
$password = 'Dollario1234567';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
