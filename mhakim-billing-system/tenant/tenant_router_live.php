<?php
require_once __DIR__ . '/../access_guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

header("Content-Type: application/json");

$companyId = $_SESSION["tenant_company_id"];

$stmt = $pdo->prepare("SELECT * FROM companies WHERE id=? LIMIT 1");
$stmt->execute([$companyId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company || !$company["router_ip"] || !$company["router_user"]) {
    echo json_encode([
        "status" => "not_configured",
        "message" => "Router not configured"
    ]);
    exit;
}

try {
    $config = new Config([
        "host" => $company["router_ip"],
        "user" => $company["router_user"],
        "pass" => $company["router_pass"],
        "port" => (int)($company["router_port"] ?: 8728),
        "timeout" => 4,
    ]);

    $client = new Client($config);

    $identity = $client->query("/system/identity/print")->read();
    $resource = $client->query("/system/resource/print")->read();
    $hotspotActive = $client->query("/ip/hotspot/active/print")->read();
    $queues = $client->query("/queue/simple/print")->read();

    echo json_encode([
        "status" => "online",
        "router_name" => $identity[0]["name"] ?? ($company["router_name"] ?? "MikroTik"),
        "uptime" => $resource[0]["uptime"] ?? "-",
        "cpu_load" => $resource[0]["cpu-load"] ?? "0",
        "free_memory" => $resource[0]["free-memory"] ?? "-",
        "board" => $resource[0]["board-name"] ?? "-",
        "active_hotspot_users" => count($hotspotActive),
        "simple_queues" => count($queues),
        "updated_at" => date("Y-m-d H:i:s")
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "offline",
        "message" => $e->getMessage(),
        "updated_at" => date("Y-m-d H:i:s")
    ]);
}
