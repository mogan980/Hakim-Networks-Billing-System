<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/vendor/autoload.php";
use RouterOS\Client;
use RouterOS\Query;

try{
    $cfg = require __DIR__ . "/config/mikrotik.php";

    $mt = new Client([
        "host"=>$cfg["host"],
        "user"=>$cfg["user"],
        "pass"=>$cfg["pass"],
        "port"=>$cfg["port"],
        "timeout"=>5
    ]);

    $active = $mt->query(new Query("/ip/hotspot/active/print"))->read();
    $bindings = $mt->query(new Query("/ip/hotspot/ip-binding/print"))->read();
    $ppp = $mt->query(new Query("/ppp/active/print"))->read();

    $bypassed = [];
    foreach($bindings as $b){
        if(($b["type"] ?? "") === "bypassed"){
            $bypassed[] = [
                "ip"=>$b["address"] ?? "-",
                "mac"=>$b["mac-address"] ?? "-",
                "activation"=>$b["comment"] ?? "Bypassed",
                "status"=>"ONLINE"
            ];
        }
    }

    $hotspot = [];
    foreach($active as $a){
        $hotspot[] = [
            "user"=>$a["user"] ?? ($a["name"] ?? "-"),
            "ip"=>$a["address"] ?? "-",
            "mac"=>$a["mac-address"] ?? "-",
            "uptime"=>$a["uptime"] ?? "-",
            "download"=>$a["bytes-out"] ?? "0",
            "upload"=>$a["bytes-in"] ?? "0",
            "status"=>"ACTIVE"
        ];
    }

    $pppoe = [];
    foreach($ppp as $p){
        $pppoe[] = [
            "user"=>$p["name"] ?? "-",
            "ip"=>$p["address"] ?? "-",
            "caller"=>$p["caller-id"] ?? "-",
            "uptime"=>$p["uptime"] ?? "-",
            "service"=>$p["service"] ?? "pppoe",
            "status"=>"ACTIVE"
        ];
    }

    echo json_encode([
        "success"=>true,
        "bypassed"=>$bypassed,
        "hotspot"=>$hotspot,
        "pppoe"=>$pppoe,
        "counts"=>[
            "bypassed"=>count($bypassed),
            "hotspot"=>count($hotspot),
            "pppoe"=>count($pppoe)
        ],
        "time"=>date("Y-m-d H:i:s")
    ]);

}catch(Exception $e){
    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage(),
        "bypassed"=>[],
        "hotspot"=>[],
        "pppoe"=>[],
        "counts"=>["bypassed"=>0,"hotspot"=>0,"pppoe"=>0],
        "time"=>date("Y-m-d H:i:s")
    ]);
}
