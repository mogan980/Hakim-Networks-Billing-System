<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/database.php";

use RouterOS\Client;
use RouterOS\Query;

$pdo->exec("SET time_zone = '+03:00'");

function logx($m){
    file_put_contents("/tmp/hakim_auto_reconnect.log","[".date("Y-m-d H:i:s")."] ".$m.PHP_EOL,FILE_APPEND);
}

function mt(){
    $cfg=require __DIR__."/../config/mikrotik.php";

    return new Client([
        "host"=>$cfg["host"],
        "user"=>$cfg["user"],
        "pass"=>$cfg["pass"],
        "port"=>$cfg["port"],
        "timeout"=>5
    ]);
}

function queue_exists($client,$ip){
    foreach($client->query(new Query("/queue/simple/print"))->read() as $q){
        if(str_contains(($q["target"] ?? ""),$ip)){
            return true;
        }
    }
    return false;
}

function binding_exists($client,$ip){
    foreach($client->query(new Query("/ip/hotspot/ip-binding/print"))->read() as $b){
        if(($b["address"] ?? "")==$ip){
            return true;
        }
    }
    return false;
}

function add_binding($client,$ip,$comment=""){
    if(binding_exists($client,$ip)) return;

    $client->query(
        (new Query("/ip/hotspot/ip-binding/add"))
        ->equal("address",$ip)
        ->equal("type","bypassed")
        ->equal("comment",$comment)
    )->read();
}

function add_queue($client,$name,$ip,$down="6M",$up="2M"){
    if(queue_exists($client,$ip)) return;

    $client->query(
        (new Query("/queue/simple/add"))
        ->equal("name",$name)
        ->equal("target",$ip."/32")
        ->equal("max-limit",$up."/".$down)
    )->read();
}

try{

    $client=mt();

    /* VALID STK CLIENTS */
    $rows=$pdo->query("
        SELECT id, phone, client_ip, expires_at
        FROM payments
        WHERE status='paid'
        AND client_ip IS NOT NULL
        AND client_ip!=''
        AND expires_at > NOW()
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach($rows as $r){

        $ip=trim($r["client_ip"]);

        add_queue($client,"HN-STK-".$ip,$ip,"6M","2M");

        add_binding(
            $client,
            $ip,
            "auto-reconnect-stk"
        );

        logx("Restored STK client ".$ip);
    }

    /* VALID VOUCHER CLIENTS */
    $rows=$pdo->query("
        SELECT voucher_code, used_by, expires_at
        FROM smart_vouchers
        WHERE status='used'
        AND used_by IS NOT NULL
        AND used_by!=''
        AND expires_at > NOW()
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach($rows as $r){

        $ip=trim($r["used_by"]);

        add_queue($client,"HN-VOUCHER-".$ip,$ip,"6M","2M");

        add_binding(
            $client,
            $ip,
            "auto-reconnect-voucher"
        );

        logx("Restored Voucher client ".$ip);
    }

    echo "Reconnect engine completed.";

}catch(Exception $e){

    logx("Reconnect engine failed: ".$e->getMessage());
    echo $e->getMessage();
}
