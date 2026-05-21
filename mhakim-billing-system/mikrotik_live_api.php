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
        throw new Exception("No active router selected.");
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
    $hotspotActive = $client->query(new Query("/ip/hotspot/active/print"))->read();
    $hotspotUsers = $client->query(new Query("/ip/hotspot/user/print"))->read();
    $pppoeActive = $client->query(new Query("/ppp/active/print"))->read();
    $queues = $client->query(new Query("/queue/simple/print"))->read();
    $dhcpLeases = $client->query(new Query("/ip/dhcp-server/lease/print"))->read();

    echo json_encode([
        "ok" => true,
        "status" => "online",
        "router" => [
            "id" => $router["id"] ?? null,
            "name" => $router["router_name"] ?? "MikroTik",
            "ip" => $router["router_ip"],
            "identity" => $identity["name"] ?? "MikroTik",
            "version" => $resource["version"] ?? "-",
            "uptime" => $resource["uptime"] ?? "-",
            "cpu" => $resource["cpu-load"] ?? 0,
            "memory_free" => $resource["free-memory"] ?? 0,
        ],
        "counts" => [
            "hotspot_online" => count($hotspotActive),
            "hotspot_users" => count($hotspotUsers),
            "pppoe_online" => count($pppoeActive),
            "queues" => count($queues),
            "dhcp_clients" => count($dhcpLeases),
        ],
        "hotspot_active" => array_slice($hotspotActive, 0, 30),
        "pppoe_active" => array_slice($pppoeActive, 0, 30),
        "queues" => array_slice($queues, 0, 30),
        "dhcp_leases" => array_slice($dhcpLeases, 0, 30),
        "updated_at" => date("Y-m-d H:i:s")
    ]);

} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "status" => "offline",
        "error" => $e->getMessage(),
        "updated_at" => date("Y-m-d H:i:s")
    ]);
}
