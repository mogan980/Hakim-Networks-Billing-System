<?php
date_default_timezone_set("Africa/Nairobi");
require_once "/var/www/html/mhakim-hotspot/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function fail($msg){
    echo "<body style='font-family:Arial;background:#450a0a;color:white;text-align:center;padding:70px'>";
    echo "<h1>Voucher Failed</h1><p>".htmlspecialchars($msg)."</p>";
    echo "<a style='color:white' href='index.php'>Go Back</a></body>";
    exit;
}

$code = strtoupper(trim($_POST["voucher_code"] ?? $_GET["voucher_code"] ?? ""));
$clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

if(!$code) fail("Voucher code required");

$stmt = $pdo->prepare("SELECT * FROM smart_vouchers WHERE voucher_code=? LIMIT 1");
$stmt->execute([$code]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$v) fail("Invalid voucher: ".$code);
if($v["status"] !== "unused") fail("Voucher already used or expired");

$r = $pdo->query("SELECT * FROM mikrotik_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if(!$r) fail("MikroTik settings missing");

try{
    $client = new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_username"],
        "pass"=>$r["router_password"],
        "port"=>(int)$r["api_port"],
        "timeout"=>10
    ]));

    $down = $v["speed_down"] ?: "8M";
    $up = $v["speed_up"] ?: "2M";
    $rate = trim($down) . "/" . trim($up);

if(stripos($down,'M') === false){
    $down .= 'M';
}

if(stripos($up,'M') === false){
    $up .= 'M';
}

$rate = $down . "/" . $up;
    $profile = "VOUCHER-" . str_replace("M","",$down) . "-" . str_replace("M","",$up);
    $uptime = ((int)$v["duration_hours"]) . "h";

    $profiles = $client->query((new Query("/ip/hotspot/user/profile/print"))->where("name",$profile))->read();
    if(empty($profiles)){
        $client->query(
    (new Query("/queue/simple/add"))
        ->equal("name","CLIENT-".$code)
        ->equal("target",$clientIp."/32")
        ->equal("max-limit",$rate)
        ->equal("comment","voucher-limit:".$code)
)->read();
    }

    $oldUser = $client->query((new Query("/ip/hotspot/user/print"))->where("name",$code))->read();
    foreach($oldUser as $u){
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

    
    // No IP bypass for clients.
    // Client must login normally through MikroTik hotspot using voucher username/password.


    $expires = date("Y-m-d H:i:s", time() + ((int)$v["duration_hours"] * 3600));
    $updt = $pdo->prepare("UPDATE smart_vouchers SET status='used', used_by=?, used_at=NOW(), expires_at=? WHERE id=?");
    $updt->execute([$clientIp,$expires,$v["id"]]);

    header("Location: voucher_success.php?u=".urlencode($code));
    exit;

}catch(Exception $e){
    fail("Activation failed: ".$e->getMessage());
}
