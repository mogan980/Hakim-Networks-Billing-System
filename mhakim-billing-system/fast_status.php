<?php
header("Content-Type: application/json");

$file = __DIR__ . "/cache/mikrotik_status.json";

if(file_exists($file)){
    echo file_get_contents($file);
    exit;
}

echo json_encode([
    "ok"=>true,
    "stable_online"=>true,
    "hotspot_online"=>0,
    "queues"=>0,
    "cpu"=>0,
    "uptime"=>"-",
    "updated_at"=>date("Y-m-d H:i:s")
]);
