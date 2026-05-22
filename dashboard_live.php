<?php
header("Content-Type: application/json");
require_once "config/database.php";

$stats = $pdo->query("SELECT * FROM system_stats WHERE id=1")->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "router_status" => $stats["router_status"] ?? "offline",
    "online_hotspot" => (int)($stats["online_hotspot"] ?? 0),
    "online_pppoe" => (int)($stats["online_pppoe"] ?? 0),
    "wan_rx" => $stats["wan_rx"] ?? 0,
    "wan_tx" => $stats["wan_tx"] ?? 0,
    "updated_at" => $stats["updated_at"] ?? date("Y-m-d H:i:s"),

    "active_clients" => (int)$pdo->query("SELECT COUNT(*) FROM clients WHERE status='active'")->fetchColumn(),
    "expired_clients" => (int)$pdo->query("SELECT COUNT(*) FROM clients WHERE status='expired'")->fetchColumn(),
    "today_revenue" => (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at)=CURDATE()")->fetchColumn(),
    "total_revenue" => (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn()
]);
