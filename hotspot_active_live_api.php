<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Query;

$config = require __DIR__ . "/config/mikrotik.php";

$hosts = [
    $config["host"] ?? "192.168.88.1",
    "192.168.88.1",
    "10.10.10.1"
];

$client = null;
$error = "";

foreach(array_unique($hosts) as $host){
    try{
        $client = new Client([
            "host" => $host,
            "user" => $config["user"],
            "pass" => $config["pass"],
            "port" => $config["port"],
            "timeout" => 5
        ]);
        break;
    }catch(Exception $e){
        $error = $e->getMessage();
    }
}

if(!$client){
    echo json_encode([
        "success" => false,
        "error" => $error,
        "users" => [],
        "count" => 0
    ]);
    exit;
}

try{
    $active = $client->query(new Query("/ip/hotspot/active/print"))->read();

    $users = [];

    foreach($active as $u){
        $users[] = [
            "user" => $u["user"] ?? "-",
            "address" => $u["address"] ?? "-",
            "mac" => $u["mac-address"] ?? "-",
            "uptime" => $u["uptime"] ?? "-",
            "bytes_in" => $u["bytes-in"] ?? 0,
            "bytes_out" => $u["bytes-out"] ?? 0,
            "server" => $u["server"] ?? "-",
            ".id" => $u[".id"] ?? ""
        ];
    }

    echo json_encode([
        "success" => true,
        "count" => count($users),
        "users" => $users,
        "time" => date("Y-m-d H:i:s")
    ]);

}catch(Exception $e){
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "users" => [],
        "count" => 0
    ]);
}
