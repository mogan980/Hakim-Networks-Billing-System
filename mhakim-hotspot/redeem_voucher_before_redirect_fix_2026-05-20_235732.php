<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$log = __DIR__ . "/voucher_activation.log";

function logx($m){
    global $log;
    file_put_contents($log, "[".date("Y-m-d H:i:s")."] ".$m.PHP_EOL, FILE_APPEND);
}

function fail($m){
    logx("FAILED: ".$m);
    header("Location: index.php?error=".urlencode($m));
    exit;
}

function normalizeSpeed($v){
    $v = strtoupper(trim((string)$v));
    if($v === "") return "1M";
    if(strpos($v,"M") === false && strpos($v,"K") === false){
        $v .= "M";
    }
    return $v;
}

$code = strtoupper(trim($_POST["voucher_code"] ?? $_GET["voucher_code"] ?? ""));
$clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

if(!$code) fail("Voucher code required.");

$stmt = $pdo->prepare("SELECT * FROM smart_vouchers WHERE voucher_code=? LIMIT 1");
$stmt->execute([$code]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$v) fail("Invalid voucher.");
if($v["status"] !== "unused") fail("Voucher already used or expired.");

$r = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if(!$r) fail("MikroTik settings missing.");

try{
    $client = new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_username"],
        "pass"=>$r["router_password"],
        "port"=>(int)$r["api_port"],
        "timeout"=>10
    ]));

    $down = normalizeSpeed($v["speed_down"] ?? "8M");
    $up   = normalizeSpeed($v["speed_up"] ?? "2M");

    // MikroTik expects upload/download.
    $rate = $up . "/" . $down;

    $profile = "VOUCHER-" . str_replace("M","",$down) . "-" . str_replace("M","",$up);
    $uptime = ((int)$v["duration_hours"]) . "h";

    logx("Redeeming $code for IP $clientIp profile=$profile rate=$rate");

    $profiles = $client->query(
        (new Query("/ip/hotspot/user/profile/print"))->where("name",$profile)
    )->read();

    if(empty($profiles)){
        $client->query(
            (new Query("/ip/hotspot/user/profile/add"))
                ->equal("name",$profile)
                ->equal("rate-limit",$rate)
                ->equal("shared-users","1")
        )->read();
    }

    $old = $client->query(
        (new Query("/ip/hotspot/user/print"))->where("name",$code)
    )->read();

    foreach($old as $u){
        if(isset($u[".id"])){
            $client->query(
                (new Query("/ip/hotspot/user/remove"))->equal(".id",$u[".id"])
            )->read();
        }
    }

    $client->query(
        (new Query("/ip/hotspot/user/add"))
            ->equal("name",$code)
            ->equal("password",$code)
            ->equal("profile",$profile)
            ->equal("limit-uptime",$uptime)
            ->equal("comment","smart-voucher:".$code)
    )->read();

    // Confirm MikroTik user exists BEFORE marking voucher used.
    $confirm = $client->query(
        (new Query("/ip/hotspot/user/print"))->where("name",$code)
    )->read();

    if(empty($confirm)){
        fail("MikroTik user was not created. Voucher remains unused.");
    }

    $expires = date("Y-m-d H:i:s", time() + ((int)$v["duration_hours"] * 3600));

    $update = $pdo->prepare("
        UPDATE smart_vouchers 
        SET status='used', used_by=?, used_at=NOW(), expires_at=? 
        WHERE id=?
    ");
    $update->execute([$clientIp,$expires,$v["id"]]);

    logx("SUCCESS: $code created in MikroTik and marked used.");

    header("Location: voucher_success.php?u=".urlencode($code)."&p=".urlencode($code));
    exit;

}catch(Exception $e){
    fail("Activation failed: ".$e->getMessage());
}
