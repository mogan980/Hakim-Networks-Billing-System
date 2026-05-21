<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$settings = $pdo->query("
SELECT * FROM mikrotik_settings
ORDER BY id DESC LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if(!$settings){
    die("Missing MikroTik settings\n");
}

$api = new Client(new Config([
    "host"=>$settings["router_ip"],
    "user"=>$settings["router_username"],
    "pass"=>$settings["router_password"],
    "port"=>(int)$settings["api_port"],
    "timeout"=>5
]));

$users = $api->query(
    new Query("/ip/hotspot/user/print")
)->read();

$count = 0;

foreach($users as $user){

    $username = $user["name"] ?? "";

    if(!$username){
        continue;
    }

    $check = $pdo->prepare("
        SELECT id,status
        FROM clients
        WHERE username=?
        LIMIT 1
    ");

    $check->execute([$username]);

    $client = $check->fetch(PDO::FETCH_ASSOC);

    if(!$client || $client["status"] === "expired"){

        $api->query(
            (new Query("/ip/hotspot/user/remove"))
                ->equal(".id",$user[".id"])
        )->read();

        $active = $api->query(
            (new Query("/ip/hotspot/active/print"))
                ->where("user",$username)
        )->read();

        foreach($active as $a){

            $api->query(
                (new Query("/ip/hotspot/active/remove"))
                    ->equal(".id",$a[".id"])
            )->read();
        }

        $count++;
    }
}

echo "Cleanup complete: {$count}\n";
