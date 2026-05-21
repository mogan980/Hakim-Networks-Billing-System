<?php
date_default_timezone_set("Africa/Nairobi");

require_once __DIR__ . "/config/database.php";
require_once "/var/www/html/vendor/autoload.php";

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

$data = file_get_contents("php://input");

file_put_contents(
    __DIR__ . "/callback.log",
    date("Y-m-d H:i:s") . " " . $data . PHP_EOL,
    FILE_APPEND
);

$response = json_decode($data,true);

$callback = $response["Body"]["stkCallback"] ?? null;

if(!$callback){
    exit;
}

$checkout = $callback["CheckoutRequestID"] ?? "";
$resultCode = $callback["ResultCode"] ?? 1;

$stmt = $pdo->prepare("
SELECT * FROM hotspot_payments
WHERE checkout_request_id=?
LIMIT 1
");

$stmt->execute([$checkout]);

$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$payment){
    exit;
}

if($resultCode != 0){

    $up = $pdo->prepare("
    UPDATE hotspot_payments
    SET status='failed'
    WHERE id=?
    ");

    $up->execute([$payment["id"]]);

    exit;
}

$metadata = $callback["CallbackMetadata"]["Item"] ?? [];

$receipt = "";
$phone = $payment["phone"];

foreach($metadata as $m){

    if(($m["Name"] ?? "") === "MpesaReceiptNumber"){
        $receipt = $m["Value"];
    }

    if(($m["Name"] ?? "") === "PhoneNumber"){
        $phone = $m["Value"];
    }
}

$pkg = $pdo->prepare("
SELECT * FROM hotspot_packages
WHERE id=?
LIMIT 1
");

$pkg->execute([$payment["package_id"]]);

$package = $pkg->fetch(PDO::FETCH_ASSOC);

if(!$package){
    exit;
}

$voucher = "MH-" . strtoupper(substr(bin2hex(random_bytes(4)),0,8));

$saveVoucher = $pdo->prepare("
INSERT INTO smart_vouchers
(
voucher_code,
package_name,
speed_down,
speed_up,
duration_hours,
price,
status
)
VALUES
(?,?,?,?,?,?,?)
");

$saveVoucher->execute([
    $voucher,
    $package["name"],
    $package["speed_down"],
    $package["speed_up"],
    $package["duration_hours"],
    $package["price"],
    "unused"
]);

$r = $pdo->query("
SELECT * FROM mikrotik_settings
LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

try{

    $client = new Client(new Config([
        "host"=>$r["router_ip"],
        "user"=>$r["router_username"],
        "pass"=>$r["router_password"],
        "port"=>$r["api_port"],
        "timeout"=>10
    ]));

    $profile = "PKG-" . $package["id"];

    $profiles = $client->query(
        (new Query("/ip/hotspot/user/profile/print"))
        ->where("name",$profile)
    )->read();

    if(empty($profiles)){

        $client->query(
            (new Query("/ip/hotspot/user/profile/add"))
            ->equal("name",$profile)
            ->equal("rate-limit",$package["speed_down"]."/".$package["speed_up"])
            ->equal("shared-users","1")
        )->read();
    }

    $client->query(
        (new Query("/ip/hotspot/user/add"))
        ->equal("name",$voucher)
        ->equal("password",$voucher)
        ->equal("profile",$profile)
        ->equal("limit-uptime",$package["duration_hours"]."h")
        ->equal("comment","stk-payment")
    )->read();

}catch(Exception $e){

    file_put_contents(
        __DIR__ . "/callback_error.log",
        date("Y-m-d H:i:s") . " " . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );
}

$up = $pdo->prepare("
UPDATE hotspot_payments
SET
status='paid',
mpesa_receipt=?,
paid_at=NOW(),
username=?,
password=?
WHERE id=?
");

$up->execute([
    $receipt,
    $voucher,
    $voucher,
    $payment["id"]
]);

echo "OK";
