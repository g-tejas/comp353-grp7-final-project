<?php

$dsn = "mysql:host=localhost; dbname=comp353_cosn";
$dbusername = "root";
$dbpassword = "AatukalL1";   //CHANGE THIS TO YOUR MYSQL PASSWORD try "" or "root"

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword);  //CONNECT TO THE DATABASE
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}