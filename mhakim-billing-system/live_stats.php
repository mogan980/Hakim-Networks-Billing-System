<?php
require_once "config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

header("Content-Type: application/json");

try {

    $settings = $pdo->query("
        SELECT * FROM mikrotik_settings
        ORDER BY id DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        throw new Exception("MikroTik settings missing");
    }

    $client = new Client(new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
    ]));

    // Hotspot users
    $hotspotActive = $client->query(
        new Query("/ip/hotspot/active/print")
    )->read();

    // PPPoE users
    $pppoeActive = $client->query(
        new Query("/ppp/active/print")
    )->read();

    // Interface traffic
    $interfaces = $client->query(
        (new Query("/interface/monitor-traffic"))
            ->equal("interface", "ether1")
            ->equal("once", "true")
    )->read();

    $rx = 0;
    $tx = 0;

    if (!empty($interfaces[0])) {
        $rx = (int)$interfaces[0]["rx-bits-per-second"];
        $tx = (int)$interfaces[0]["tx-bits-per-second"];
    }

    echo json_encode([
        "success" => true,
        "hotspot_users" => count($hotspotActive),
        "pppoe_users" => count($pppoeActive),
        "rx_mbps" => round($rx / 1000000, 2),
        "tx_mbps" => round($tx / 1000000, 2),
        "time" => date("H:i:s")
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
