<?php

header("Content-Type: application/json");

require_once "config/database.php";
require_once "vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

try {

    $settings = $pdo->query("
        SELECT * FROM mikrotik_settings
        ORDER BY id DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        throw new Exception("Router settings missing");
    }

    $client = new Client(new Config([
        'host' => $settings['router_ip'],
        'user' => $settings['router_username'],
        'pass' => $settings['router_password'],
        'port' => (int)$settings['api_port'],
        'timeout' => 3,
    ]));

    $identity = $client->query(
        new Query('/system/identity/print')
    )->read();

    $routerName = $identity[0]['name'] ?? 'MikroTik';

    echo json_encode([
        "success" => true,
        "status" => "online",
        "router" => $routerName,
        "message" => "Router reachable"
    ]);

} catch(Exception $e){

    echo json_encode([
        "success" => false,
        "status" => "offline",
        "message" => $e->getMessage()
    ]);
}
