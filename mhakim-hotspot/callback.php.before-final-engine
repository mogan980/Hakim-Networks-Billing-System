<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$raw = file_get_contents("php://input");
file_put_contents(__DIR__."/mpesa_callback.log","[".date("Y-m-d H:i:s")."] ".$raw.PHP_EOL,FILE_APPEND);

$data = json_decode($raw,true);
$callback = $data["Body"]["stkCallback"] ?? null;

if(!$callback){
    echo "OK";
    exit;
}

$checkout = $callback["CheckoutRequestID"] ?? "";
$resultCode = (int)($callback["ResultCode"] ?? 999);

$stmt = $pdo->prepare("SELECT * FROM hotspot_payments WHERE checkout_request_id=? LIMIT 1");
$stmt->execute([$checkout]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$payment){
    file_put_contents(__DIR__."/mpesa_callback_error.log","Payment not found: $checkout\n",FILE_APPEND);
    echo "OK";
    exit;
}

if($resultCode !== 0){
    $pdo->prepare("UPDATE hotspot_payments SET status='failed' WHERE id=?")->execute([$payment["id"]]);
    echo "OK";
    exit;
}

$receipt = "";
foreach(($callback["CallbackMetadata"]["Item"] ?? []) as $item){
    if(($item["Name"] ?? "") === "MpesaReceiptNumber"){
        $receipt = $item["Value"] ?? "";
    }
}

$package = null;
if(!empty($payment["package_id"])){
    $p = $pdo->prepare("SELECT * FROM packages WHERE id=? LIMIT 1");
    $p->execute([$payment["package_id"]]);
    $package = $p->fetch(PDO::FETCH_ASSOC);
}

$hours = (int)($package["duration_hours"] ?? 24);
$expires = date("Y-m-d H:i:s", time() + ($hours * 3600));

$pdo->prepare("
UPDATE hotspot_payments
SET status='paid', mpesa_receipt=?, paid_at=NOW(), expires_at=?
WHERE id=?
")->execute([$receipt,$expires,$payment["id"]]);

function speed($v){
    $v = strtoupper(trim((string)$v));
    if($v === "") return "1M";
    if(strpos($v,"M") === false && strpos($v,"K") === false) $v .= "M";
    return $v;
}

try{
    $r = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $client = new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_username"],
        "pass"=>$r["router_password"],
        "port"=>(int)$r["api_port"],
        "timeout"=>10
    ]));

    $clientIp = $payment["client_ip"];
    $username = $payment["username"] ?: $payment["phone"];
    $password = $payment["password"] ?: $username;

    $down = speed($package["speed_down"] ?? "8M");
    $up = speed($package["speed_up"] ?? "2M");

    // MikroTik queue format is upload/download.
    $rate = $up . "/" . $down;

    // Remove old user
    $oldUsers = $client->query((new Query("/ip/hotspot/user/print"))->where("name",$username))->read();
    foreach($oldUsers as $u){
        if(isset($u[".id"])){
            $client->query((new Query("/ip/hotspot/user/remove"))->equal(".id",$u[".id"]))->read();
        }
    }

    // Create hotspot user also, for records/manual login fallback
    $client->query(
        (new Query("/ip/hotspot/user/add"))
            ->equal("name",$username)
            ->equal("password",$password)
            ->equal("limit-uptime",$hours."h")
            ->equal("comment","mpesa:".$checkout)
    )->read();

    // Remove old IP binding for same payment/device
    $bindings = $client->query((new Query("/ip/hotspot/ip-binding/print"))->where("address",$clientIp))->read();
    foreach($bindings as $b){
        if(isset($b[".id"])){
            $client->query((new Query("/ip/hotspot/ip-binding/remove"))->equal(".id",$b[".id"]))->read();
        }
    }

    // Bypass hotspot login permanently until expiry engine removes it
    $client->query(
        (new Query("/ip/hotspot/ip-binding/add"))
            ->equal("address",$clientIp)
            ->equal("type","bypassed")
            ->equal("comment","mpesa:".$checkout." expires:".$expires)
    )->read();

    // Remove old queue for same client/payment
    $queues = $client->query((new Query("/queue/simple/print"))->where("target",$clientIp."/32"))->read();
    foreach($queues as $q){
        if(isset($q[".id"])){
            $client->query((new Query("/queue/simple/remove"))->equal(".id",$q[".id"]))->read();
        }
    }

    // Add speed limit
    $client->query(
        (new Query("/queue/simple/add"))
            ->equal("name","MPESA-".$username)
            ->equal("target",$clientIp."/32")
            ->equal("max-limit",$rate)
            ->equal("comment","mpesa:".$checkout." expires:".$expires)
    )->read();

    // Remove from hosts/active so device reconnects cleanly
    $hosts = $client->query((new Query("/ip/hotspot/host/print"))->where("address",$clientIp))->read();
    foreach($hosts as $h){
        if(isset($h[".id"])){
            $client->query((new Query("/ip/hotspot/host/remove"))->equal(".id",$h[".id"]))->read();
        }
    }

    file_put_contents(__DIR__."/mpesa_callback.log","[".date("Y-m-d H:i:s")."] PAYMENT ACTIVATED BY IP BINDING SUCCESSFULLY: $clientIp\n",FILE_APPEND);

}catch(Exception $e){
    file_put_contents(__DIR__."/mpesa_callback_error.log","[".date("Y-m-d H:i:s")."] ".$e->getMessage().PHP_EOL,FILE_APPEND);
}

echo "OK";
