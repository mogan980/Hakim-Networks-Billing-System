<?php
require_once "/var/www/html/mhakim-billing-system/vendor/autoload.php";
require_once "/var/www/html/mhakim-billing-system/config/database.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$router = $pdo->query("SELECT * FROM super_admin_router LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$api = new Client(new Config([
    "host"=>$router["router_ip"],
    "user"=>$router["router_user"],
    "pass"=>$router["router_pass"],
    "port"=>(int)$router["router_port"],
    "timeout"=>5,
    "attempts"=>1
]));

$profiles = $api->query(new Query("/ip/hotspot/profile/print"))->read();

foreach($profiles as $p){
    if(!empty($p[".id"])){
        $api->query(
            (new Query("/ip/hotspot/profile/set"))
            ->equal(".id",$p[".id"])
            ->equal("login-by","http-pap,http-chap,cookie")
            ->equal("dns-name","")
        )->read();
    }
}

echo "Hotspot profile repaired\n";
