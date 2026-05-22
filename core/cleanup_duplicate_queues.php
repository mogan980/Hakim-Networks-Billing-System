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

$queues = $client->query(new Query("/queue/simple/print"))->read();

$targets = [];

foreach ($queues as $q) {
    $target = $q["target"] ?? "";
    $name = $q["name"] ?? "";
    $id = $q[".id"] ?? "";

    if (!$target || !$id) continue;

    if (!isset($targets[$target])) {
        $targets[$target] = [];
    }

    $targets[$target][] = $q;
}

foreach ($targets as $target => $list) {
    if (count($list) <= 1) continue;

    usort($list, function($a, $b) {
        $aIsHN = str_starts_with($a["name"] ?? "", "HN-") ? 1 : 0;
        $bIsHN = str_starts_with($b["name"] ?? "", "HN-") ? 1 : 0;
        return $bIsHN <=> $aIsHN;
    });

    $keep = array_shift($list);

    foreach ($list as $q) {
        echo "Removing duplicate queue: " . ($q["name"] ?? "") . " target " . $target . PHP_EOL;

        $client->query(
            (new Query("/queue/simple/remove"))
                ->equal(".id", $q[".id"])
        )->read();
    }
}

echo "Duplicate queue cleanup done " . date("Y-m-d H:i:s") . PHP_EOL;
