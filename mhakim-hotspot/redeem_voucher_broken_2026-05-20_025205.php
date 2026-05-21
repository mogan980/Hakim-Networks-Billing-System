<?php
date_default_timezone_set("Africa/Nairobi");
require_once "/var/www/html/mhakim-hotspot/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function fail($m){
    header("Location: index.php?error=" . urlencode($m));
    exit;
}

$code = strtoupper(trim($_POST["voucher_code"] ?? $_GET["voucher_code"] ?? ""));
$clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

if(!$code) fail("Voucher code required");

$stmt = $pdo->prepare("SELECT * FROM smart_vouchers WHERE voucher_code=? LIMIT 1");
$stmt->execute([$code]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$v) fail("Invalid voucher");
if($v["status"] !== "unused") fail("Voucher already used or expired");

$r = $pdo->query("SELECT * FROM mikrotik_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);

try{
    $client = new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_username"],
        "pass"=>$r["router_password"],
        "port"=>(int)$r["api_port"],
        "timeout"=>10
    ]));

    $profile = "VOUCHER-" . str_replace("M","",$v["speed_down"]) . "-" . str_replace("M","",$v["speed_up"]);
    $rate = $v["speed_down"] . "/" . $v["speed_up"];
    $uptime = ((int)$v["duration_hours"]) . "h";

    $profiles = $client->query((new Query("/ip/hotspot/user/profile/print"))->where("name",$profile))->read();

    if(empty($profiles)){
        $client->query((new Query("/ip/hotspot/user/profile/add"))
            ->equal("name",$profile)
            ->equal("rate-limit",$rate)
            ->equal("shared-users","1")
        )->read();
    }

    $old = $client->query((new Query("/ip/hotspot/user/print"))->where("name",$code))->read();
    foreach($old as $u){
        if(isset($u[".id"])){
            $client->query((new Query("/ip/hotspot/user/remove"))->equal(".id",$u[".id"]))->read();
        }
    }

    $client->query((new Query("/ip/hotspot/user/add"))
        ->equal("name",$code)
        ->equal("password",$code)
        ->equal("profile",$profile)
        ->equal("limit-uptime",$uptime)
        ->equal("comment","smart-voucher:".$code)
    )->read();

    if($clientIp && $clientIp !== "192.168.88.1"){
        $oldB = $client->query((new Query("/ip/hotspot/ip-binding/print"))->where("address",$clientIp))->read();

        foreach($oldB as $b){
            if(isset($b[".id"])){
                $client->query((new Query("/ip/hotspot/ip-binding/remove"))->equal(".id",$b[".id"]))->read();
            }
        }

        $client->query((new Query("/ip/hotspot/ip-binding/add"))
            ->equal("address",$clientIp)
            ->equal("type","bypassed")
            ->equal("comment","voucher:".$code)
        )->read();
    }

    $expires = date("Y-m-d H:i:s", time() + ((int)$v["duration_hours"] * 3600));

    $up = $pdo->prepare("UPDATE smart_vouchers SET status='used', used_by=?, used_at=NOW(), expires_at=? WHERE id=?");
    $up->execute([$clientIp,$expires,$v["id"]]);

    header("Location: voucher_success.php?u=".urlencode($code)."&p=".urlencode($code));
    exit;

}catch(Exception $e){
    fail("Activation failed: ".$e->getMessage());
}
