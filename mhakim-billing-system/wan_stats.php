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
        throw new Exception("Router settings missing.");
    }

    $api = new Client(new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
    ]));

    $interfaces = $api->query(
        (new Query("/interface/monitor-traffic"))
            ->equal("interface", "ether1")
            ->equal("once", "")
    )->read();

    $rx = (float)($interfaces[0]["rx-bits-per-second"] ?? 0);
    $tx = (float)($interfaces[0]["tx-bits-per-second"] ?? 0);

    echo json_encode([
        "success" => true,
        "rx_mbps" => round($rx / 1000000, 2),
        "tx_mbps" => round($tx / 1000000, 2),
        "time" => date("H:i:s")
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
