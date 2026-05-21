<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";

$checkout = $_GET["checkout"] ?? "";

$stmt = $pdo->prepare("
SELECT status, username, password, mpesa_receipt
FROM hotspot_payments
WHERE checkout_request_id=?
LIMIT 1
");
$stmt->execute([$checkout]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$p){
    echo json_encode(["status"=>"missing"]);
    exit;
}

echo json_encode([
    "status"=>$p["status"],
    "username"=>$p["username"],
    "password"=>$p["password"] ?: $p["username"],
    "receipt"=>$p["mpesa_receipt"]
]);
