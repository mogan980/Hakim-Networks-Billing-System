<?php
require_once __DIR__ . "/config/database.php";
header("Content-Type: application/json");

$rows = $pdo->query("
    SELECT username, ip_address, rx_rate, tx_rate, total_rx, total_tx, updated_at
    FROM realtime_bandwidth
    ORDER BY total_tx DESC, total_rx DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$total = $pdo->query("
    SELECT 
        COALESCE(SUM(total_rx),0) AS total_rx,
        COALESCE(SUM(total_tx),0) AS total_tx,
        COUNT(*) AS users
    FROM realtime_bandwidth
")->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "rows" => $rows,
    "total" => $total
]);
