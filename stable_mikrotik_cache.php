<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$cacheFile = __DIR__ . "/cache/mikrotik_status.json";
$ttl = 30;

if(file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl){
    echo file_get_contents($cacheFile);
    exit;
}

function respond($data, $cacheFile){
    file_put_contents($cacheFile, json_encode($data));
    echo json_encode($data);
    exit;
}

try{
    $r = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $client = new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_username"],
        "pass"=>$r["router_password"],
        "port"=>(int)$r["api_port"],
        "timeout"=>3
    ]));

    $resource = $client->query(new Query("/system/resource/print"))->read()[0] ?? [];
    $active = $client->query(new Query("/ip/hotspot/active/print"))->read();
    $hosts = $client->query(new Query("/ip/hotspot/host/print"))->read();
    $queues = $client->query(new Query("/queue/simple/print"))->read();

    respond([
        "ok"=>true,
        "stable_online"=>true,
        "router_identity"=>"MikroTik",
        "cpu"=>$resource["cpu-load"] ?? "0",
        "uptime"=>$resource["uptime"] ?? "-",
        "free_memory"=>$resource["free-memory"] ?? "0",
        "hotspot_online"=>count($active),
        "hosts"=>count($hosts),
        "queues"=>count($queues),
        "online_users"=>array_values(array_filter(array_map(fn($a)=>$a["user"] ?? null,$active))),
        "updated_at"=>date("Y-m-d H:i:s")
    ], $cacheFile);

}catch(Exception $e){

    if(file_exists($cacheFile)){
        $old = json_decode(file_get_contents($cacheFile), true);
        $old["ok"] = true;
        $old["stable_online"] = true;
        $old["cached"] = true;
        $old["warning"] = "Using cached data";
        echo json_encode($old);
        exit;
    }

    respond([
        "ok"=>false,
        "stable_online"=>false,
        "error"=>$e->getMessage(),
        "updated_at"=>date("Y-m-d H:i:s")
    ], $cacheFile);
}
