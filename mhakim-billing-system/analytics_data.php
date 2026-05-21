<?php
require_once "auth.php";
requireLogin();
require_once "config/database.php";

header("Content-Type: application/json");

$revenue = $pdo->query("
    SELECT DATE(created_at) AS day, SUM(amount) AS total
    FROM payments
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) DESC
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);

$revenue = array_reverse($revenue);

$clientStatus = $pdo->query("
    SELECT status, COUNT(*) AS total
    FROM clients
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

$packages = $pdo->query("
    SELECT packages.name, COUNT(clients.id) AS total
    FROM clients
    LEFT JOIN packages ON clients.package_id = packages.id
    GROUP BY packages.id
    ORDER BY total DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

$stats = $pdo->query("SELECT * FROM system_stats WHERE id=1")->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "revenue" => $revenue,
    "client_status" => $clientStatus,
    "packages" => $packages,
    "stats" => $stats
]);
