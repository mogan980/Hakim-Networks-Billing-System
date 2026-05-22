<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

try {

    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$router) {
        die("Router not configured");
    }

    $client = new Client(new Config([
        "host" => $router["router_ip"],
        "user" => $router["router_user"],
        "pass" => $router["router_pass"],
        "port" => (int)$router["router_port"]
    ]));

    $hotspot = $client->query(
        new Query("/ip/hotspot/active/print")
    )->read();

    foreach ($hotspot as $u) {

        $username = $u["user"] ?? "Unknown";
        $ip = $u["address"] ?? "";
        $rx = $u["bytes-in"] ?? 0;
        $tx = $u["bytes-out"] ?? 0;

        $stmt = $pdo->prepare("
            INSERT INTO realtime_bandwidth
            (username, ip_address, rx_rate, tx_rate, total_rx, total_tx)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $username,
            $ip,
            formatBytes($rx),
            formatBytes($tx),
            $rx,
            $tx
        ]);
    }

    echo "Realtime bandwidth synced successfully.\n";

} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}

function formatBytes($bytes){

    $bytes = (float)$bytes;

    if($bytes >= 1073741824)
        return round($bytes/1073741824,2).' GB';

    if($bytes >= 1048576)
        return round($bytes/1048576,2).' MB';

    if($bytes >= 1024)
        return round($bytes/1024,2).' KB';

    return $bytes.' B';
}
