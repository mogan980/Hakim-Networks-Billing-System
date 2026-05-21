<?php
date_default_timezone_set("Africa/Nairobi");

ini_set("display_errors",1);
error_reporting(E_ALL);

require_once __DIR__ . "/config/database.php";

function fail($msg){
    die($msg);
}

$phone = trim($_POST["phone"] ?? "");
$packageId = (int)($_POST["package_id"] ?? 0);
$clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

if(!$phone || !$packageId){
    fail("Missing phone or package");
}

$phone = preg_replace('/\D/','',$phone);

if(substr($phone,0,1)==="0"){
    $phone = "254" . substr($phone,1);
}

if(substr($phone,0,3)!=="254"){
    fail("Invalid phone format");
}

$p = $pdo->prepare("
SELECT * FROM hotspot_packages
WHERE id=? AND status='active'
LIMIT 1
");

$p->execute([$packageId]);
$package = $p->fetch(PDO::FETCH_ASSOC);

if(!$package){
    fail("Package not found");
}

$amount = (float)$package["price"];

$stmt = $pdo->query("
SELECT * FROM mpesa_settings
WHERE status='active'
ORDER BY id DESC
LIMIT 1
");

$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$settings){
    fail("M-Pesa settings missing");
}

$consumerKey = $settings["consumer_key"];
$consumerSecret = $settings["consumer_secret"];
$shortcode = $settings["shortcode"];
$passkey = $settings["passkey"];
$callback = $settings["callback_url"];
$transactionType = $settings["transaction_type"];

$credentials = base64_encode($consumerKey . ":" . $consumerSecret);

$tokenUrl = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

$ch = curl_init($tokenUrl);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $credentials"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$tokenResponse = curl_exec($ch);

if(curl_errno($ch)){
    fail("TOKEN CURL ERROR: " . curl_error($ch));
}

$tokenData = json_decode($tokenResponse,true);

$accessToken = $tokenData["access_token"] ?? null;

if(!$accessToken){
    fail("Failed to get access token");
}

$timestamp = date("YmdHis");

$password = base64_encode(
    $shortcode .
    $passkey .
    $timestamp
);

$accountReference = "HakimNetworks";
$transactionDesc = $package["name"];

$payload = [
    "BusinessShortCode"=>$shortcode,
    "Password"=>$password,
    "Timestamp"=>$timestamp,
    "TransactionType"=>$transactionType,
    "Amount"=>$amount,
    "PartyA"=>$phone,
    "PartyB"=>$shortcode,
    "PhoneNumber"=>$phone,
    "CallBackURL"=>$callback,
    "AccountReference"=>$accountReference,
    "TransactionDesc"=>$transactionDesc
];

$stkUrl = "https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

$ch = curl_init($stkUrl);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $accessToken"
]);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if(curl_errno($ch)){
    fail("STK CURL ERROR: " . curl_error($ch));
}

file_put_contents(
    __DIR__ . "/stk_push.log",
    date("Y-m-d H:i:s") . " " . $response . PHP_EOL,
    FILE_APPEND
);

$data = json_decode($response,true);

if(isset($data["ResponseCode"]) && $data["ResponseCode"] == "0"){

    $insert = $pdo->prepare("
    INSERT INTO hotspot_payments
    (
        phone,
        amount,
        package_id,
        checkout_request_id,
        status,
        client_ip
    )
    VALUES
    (?,?,?,?,?,?)
    ");

    $insert->execute([
        $phone,
        $amount,
        $packageId,
        $data["CheckoutRequestID"],
        "pending",
        $clientIp
    ]);

    echo json_encode([
        "success"=>true,
        "message"=>"STK Push sent successfully"
    ]);

}else{

    echo json_encode([
        "success"=>false,
        "response"=>$data
    ]);
}
