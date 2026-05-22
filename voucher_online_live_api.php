<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config/database.php";

use RouterOS\Client;
use RouterOS\Query;

$config = require __DIR__ . "/config/mikrotik.php";
$hosts = array_unique([$config["host"] ?? "192.168.88.1", "192.168.88.1", "10.10.10.1"]);

$client = null;
foreach($hosts as $host){
    try{
        $client = new Client([
            "host"=>$host,
            "user"=>$config["user"],
            "pass"=>$config["pass"],
            "port"=>$config["port"],
            "timeout"=>5
        ]);
        break;
    }catch(Exception $e){}
}

$onlineIps = [];
if($client){
    foreach($client->query(new Query("/ip/hotspot/active/print"))->read() as $u){
        if(!empty($u["address"])) $onlineIps[$u["address"]] = true;
    }
}

$rows = $pdo->query("SELECT voucher_code, used_by FROM smart_vouchers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$out = [];
foreach($rows as $r){
    $code = preg_replace('/\s+/', '', $r["voucher_code"] ?? "");
    $ip = trim($r["used_by"] ?? "");
    $out[$code] = $ip && isset($onlineIps[$ip]);
}

echo json_encode(["success"=>true,"items"=>$out]);
