<?php

date_default_timezone_set("Africa/Nairobi");

$host = "localhost";
$dbname = "mhakim_billing";
$username = "hakim";
$password = "1234";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("SET time_zone = '+03:00'");
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
