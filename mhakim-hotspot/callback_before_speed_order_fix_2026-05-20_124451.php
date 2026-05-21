<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$raw = file_get_contents("php://input");

file_put_contents(__DIR__ . "/mpesa_callback.log", "[" . date("Y-m-d H:i:s") . "] " . $raw . PHP_EOL, FILE_APPEND);

$data = json_decode($raw, true);

$callback = $data["Body"]["stkCallback"] ?? null;

if(!$callback){
    http_response_code(200);
    echo "OK";
    exit;
}

$checkout = $callback["CheckoutRequestID"] ?? "";
$resultCode = (int)($callback["ResultCode"] ?? 999);
$resultDesc = $callback["ResultDesc"] ?? "";

$stmt = $pdo->prepare("SELECT * FROM hotspot_payments WHERE checkout_request_id=? LIMIT 1");
$stmt->execute([$checkout]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$payment){
    file_put_contents(__DIR__ . "/mpesa_callback_error.log", "Payment not found: $checkout\n", FILE_APPEND);
    echo "OK";
    exit;
}

if($resultCode !== 0){
    $up = $pdo->prepare("UPDATE hotspot_payments SET status='failed' WHERE id=?");
    $up->execute([$payment["id"]]);
    echo "OK";
    exit;
}

$items = $callback["CallbackMetadata"]["Item"] ?? [];
$receipt = "";

foreach($items as $item){
    if(($item["Name"] ?? "") === "MpesaReceiptNumber"){
        $receipt = $item["Value"] ?? "";
    }
}

$up = $pdo->prepare("
    UPDATE hotspot_payments
    SET status='paid', mpesa_receipt=?, paid_at=NOW()
    WHERE id=?
");
$up->execute([$receipt, $payment["id"]]);

try {
    $settings = $pdo->query("SELECT * FROM mikrotik_settings ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if(!$settings){
        throw new Exception("MikroTik settings missing");
    }

    $client = new Client(new Config([
        "host" => $settings["router_ip"],
        "user" => $settings["router_username"],
        "pass" => $settings["router_password"],
        "port" => (int)$settings["api_port"],
        "timeout" => 10
    ]));

    $package = null;

    if(!empty($payment["package_id"])){
        $p = $pdo->prepare("SELECT * FROM hotspot_packages WHERE id=? LIMIT 1");
        $p->execute([$payment["package_id"]]);
        $package = $p->fetch(PDO::FETCH_ASSOC);
    }

    $down = $package["speed_down"] ?? "8M";
    $upSpeed = $package["speed_up"] ?? "2M";
    $hours = (int)($package["duration_hours"] ?? 24);

    if(stripos($down, "M") === false) $down .= "M";
    if(stripos($upSpeed, "M") === false) $upSpeed .= "M";

    $rate = $down . "/" . $upSpeed;
    $profile = "PAYMENT-" . str_replace("M","",$down) . "-" . str_replace("M","",$upSpeed);
    $uptime = $hours . "h";
    $username = $payment["username"];
    $password = $payment["password"];

    $profiles = $client->query(
        (new Query("/ip/hotspot/user/profile/print"))->where("name", $profile)
    )->read();

    if(empty($profiles)){
        $client->query(
            (new Query("/ip/hotspot/user/profile/add"))
                ->equal("name", $profile)
                ->equal("rate-limit", $rate)
                ->equal("shared-users", "1")
        )->read();
    }

    $oldUsers = $client->query(
        (new Query("/ip/hotspot/user/print"))->where("name", $username)
    )->read();

    foreach($oldUsers as $u){
        if(isset($u[".id"])){
            $client->query(
                (new Query("/ip/hotspot/user/remove"))->equal(".id", $u[".id"])
            )->read();
        }
    }

    $client->query(
        (new Query("/ip/hotspot/user/add"))
            ->equal("name", $username)
            ->equal("password", $password)
            ->equal("profile", $profile)
            ->equal("limit-uptime", $uptime)
            ->equal("comment", "mpesa:" . $checkout)
    )->read();

    file_put_contents(__DIR__ . "/mpesa_callback.log", "[" . date("Y-m-d H:i:s") . "] PAYMENT ACTIVATED SUCCESSFULLY\n", FILE_APPEND);

} catch(Exception $e){
    file_put_contents(__DIR__ . "/mpesa_callback_error.log", "[" . date("Y-m-d H:i:s") . "] " . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

echo "OK";
