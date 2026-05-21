<?php
header("Content-Type: application/json");

require_once "/var/www/html/mhakim-billing-system/config/database.php";

$stats = $pdo->query("
    SELECT online_hotspot, online_pppoe, router_status, updated_at
    FROM system_stats
    WHERE id=1
")->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "router_status" => $stats["router_status"] ?? "unknown",
    "online_hotspot" => (int)($stats["online_hotspot"] ?? 0),
    "online_pppoe" => (int)($stats["online_pppoe"] ?? 0),
    "updated_at" => $stats["updated_at"] ?? null
]);
