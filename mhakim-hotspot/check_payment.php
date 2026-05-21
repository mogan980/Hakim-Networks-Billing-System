<?php
header("Content-Type: application/json");
require_once "/var/www/html/mhakim-billing-system/config/database.php";

$checkout = $_GET["checkout"] ?? $_GET["checkout_id"] ?? "";

if (!$checkout) {
    echo json_encode(["status"=>"missing_checkout"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM payments
    WHERE checkout_id = ? OR reference = ?
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([$checkout, $checkout]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    echo json_encode(["status"=>"not_found"]);
    exit;
}

$status = strtolower(trim($p["status"] ?? "pending"));

if (in_array($status, ["paid","success","complete","completed","activated"])) {
    echo json_encode(["status"=>"paid","checkout"=>$checkout]);
    exit;
}

if (in_array($status, ["failed","cancelled","canceled"])) {
    echo json_encode(["status"=>"failed"]);
    exit;
}

echo json_encode(["status"=>"pending","db_status"=>$status]);
