<?php
require_once __DIR__ . "/router_connect.php";

use RouterOS\Query;

header("Content-Type: application/json");

try {
    $api = hotspotRouter();

    $username = "254704467699";
    $password = "7699";

    $existing = $api->query(
        (new Query("/ip/hotspot/user/print"))
        ->where("name", $username)
    )->read();

    if(empty($existing)){
        $api->query(
            (new Query("/ip/hotspot/user/add"))
            ->equal("name", $username)
            ->equal("password", $password)
            ->equal("profile", "default")
            ->equal("limit-uptime", "1h")
            ->equal("disabled", "no")
            ->equal("comment", "Hakim Test Activation")
        )->read();
    }

    $check = $api->query(
        (new Query("/ip/hotspot/user/print"))
        ->where("name", $username)
    )->read();

    echo json_encode([
        "success" => true,
        "username" => $username,
        "password" => $password,
        "mikrotik_user_found" => !empty($check),
        "data" => $check
    ], JSON_PRETTY_PRINT);

} catch(Exception $e){
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
