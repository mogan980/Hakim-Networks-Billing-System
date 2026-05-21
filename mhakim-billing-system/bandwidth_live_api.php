<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function mbps($v){ return round(((float)$v)/1000000, 2); }

try {
    $router = $pdo->query("SELECT * FROM routers WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $client = new Client(new Config([
        "host"=>$router["router_ip"],
        "user"=>$router["router_username"],
        "pass"=>$router["router_password"],
        "port"=>(int)$router["api_port"],
        "timeout"=>5
    ]));

    $iface = "bridge";
    $traffic = $client->query(
        (new Query("/interface/monitor-traffic"))
        ->equal("interface", $iface)
        ->equal("once", "")
    )->read();

    if(empty($traffic)){
        $traffic = $client->query(
            (new Query("/interface/monitor-traffic"))
            ->equal("interface", "ether1")
            ->equal("once", "")
        )->read();
    }

    $rx = (float)($traffic[0]["rx-bits-per-second"] ?? 0);
    $tx = (float)($traffic[0]["tx-bits-per-second"] ?? 0);

    echo json_encode([
        "ok"=>true,
        "rx_bps"=>$rx,
        "tx_bps"=>$tx,
        "rx"=>mbps($rx)." Mbps",
        "tx"=>mbps($tx)." Mbps"
    ]);
} catch(Exception $e){
    echo json_encode(["ok"=>false,"error"=>$e->getMessage()]);
}
