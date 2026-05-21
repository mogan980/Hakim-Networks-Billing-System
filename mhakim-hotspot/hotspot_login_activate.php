<?php
require_once __DIR__ . "/router_connect.php";

use RouterOS\Query;

function activateHotspotLogin($username, $password, $profile="PAYMENT-8M-2M", $limitUptime="1h"){
    $api = hotspotRouter();

    $username = preg_replace('/[^0-9A-Za-z_\-]/', '', $username);
    $password = preg_replace('/[^0-9A-Za-z_\-]/', '', $password);

    if(!$username || !$password){
        throw new Exception("Invalid hotspot username or password");
    }

    $existing = $api->query(
        (new Query("/ip/hotspot/user/print"))
        ->where("name", $username)
    )->read();

    if(!empty($existing)){
        foreach($existing as $u){
            if(!empty($u[".id"])){
                $api->query(
                    (new Query("/ip/hotspot/user/set"))
                    ->equal(".id", $u[".id"])
                    ->equal("password", $password)
                    ->equal("profile", $profile)
                    ->equal("limit-uptime", $limitUptime)
                    ->equal("disabled", "no")
                )->read();
            }
        }
    }else{
        $api->query(
            (new Query("/ip/hotspot/user/add"))
            ->equal("name", $username)
            ->equal("password", $password)
            ->equal("profile", $profile)
            ->equal("limit-uptime", $limitUptime)
            ->equal("disabled", "no")
            ->equal("comment", "Hakim Networks Hotspot Activation")
        )->read();
    }

    return [
        "username" => $username,
        "password" => $password,
        "login_url" => "http://192.168.88.1/login",
        "redirect" => "http://neverssl.com/"
    ];
}
