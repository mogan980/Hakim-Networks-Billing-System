<?php
require_once __DIR__ . "/config/database.php";

$payments = $pdo->query("
    SELECT 
        p.id,
        p.phone,
        p.amount,
        p.method,
        p.status,
        p.checkout_id,
        p.mpesa_receipt,
        p.client_ip,
        p.created_at,
        pk.name AS package_name
    FROM payments p
    LEFT JOIN packages pk ON p.package_id = pk.id
    ORDER BY p.id DESC
    LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "payments" => $payments
]);
