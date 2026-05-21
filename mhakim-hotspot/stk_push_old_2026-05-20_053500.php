<?php
date_default_timezone_set("Africa/Nairobi");
require_once __DIR__ . "/mpesa_config.php";

header("Content-Type: application/json");

$phone = trim($_POST["phone"] ?? "");
$amount = (int)($_POST["amount"] ?? 1);

if ($phone === "") {
    echo json_encode(["success" => false, "message" => "Phone required"]);
    exit;
}

$phone = preg_replace("/\D/", "", $phone);

if (substr($phone, 0, 1) === "0") {
    $phone = "254" . substr($phone, 1);
}

if (substr($phone, 0, 3) !== "254") {
    echo json_encode(["success" => false, "message" => "Invalid phone"]);
    exit;
}

function curl_common($ch) {
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
}

/* TOKEN */
$credentials = base64_encode($consumerKey . ":" . $consumerSecret);

$ch = curl_init($baseUrl . "/oauth/v1/generate?grant_type=client_credentials");
curl_common($ch);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . $credentials
]);

$tokenResponse = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["success" => false, "stage" => "token", "error" => curl_error($ch)]);
    exit;
}

curl_close($ch);

$tokenData = json_decode($tokenResponse, true);

if (!isset($tokenData["access_token"])) {
    echo json_encode(["success" => false, "stage" => "token", "response" => $tokenResponse]);
    exit;
}

$accessToken = $tokenData["access_token"];

/* STK PUSH */
$timestamp = date("YmdHis");
$password = base64_encode($shortcode . $passkey . $timestamp);

$callbackUrl = "https://pancreas-remix-jelly.ngrok-free.dev/mhakim-hotspot/callback.php";

$payload = [
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackUrl,
    "AccountReference" => "HakimNetworks",
    "TransactionDesc" => "Internet Payment"
];

$curl = curl_init($baseUrl . "/mpesa/stkpush/v1/processrequest");
curl_common($curl);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo json_encode(["success" => false, "stage" => "stk", "error" => curl_error($curl)]);
    exit;
}

curl_close($curl);

echo $response;
