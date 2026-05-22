<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/database.php";

use RouterOS\Client;
use RouterOS\Query;

$pdo->exec("SET time_zone = '+03:00'");

$log = "/tmp/hakim_auto_reconnect.log";

function logx($m){
    global $log;
    file_put_contents($log, "[".date("Y-m-d H:i:s")."] ".$m.PHP_EOL, FILE_APPEND);
}

function mt(){
    $cfg = require __DIR__ . "/../config/mikrotik.php";

    foreach(array_unique([$cfg["host"] ?? "192.168.88.1", "192.168.88.1", "10.10.10.1"]) as $host){
        try{
            return new Client([
                "host"=>$host,
                "user"=>$cfg["user"],
                "pass"=>$cfg["pass"],
                "port"=>$cfg["port"],
                "timeout"=>5
            ]);
        }catch(Exception $e){
            logx("MikroTik host failed {$host}: ".$e->getMessage());
        }
    }

    return null;
}

function has_queue($client,$ip){
    try{
        foreach($client->query(new Query("/queue/simple/print"))->read() as $q){
            $target = $q["target"] ?? "";
            if(str_contains($target, $ip)){
                return true;
            }
        }
    }catch(Exception $e){}
    return false;
}

function add_queue($client,$name,$ip,$down="6M",$up="2M"){
    try{
        if(has_queue($client,$ip)){
            return false;
        }

        // MikroTik format is upload/download
        $client->query(
            (new Query("/queue/simple/add"))
            ->equal("name",$name)
            ->equal("target",$ip."/32")
            ->equal("max-limit",$up."/".$down)
        )->read();

        return true;
    }catch(Exception $e){
        logx("Queue add failed for {$ip}: ".$e->getMessage());
        return false;
    }
}

$client = mt();

if(!$client){
    logx("Auto reconnect aborted: MikroTik unreachable.");
    exit;
}

$reconnected = 0;

/* Valid STK / paid hotspot clients */
try{
    $rows = $pdo->query("
        SELECT id, phone, client_ip, expires_at
        FROM payments
        WHERE status='paid'
        AND client_ip IS NOT NULL
        AND client_ip!=''
        AND expires_at IS NOT NULL
        AND expires_at > NOW()
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach($rows as $r){
        $ip = trim($r["client_ip"]);
        $name = "HN-STK-".$ip;

        if(add_queue($client,$name,$ip,"6M","2M")){
            $reconnected++;
            logx("Reconnected STK client {$ip}, expires {$r["expires_at"]}");
        }
    }
}catch(Exception $e){
    logx("STK scan failed: ".$e->getMessage());
}

/* Valid smart voucher clients */
try{
    $rows = $pdo->query("
        SELECT id, voucher_code, used_by, expires_at, speed
        FROM smart_vouchers
        WHERE status='used'
        AND used_by IS NOT NULL
        AND used_by!=''
        AND expires_at IS NOT NULL
        AND expires_at > NOW()
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach($rows as $r){
        $ip = trim($r["used_by"]);
        $name = "HN-VOUCHER-".$ip;

        $down = "6M";
        $up = "2M";

        if(!empty($r["speed"]) && str_contains($r["speed"],"/")){
            [$d,$u] = explode("/", strtoupper($r["speed"]));
            $down = trim($d) ?: "6M";
            $up = trim($u) ?: "2M";
        }

        if(add_queue($client,$name,$ip,$down,$up)){
            $reconnected++;
            logx("Reconnected voucher {$r["voucher_code"]} {$ip}, expires {$r["expires_at"]}");
        }
    }
}catch(Exception $e){
    logx("Voucher scan failed: ".$e->getMessage());
}

logx("Auto reconnect complete. Reconnected: ".$reconnected);
