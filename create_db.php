<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE DATABASE IF NOT EXISTS capstone_project";
    $pdo->exec($sql);
    
    echo "Database created successfully";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit(1);
}
?>
