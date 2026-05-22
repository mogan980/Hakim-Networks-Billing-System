<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

try{
    $r = $pdo->query("SELECT * FROM mikrotik_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $client = new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_username"],
        "pass"=>$r["router_password"],
        "port"=>(int)$r["api_port"],
        "timeout"=>10
    ]));

    $active = $client->query(new Query("/ip/hotspot/active/print"))->read();
    $hosts = $client->query(new Query("/ip/hotspot/host/print"))->read();
    $bindings = $client->query(new Query("/ip/hotspot/ip-binding/print"))->read();

    echo json_encode([
        "ok"=>true,
        "active"=>$active,
        "hosts"=>$hosts,
        "bindings"=>$bindings,
        "counts"=>[
            "active"=>count($active),
            "hosts"=>count($hosts),
            "bindings"=>count($bindings)
        ]
    ]);
}catch(Exception $e){
    echo json_encode(["ok"=>false,"error"=>$e->getMessage()]);
}
