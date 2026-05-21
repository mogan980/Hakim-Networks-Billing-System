<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

try {
    $router = $pdo->query("
        SELECT * FROM routers 
        WHERE status='active' 
        ORDER BY id DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$router) {
        $router = $pdo->query("
            SELECT 
            router_ip,
            router_username,
            router_password,
            api_port
            FROM mikrotik_settings 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
    }

    if (!$router) {
        throw new Exception("No active router found");
    }

    $client = new Client(new Config([
        "host" => $router["router_ip"],
        "user" => $router["router_username"],
        "pass" => $router["router_password"],
        "port" => (int)$router["api_port"],
        "timeout" => 5
    ]));

    $identity = $client->query(new Query("/system/identity/print"))->read()[0] ?? [];
    $resource = $client->query(new Query("/system/resource/print"))->read()[0] ?? [];
    $hotspot = $client->query(new Query("/ip/hotspot/active/print"))->read();
    $pppoe = $client->query(new Query("/ppp/active/print"))->read();
    $queues = $client->query(new Query("/queue/simple/print"))->read();

    echo json_encode([
        "status" => "online",
        "identity" => $identity["name"] ?? "MikroTik",
        "cpu" => $resource["cpu-load"] ?? 0,
        "memory_free" => $resource["free-memory"] ?? "-",
        "version" => $resource["version"] ?? "-",
        "uptime" => $resource["uptime"] ?? "-",
        "hotspot_online" => count($hotspot),
        "pppoe_online" => count($pppoe),
        "queues" => count($queues),
        "router_ip" => $router["router_ip"]
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "offline",
        "error" => $e->getMessage()
    ]);
}
