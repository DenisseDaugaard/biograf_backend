<?php
$host = 'localhost';
$db = "biograf-backend"; 
$user = 'root';
$password = 'root';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    die('🫣 The conection failded: ' . $error->getMessage());
}
?>
