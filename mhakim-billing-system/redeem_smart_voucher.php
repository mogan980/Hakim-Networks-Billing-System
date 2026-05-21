<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/core/activate_internet.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

function fail($msg){
    echo "<h2 style='font-family:Arial;color:#dc2626;text-align:center;margin-top:80px;'>$msg</h2>";
    exit;
}

$code = strtoupper(trim($_POST["voucher_code"] ?? $_GET["voucher_code"] ?? ""));

if(!$code){
    fail("Voucher code required.");
}

$stmt = $pdo->prepare("SELECT * FROM smart_vouchers WHERE voucher_code=? LIMIT 1");
$stmt->execute([$code]);
$voucher = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$voucher){
    fail("Invalid voucher.");
}

if($voucher["status"] !== "unused"){
    fail("Voucher already used or expired.");
}

$router = $pdo->query("SELECT * FROM routers WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if(!$router){
    fail("No active MikroTik router selected.");
}

try{
    $client = new Client(new Config([
        "host"=>$router["router_ip"],
        "user"=>$router["router_username"],
        "pass"=>$router["router_password"],
        "port"=>(int)$router["api_port"],
        "timeout"=>5
    ]));

    $profile = "VOUCHER-" . str_replace("M","",$voucher["speed_down"]) . "-" . str_replace("M","",$voucher["speed_up"]);
    $rateLimit = $voucher["speed_down"] . "/" . $voucher["speed_up"];
    $limitUptime = ((int)$voucher["duration_hours"]) . "h";

    $profiles = $client->query(
        (new Query("/ip/hotspot/user/profile/print"))
            ->where("name", $profile)
    )->read();

    if(empty($profiles)){
        $client->query(
            (new Query("/ip/hotspot/user/profile/add"))
                ->equal("name", $profile)
                ->equal("rate-limit", $rateLimit)
                ->equal("shared-users", "1")
        )->read();
    }

    $existing = $client->query(
        (new Query("/ip/hotspot/user/print"))
            ->where("name", $code)
    )->read();

    foreach($existing as $u){
        if(isset($u[".id"])){
            $client->query(
                (new Query("/ip/hotspot/user/remove"))
                    ->equal(".id", $u[".id"])
            )->read();
        }
    }

    $client->query(
        (new Query("/ip/hotspot/user/add"))
            ->equal("name", $code)
            ->equal("password", $code)
            ->equal("profile", $profile)
            ->equal("limit-uptime", $limitUptime)
            ->equal("comment", "smart-voucher:" . $code)
    )->read();
   $clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

if (!$clientIp) {
    fail("Could not detect client IP.");
}

$result = activateInternet(
    $clientIp,
    "Voucher " . $code,
    $voucher["speed_down"],
    $voucher["speed_up"],
    (int)$voucher["duration_hours"],
    "smart-voucher:" . $code,
    $code,
    $code
);

if (!$result["success"]) {
    fail("Voucher is valid but internet activation failed: " . $result["message"]);
}
    $expires = date("Y-m-d H:i:s", time() + ((int)$voucher["duration_hours"] * 3600));

    $update = $pdo->prepare("
        UPDATE smart_vouchers 
        SET status='used', used_by=?, used_at=NOW(), expires_at=? 
        WHERE id=?
    ");
    $update->execute([
        $clientIp,
        $expires,
        $voucher["id"]
    ]);

    header("Location: smart_voucher_success.php?u=" . urlencode($code) . "&p=" . urlencode($code));
    exit;

}catch(Exception $e){
    fail("Activation failed: " . $e->getMessage());
}
