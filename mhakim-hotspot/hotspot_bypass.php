<?php
require_once __DIR__ . "/router_connect.php";

use RouterOS\Query;

function activateClientBypass($clientIp, $comment="Hakim Networks Payment"){
    if(!$clientIp){
        throw new Exception("Client IP missing");
    }

    $api = hotspotRouter();

    $existing = $api->query(
        (new Query("/ip/hotspot/ip-binding/print"))
        ->where("address", $clientIp)
    )->read();

    if(empty($existing)){
        $api->query(
            (new Query("/ip/hotspot/ip-binding/add"))
            ->equal("address", $clientIp)
            ->equal("type", "bypassed")
            ->equal("comment", $comment)
        )->read();
    }else{
        foreach($existing as $b){
            if(!empty($b[".id"])){
                $api->query(
                    (new Query("/ip/hotspot/ip-binding/set"))
                    ->equal(".id", $b[".id"])
                    ->equal("type", "bypassed")
                    ->equal("disabled", "no")
                    ->equal("comment", $comment)
                )->read();
            }
        }
    }

    return true;
}
