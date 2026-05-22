<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

header("Content-Type: application/json");

try {
    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $client = new Client(new Config([
        "host" => $router["router_ip"],
        "user" => $router["router_user"],
        "pass" => $router["router_pass"],
        "port" => (int)$router["router_port"],
        "timeout" => 5,
        "attempts" => 1
    ]));

    $bindings = $client->query(
        new Query("/ip/hotspot/ip-binding/print")
    )->read();

    $rows = [];
    foreach($bindings as $b){
        if(($b["type"] ?? "") === "bypassed"){
            $rows[] = [
                "ip" => $b["address"] ?? "-",
                "mac" => $b["mac-address"] ?? "-",
                "comment" => $b["comment"] ?? "Bypassed Client",
                "status" => ($b["disabled"] ?? "false") === "true" ? "disabled" : "online"
            ];
        }
    }

    echo json_encode(["success"=>true, "rows"=>$rows]);

} catch(Exception $e){
    echo json_encode(["success"=>false, "error"=>$e->getMessage(), "rows"=>[]]);
}
