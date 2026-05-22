<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

/*
|--------------------------------------------------------------------------
| MikroTik Settings
|--------------------------------------------------------------------------
*/

$settings = $pdo->query("
    SELECT *
    FROM mikrotik_settings
    ORDER BY id DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if(!$settings){
    die("Missing MikroTik settings\n");
}

/*
|--------------------------------------------------------------------------
| Connect to MikroTik
|--------------------------------------------------------------------------
*/

$api = new Client(new Config([
    "host" => $settings["router_ip"],
    "user" => $settings["router_username"],
    "pass" => $settings["router_password"],
    "port" => (int)$settings["api_port"],
    "timeout" => 5
]));

/*
|--------------------------------------------------------------------------
| Find Expired Users
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT *
    FROM clients
    WHERE status='active'
    AND expires_at IS NOT NULL
    AND expires_at <= NOW()
");

$expired = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

foreach($expired as $user){

    $username = trim($user["username"]);

    if(!$username){
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Hotspot User
    |--------------------------------------------------------------------------
    */

    $hotspotUser = $api->query(
        (new Query('/ip/hotspot/user/print'))
            ->where('name', $username)
    )->read();

    if(!empty($hotspotUser)){

        $api->query(
            (new Query('/ip/hotspot/user/remove'))
                ->equal('.id', $hotspotUser[0]['.id'])
        )->read();
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Active Sessions
    |--------------------------------------------------------------------------
    */

    $active = $api->query(
        (new Query('/ip/hotspot/active/print'))
            ->where('user', $username)
    )->read();

    foreach($active as $a){

        $api->query(
            (new Query('/ip/hotspot/active/remove'))
                ->equal('.id', $a['.id'])
        )->read();
    }

    /*
    |--------------------------------------------------------------------------
    | Update Database
    |--------------------------------------------------------------------------
    */

    $pdo->prepare("
        UPDATE clients
        SET status='expired'
        WHERE id=?
    ")->execute([$user["id"]]);

    $count++;
}

echo "Expired users processed: {$count}\n";
