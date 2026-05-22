<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Query;

$config = require __DIR__ . "/config/mikrotik.php";

$cacheFile = __DIR__ . "/router_status_cache.json";
$now = time();
$graceSeconds = 90;
$maxFailCount = 3;

$old = [
    "status" => "offline",
    "last_success" => 0,
    "last_check" => 0,
    "fail_count" => 0,
    "identity" => "MikroTik",
    "message" => "No cache yet"
];

if (file_exists($cacheFile)) {
    $json = json_decode(file_get_contents($cacheFile), true);
    if (is_array($json)) $old = array_merge($old, $json);
}

/* Prevent too many API calls: reuse cache if checked within 10 sec */
if (($now - (int)$old["last_check"]) < 10) {
    echo json_encode($old);
    exit;
}

try {
    $client = new Client([
        "host" => $config["host"],
        "user" => $config["user"],
        "pass" => $config["pass"],
        "port" => $config["port"],
        "timeout" => 4
    ]);

    $identity = $client->query(new Query("/system/identity/print"))->read();
    $name = $identity[0]["name"] ?? "MikroTik";

    $data = [
        "status" => "online",
        "online" => true,
        "identity" => $name,
        "host" => $config["host"],
        "last_success" => $now,
        "last_check" => $now,
        "fail_count" => 0,
        "message" => "Connected",
        "time" => date("Y-m-d H:i:s")
    ];

    file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode($data);
    exit;

} catch (Exception $e) {

    $failCount = ((int)$old["fail_count"]) + 1;
    $lastSuccess = (int)$old["last_success"];

    $stillOnline = ($lastSuccess > 0 && ($now - $lastSuccess) <= $graceSeconds && $failCount < $maxFailCount);

    $data = [
        "status" => $stillOnline ? "online" : "offline",
        "online" => $stillOnline,
        "identity" => $old["identity"] ?? "MikroTik",
        "host" => $config["host"],
        "last_success" => $lastSuccess,
        "last_check" => $now,
        "fail_count" => $failCount,
        "message" => $stillOnline ? "Using grace cache: " . $e->getMessage() : $e->getMessage(),
        "time" => date("Y-m-d H:i:s")
    ];

    file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode($data);
    exit;
}
