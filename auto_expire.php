<?php
date_default_timezone_set("Africa/Nairobi");

require_once "/var/www/html/mhakim-billing-system/config/database.php";
require_once "/var/www/html/mhakim-billing-system/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$settings = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if (!$settings) exit;

try {
    $api = new Client(new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
    ]));

    $expired = $pdo->query("
        SELECT * FROM clients
        WHERE status='active'
        AND expires_at IS NOT NULL
        AND expires_at <= NOW()
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expired as $client) {
        $username = $client["username"];
        $comment = "MH-" . $username;

        $active = $api->query(
            (new Query('/ip/hotspot/active/print'))->where('user', $username)
        )->read();

        foreach ($active as $a) {
            if (isset($a[".id"])) {
                $api->query((new Query('/ip/hotspot/active/remove'))->equal('.id', $a[".id"]))->read();
            }
        }

        $users = $api->query(
            (new Query('/ip/hotspot/user/print'))->where('name', $username)
        )->read();

        foreach ($users as $u) {
            if (isset($u[".id"])) {
                $api->query((new Query('/ip/hotspot/user/disable'))->equal('.id', $u[".id"]))->read();
            }
        }

        $bindings = $api->query(
            (new Query('/ip/hotspot/ip-binding/print'))->where('comment', $comment)
        )->read();

        foreach ($bindings as $b) {
            if (isset($b[".id"])) {
                $api->query((new Query('/ip/hotspot/ip-binding/remove'))->equal('.id', $b[".id"]))->read();
            }
        }

        $queues = $api->query(
            (new Query('/queue/simple/print'))->where('name', $comment)
        )->read();

        foreach ($queues as $q) {
            if (isset($q[".id"])) {
                $api->query((new Query('/queue/simple/remove'))->equal('.id', $q[".id"]))->read();
            }
        }

        $pdo->prepare("UPDATE clients SET status='expired' WHERE id=?")->execute([$client["id"]]);
    }

} catch (Exception $e) {
    file_put_contents("/var/www/html/mhakim-billing-system/auto_expire.log", $e->getMessage() . PHP_EOL, FILE_APPEND);
}
