<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

try {
    $router = $pdo->query("SELECT * FROM routers WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if(!$router) throw new Exception("No active router selected");

    $client = new Client(new Config([
        "host"=>$router["router_ip"],
        "user"=>$router["router_username"],
        "pass"=>$router["router_password"],
        "port"=>(int)$router["api_port"],
        "timeout"=>5
    ]));

    $hotspot = $client->query(new Query("/ip/hotspot/active/print"))->read();
    $bindings = $client->query(new Query("/ip/hotspot/ip-binding/print"))->read();
    $pppoe = $client->query(new Query("/ppp/active/print"))->read();
    $queues = $client->query(new Query("/queue/simple/print"))->read();
    $leases = $client->query(new Query("/ip/dhcp-server/lease/print"))->read();
    $interfaces = $client->query(new Query("/interface/print"))->read();

    echo json_encode([
        "ok"=>true,
        "hotspot"=>$hotspot,
        "bindings"=>$bindings,
        "pppoe"=>$pppoe,
        "queues"=>$queues,
        "leases"=>$leases,
        "interfaces"=>$interfaces,
        "counts"=>[
            "hotspot"=>count($hotspot),
            "bindings"=>count($bindings),
            "pppoe"=>count($pppoe),
            "queues"=>count($queues),
            "leases"=>count($leases),
            "interfaces"=>count($interfaces)
        ]
    ]);
} catch(Exception $e){
    echo json_encode(["ok"=>false,"error"=>$e->getMessage()]);
}
