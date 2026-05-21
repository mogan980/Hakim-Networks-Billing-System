<?php
require_once "/var/www/html/mhakim-billing-system/vendor/autoload.php";
require_once "/var/www/html/mhakim-billing-system/config/database.php";

use RouterOS\Client;
use RouterOS\Config;

function hotspotRouter(){
    global $pdo;

    $router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if(!$router){
        throw new Exception("Router not configured");
    }

    return new Client(new Config([
        "host" => $router["router_ip"],
        "user" => $router["router_user"],
        "pass" => $router["router_pass"],
        "port" => (int)($router["router_port"] ?: 8728),
        "timeout" => 8,
        "attempts" => 1
    ]));
}
