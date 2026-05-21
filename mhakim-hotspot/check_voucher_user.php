<?php
require_once __DIR__ . "/router_connect.php";
use RouterOS\Query;

$api = hotspotRouter();
$user = "MH-7449FDD5";

$rows = $api->query(
    (new Query("/ip/hotspot/user/print"))
    ->where("name", $user)
)->read();

header("Content-Type: application/json");
echo json_encode($rows, JSON_PRETTY_PRINT);
