<?php
date_default_timezone_set("Africa/Nairobi");

require_once "/var/www/html/mhakim-billing-system/config/database.php";

function fail($msg){
    die("STK ERROR: " . $msg);
}

$phone = trim($_POST["phone"] ?? "");
$packageId = (int)($_POST["package_id"] ?? 0);
$clientIp = $_POST["client_ip"] ?? ($_SERVER["REMOTE_ADDR"] ?? "");

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

$p = $pdo->prepare("SELECT * FROM packages WHERE id=? LIMIT 1");
$p->execute([$packageId]);
$package = $p->fetch(PDO::FETCH_ASSOC);

if(!$package){
    fail("Package not found");
}

$amount = ceil((float)$package["price"]);
$username = $phone;
$userPassword = substr($phone,-4);

$c = $pdo->prepare("SELECT * FROM clients WHERE username=? OR phone=? LIMIT 1");
$c->execute([$username,$phone]);
$client = $c->fetch(PDO::FETCH_ASSOC);

if($client){
    $clientId = $client["id"];

    $pdo->prepare("
        UPDATE clients
        SET phone=?, password=?, package_id=?, client_ip=?, status='pending'
        WHERE id=?
    ")->execute([$phone,$userPassword,$packageId,$clientIp,$clientId]);

}else{
    $pdo->prepare("
        INSERT INTO clients
        (full_name, phone, username, password, package_id, status, client_ip, connection_type)
        VALUES (?,?,?,?,?,?,?,?)
    ")->execute([$username,$phone,$username,$userPassword,$packageId,'pending',$clientIp,'hotspot']);

    $clientId = $pdo->lastInsertId();
}

$mpesa = $pdo->query("
    SELECT *
    FROM mpesa_settings
    WHERE status='active'
    ORDER BY id ASC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if(!$mpesa){
    fail("M-Pesa settings missing");
}

$environment = strtolower($mpesa["environment"] ?? "sandbox");

$base = in_array($environment, ["live","production"])
    ? "https://api.safaricom.co.ke"
    : "https://sandbox.safaricom.co.ke";

$key = trim($mpesa["consumer_key"]);
$secret = trim($mpesa["consumer_secret"]);
$shortcode = trim($mpesa["shortcode"]);
$passkey = trim($mpesa["passkey"]);
$callback = trim($mpesa["callback_url"]);
$transactionType = trim($mpesa["transaction_type"] ?? "CustomerBuyGoodsOnline");

if(!$key || !$secret || !$shortcode || !$passkey || !$callback){
    fail("M-Pesa credentials or callback missing");
}

/* access token */
$credentials = base64_encode($key . ":" . $secret);

$ch = curl_init($base . "/oauth/v1/generate?grant_type=client_credentials");

curl_setopt_array($ch,[
    CURLOPT_HTTPHEADER => ["Authorization: Basic " . $credentials],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 60
]);

$tokenResponse = curl_exec($ch);

if(curl_errno($ch)){
    fail("TOKEN CURL: " . curl_error($ch));
}

$tokenHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenData = json_decode($tokenResponse,true);
$accessToken = $tokenData["access_token"] ?? null;

if(!$accessToken){
    fail("TOKEN HTTP $tokenHttp: " . $tokenResponse);
}

/* stk push */
$timestamp = date("YmdHis");
$passwordKey = base64_encode($shortcode . $passkey . $timestamp);

$payload = [
    "BusinessShortCode" => $shortcode,
    "Password" => $passwordKey,
    "Timestamp" => $timestamp,
    "TransactionType" => $transactionType,
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callback,
    "AccountReference" => "HakimNetworks",
    "TransactionDesc" => $package["name"] ?? "Hotspot Internet"
];

$ch = curl_init($base . "/mpesa/stkpush/v1/processrequest");

curl_setopt_array($ch,[
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);

if(curl_errno($ch)){
    fail("STK CURL: " . curl_error($ch));
}

$stkHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response,true);
$checkoutId = $data["CheckoutRequestID"] ?? null;

if(!$checkoutId){
    fail("STK HTTP $stkHttp: " . $response);
}

$pdo->prepare("
    INSERT INTO payments
    (client_id, package_id, phone, amount, method, status, checkout_id, reference, client_ip, created_at)
    VALUES (?, ?, ?, ?, 'mpesa', 'pending', ?, ?, ?, NOW())
")->execute([
    $clientId,
    $packageId,
    $phone,
    $amount,
    $checkoutId,
    $checkoutId,
    $clientIp
]);

header("Location: wait_payment.php?checkout_id=" . urlencode($checkoutId));
exit;
