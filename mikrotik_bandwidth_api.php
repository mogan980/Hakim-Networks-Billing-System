<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Query;

try{
    $cfg = require __DIR__ . "/config/mikrotik.php";

    $client = new Client([
        "host"=>$cfg["host"],
        "user"=>$cfg["user"],
        "pass"=>$cfg["pass"],
        "port"=>$cfg["port"],
        "timeout"=>5
    ]);

    $interfaces = $client->query(new Query("/interface/print"))->read();

    $out = [];

    foreach($interfaces as $i){
        $name = $i["name"] ?? "";
        if(!$name) continue;

        try{
            $traffic = $client->query(
                (new Query("/interface/monitor-traffic"))
                ->equal("interface",$name)
                ->equal("once","")
            )->read();

            $rx = (int)($traffic[0]["rx-bits-per-second"] ?? 0);
            $tx = (int)($traffic[0]["tx-bits-per-second"] ?? 0);

            $out[] = [
                "name"=>$name,
                "type"=>$i["type"] ?? "-",
                "running"=>($i["running"] ?? "false"),
                "disabled"=>($i["disabled"] ?? "false"),
                "rx"=>$rx,
                "tx"=>$tx,
                "rx_mbps"=>round($rx/1000000,2),
                "tx_mbps"=>round($tx/1000000,2)
            ];
        }catch(Exception $e){}
    }

    echo json_encode([
        "success"=>true,
        "time"=>date("H:i:s"),
        "interfaces"=>$out
    ]);

}catch(Exception $e){
    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage(),
        "time"=>date("H:i:s"),
        "interfaces"=>[]
    ]);
}
