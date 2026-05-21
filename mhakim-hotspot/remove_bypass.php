<?php
require_once __DIR__ . "/router_connect.php";
use RouterOS\Query;

$api = hotspotRouter();
$rows = $api->query(new Query("/ip/hotspot/ip-binding/print"))->read();

foreach($rows as $r){
    if(($r["type"] ?? "") === "bypassed" && !empty($r[".id"])){
        $api->query(
            (new Query("/ip/hotspot/ip-binding/remove"))
            ->equal(".id", $r[".id"])
        )->read();
    }
}
echo "Bypass entries removed\n";
