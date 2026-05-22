<?php
header("Content-Type: application/json");

$cache = __DIR__ . "/cache/mikrotik_status.json";

if(file_exists($cache)){
    $d = json_decode(file_get_contents($cache), true);
    echo json_encode([
        "ok"=>true,
        "online"=>$d["online_users"] ?? [],
        "cached"=>true
    ]);
    exit;
}

echo json_encode(["ok"=>true,"online"=>[],"cached"=>true]);
