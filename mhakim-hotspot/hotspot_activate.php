<?php
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function hn_log($msg){
    file_put_contents(__DIR__."/activation_engine.log","[".date("Y-m-d H:i:s")."] ".$msg.PHP_EOL,FILE_APPEND);
}

function hn_speed($v){
    $v = strtoupper(trim((string)$v));
    if($v === "") return "1M";
    if(strpos($v,"M")===false && strpos($v,"K")===false) $v .= "M";
    return $v;
}

function hn_router($pdo){
    $r = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $tries = [];

    if($r){
        $tries[] = $r;
        if(($r["router_ip"] ?? "") !== "192.168.88.1"){
            $r2 = $r;
            $r2["router_ip"] = "192.168.88.1";
            $tries[] = $r2;
        }
    }

    foreach($tries as $t){
        try{
            return new Client(new Config([
                "host"=>$t["router_ip"],
                "user"=>$t["router_username"],
                "pass"=>$t["router_password"],
                "port"=>(int)$t["api_port"],
                "timeout"=>8
            ]));
        }catch(Exception $e){
            hn_log("Router failed ".$t["router_ip"].": ".$e->getMessage());
        }
    }

    throw new Exception("No MikroTik connection available.");
}

function hn_activate($pdo,$clientIp,$username,$password,$package,$source,$ref){
    $client = hn_router($pdo);

    $hours = (int)($package["duration_hours"] ?? 24);
    if($hours < 1) $hours = 24;

    $down = hn_speed($package["speed_down"] ?? "8M");
    $up   = hn_speed($package["speed_up"] ?? "2M");

    // MikroTik format: upload/download
    $rate = $up . "/" . $down;

    $expires = date("Y-m-d H:i:s", time()+($hours*3600));
    $comment = $source.":".$ref." expires:".$expires;

    // remove old same hotspot user
    foreach($client->query((new Query("/ip/hotspot/user/print"))->where("name",$username))->read() as $u){
        if(isset($u[".id"])) $client->query((new Query("/ip/hotspot/user/remove"))->equal(".id",$u[".id"]))->read();
    }

    // add user record
    $client->query(
        (new Query("/ip/hotspot/user/add"))
        ->equal("name",$username)
        ->equal("password",$password)
        ->equal("limit-uptime",$hours."h")
        ->equal("comment",$comment)
    )->read();

    // remove active/host records for clean reconnect
    foreach($client->query((new Query("/ip/hotspot/active/print"))->where("address",$clientIp))->read() as $a){
        if(isset($a[".id"])) $client->query((new Query("/ip/hotspot/active/remove"))->equal(".id",$a[".id"]))->read();
    }

    foreach($client->query((new Query("/ip/hotspot/host/print"))->where("address",$clientIp))->read() as $h){
        if(isset($h[".id"])) $client->query((new Query("/ip/hotspot/host/remove"))->equal(".id",$h[".id"]))->read();
    }

    // remove old ip-binding for this device
    foreach($client->query((new Query("/ip/hotspot/ip-binding/print"))->where("address",$clientIp))->read() as $b){
        if(isset($b[".id"])) $client->query((new Query("/ip/hotspot/ip-binding/remove"))->equal(".id",$b[".id"]))->read();
    }

    // bypass hotspot login for paid/voucher device
    $client->query(
        (new Query("/ip/hotspot/ip-binding/add"))
        ->equal("address",$clientIp)
        ->equal("type","bypassed")
        ->equal("comment",$comment)
    )->read();

    // remove old queue
    foreach($client->query((new Query("/queue/simple/print"))->where("target",$clientIp."/32"))->read() as $q){
        if(isset($q[".id"])) $client->query((new Query("/queue/simple/remove"))->equal(".id",$q[".id"]))->read();
    }

    // add speed limit
    $client->query(
        (new Query("/queue/simple/add"))
        ->equal("name",strtoupper($source)."-".$username)
        ->equal("target",$clientIp."/32")
        ->equal("max-limit",$rate)
        ->equal("comment",$comment)
    )->read();

    hn_log("ACTIVATED $source user=$username ip=$clientIp rate=$rate expires=$expires");

    return $expires;
}
