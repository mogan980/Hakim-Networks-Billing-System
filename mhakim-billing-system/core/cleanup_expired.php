<?php

date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/../vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Query;

$config = require __DIR__ . "/../config/mikrotik.php";

$client = new Client([
    "host" => $config["host"],
    "user" => $config["user"],
    "pass" => $config["pass"],
    "port" => $config["port"]
]);

$now = time();

echo "Cleanup started: " . date("Y-m-d H:i:s") . PHP_EOL;

/*
|--------------------------------------------------------------------------
| REMOVE EXPIRED IP BINDINGS
|--------------------------------------------------------------------------
*/

$bindings = $client->query(
    new Query("/ip/hotspot/ip-binding/print")
)->read();

foreach ($bindings as $b) {

    $comment = $b["comment"] ?? "";

    if (preg_match('/expires:([0-9\-\s:]+)/', $comment, $m)) {

        $expires = trim($m[1]);

        if (strtotime($expires) <= $now) {

            echo "Removing binding: " . ($b["address"] ?? "") . PHP_EOL;

            $client->query(
                (new Query("/ip/hotspot/ip-binding/remove"))
                    ->equal(".id", $b[".id"])
            )->read();
        }
    }
}

/*
|--------------------------------------------------------------------------
| REMOVE EXPIRED QUEUES
|--------------------------------------------------------------------------
*/

$queues = $client->query(
    new Query("/queue/simple/print")
)->read();

foreach ($queues as $q) {

    $comment = $q["comment"] ?? "";

    if (preg_match('/expires:([0-9\-\s:]+)/', $comment, $m)) {

        $expires = trim($m[1]);

        if (strtotime($expires) <= $now) {

            echo "Removing queue: " . ($q["name"] ?? "") . PHP_EOL;

            $client->query(
                (new Query("/queue/simple/remove"))
                    ->equal(".id", $q[".id"])
            )->read();
        }
    }
}

echo "Cleanup finished\n";
