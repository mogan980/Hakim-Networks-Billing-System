<?php
date_default_timezone_set("Africa/Nairobi");
header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";

function fail($stage, $msg){
    echo json_encode([
        "success" => false,
        "stage" => $stage,
        "error" => $msg
    ]);
    exit;
}

$phone = preg_replace('/\D/', '', $_POST["phone"] ?? "");
$amount = (int)($_POST["amount"] ?? 0);
$package_id = (int)($_POST["package_id"] ?? 0);
$client_ip = $_SERVER["REMOTE_ADDR"] ?? "";

if(substr($phone,0,1)==="0"){
    $phone = "254" . substr($phone,1);
}

if(substr($phone,0,3)!=="254"){
    fail("validation","Phone must start with 254 or 07");
}

if($amount < 1){
    fail("validation","Invalid amount");
}

$settings = $pdo->query("SELECT * FROM mpesa_settings WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if(!$settings){
    fail("settings","M-Pesa settings missing");
}

$baseUrl = $settings["environment"] === "production"
    ? "https://api.safaricom.co.ke"
    : "https://sandbox.safaricom.co.ke";

$credentials = base64_encode($settings["consumer_key"] . ":" . $settings["consumer_secret"]);

$ch = curl_init($baseUrl . "/oauth/v1/generate?grant_type=client_credentials");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Basic " . $credentials],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 60
]);

$tokenRaw = curl_exec($ch);

if(curl_errno($ch)){
    fail("token", curl_error($ch));
}

$tokenData = json_decode($tokenRaw, true);
$accessToken = $tokenData["access_token"] ?? null;

if(!$accessToken){
    fail("token", $tokenRaw);
}

$timestamp = date("YmdHis");
$password = base64_encode($settings["shortcode"] . $settings["passkey"] . $timestamp);

$payload = [
    "BusinessShortCode" => $settings["shortcode"],
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => $settings["transaction_type"],
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $settings["till_number"],
    "PhoneNumber" => $phone,
    "CallBackURL" => $settings["callback_url"],
    "AccountReference" => "HakimNet",
    "TransactionDesc" => "Hakim Networks Internet"
];

$curl = curl_init($baseUrl . "/mpesa/stkpush/v1/processrequest");
curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $accessToken,
        "Content-Type: application/json"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($curl);

if(curl_errno($curl)){
    fail("stk", curl_error($curl));
}

$data = json_decode($response, true);

if(($data["ResponseCode"] ?? "") === "0"){
    $checkout = $data["CheckoutRequestID"];

    $stmt = $pdo->prepare("
        INSERT INTO hotspot_payments
        (phone, amount, package_id, username, password, checkout_request_id, status, client_ip)
        VALUES (?,?,?,?,?,?, 'pending', ?)
    ");

    $username = $phone;
    $passwordPlain = $phone;

    $stmt->execute([
        $phone,
        $amount,
        $package_id,
        $username,
        $passwordPlain,
        $checkout,
        $client_ip
    ]);
}

echo $response;
